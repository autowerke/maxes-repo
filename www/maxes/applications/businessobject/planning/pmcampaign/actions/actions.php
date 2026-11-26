<?php
require_once ("../../../eat.php");

Class ActionSet {
	
	public static function schedulerAI(){
		$payload = Super::getParam("schedulerAI");
		$pmcampaignnum = $payload["pmcampaignnum"];
		$scheduling_data = $payload["scheduling_data"];

		
		$dataML = [
			"pmcampaignum" => $pmcampaignnum,
			"scheduling_data" => $scheduling_data, 
			"week_capacity" => 32
		];


		$friendlyPayload = json_encode($dataML);
		$endpoint = "/generate/workorder-scheduling";
		
		$resultAI = Super::sendToML($endpoint, $friendlyPayload, true); 

		//echo json_encode($resultAI);
		//exit;
		$dataAI = json_decode($resultAI, true);

		if ($dataAI) {
			$toon = $dataAI['toon_data'] ?? null;
			$pmcampaignnum = $dataAI['pmcampaignnum'] ?? null;
			
			$pmcampaignDescription = "";
			if (!empty($pmcampaignnum)) {
				$pmcampaignDescription = (new Set())->MboSetRemote("pmcampaign")
					->where("pmcampaignnum=?", $pmcampaignnum)
					->getMboValue("description");
			}
			
			$header = "pmcampaignnum: $pmcampaignnum - $pmcampaignDescription <hr>";
			$narrative_analysis = $dataAI['narrative_analysis'] ?? "";

			$optimized_schedule = $dataAI["optimized_schedule"];
			Super::sendJsonResponse("success", $narrative_analysis, $optimized_schedule); //auto exit
			
		}
		Super::SendJsonResponse("success", json_encode($scheduling_data));
	}
	
	public static function updateQtyProcCart() {
	
		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();

		try {
			$payload = Super::getParam("updateQtyProcCart");
			if (empty($payload['data']) || !is_array($payload['data'])) {
				throw new Exception("Payload Format is not valid or null.");
			}
			
			$updates = $payload['data'];
			
			foreach ($updates as $update) {
				if (empty($update['procurementcartid']) || !is_numeric($update['procurementcartid'])) {
					throw new Exception("Procurement Cart ID is not valid.");
				}
				// Kuantitas boleh 0 (mungkin untuk dihapus nanti), tapi tidak boleh negatif atau non-numerik.
				if (!isset($update['newquantity']) || !is_numeric($update['newquantity']) || $update['newquantity'] < 0) {
					throw new Exception("New Quantity is not valid for ID: " . $update['procurementcartid'] . " . Quantity must be numeric and more than 0");
				}
				
				$procurementcartid = (int)$update['procurementcartid'];
				$newquantity = (float)$update['newquantity'];

				
				(new Set())
					->useTransactionManager()
					->MboSetRemote("procurementcart")
					->where("procurementcartid = ?", $procurementcartid) 
					->setValue("quantity", $newquantity)
					->change();
			}

		
			$tm->commit();
			Super::sendJsonResponse("success", "New Quantity has been modified");

		} catch (Exception $e) {
			
			$tm->rollback();
			Super::sendJsonResponse("error", "Failed to update New Quantity: " . $e->getMessage());
		}
	}
	public static function createSQFromCart() {
	
		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();

		try {
		
			$payload = Super::getParam("createSQFromCart");
			
			if (empty($payload["pmcampaignnum"])) {
				throw new Exception("Campaign Num is required!");
			}
			$pmcampaignnum = $payload["pmcampaignnum"];

			$set = new Set();
			$cartItems = $set->useTransactionManager()
							->MboSetRemote("procurementcart")
							->where("pmcampaignnum = ? AND carttype = 'SQ' AND status = 'PENDING'", $pmcampaignnum)
							->getMboSet()
							->fetchData();

			if (empty($cartItems)) {
				Super::sendJsonResponse("success", "There are no Sales Quotation items that need to be processed in the cart.");
				$tm->commit();
				return;
			}

			// Kelompokkan item berdasarkan Pelanggan (customercode)
			$itemsByCustomer = [];
			foreach ($cartItems as $item) {
				$itemsByCustomer[$item['customercode']][] = $item;
			}

			// Langkah 4: Pre-fetching Data untuk Efisiensi
			$itemNums = []; $serviceNums = []; $woNums = [];
			foreach ($cartItems as $item) {
				if ($item['sourcetype'] == 'ITEM') $itemNums[] = $item['sourcenum'];
				if ($item['sourcetype'] == 'SERVICE') $serviceNums[] = $item['sourcenum'];
				$woNums[] = $item['wonum'];
			}
			
			$itemData = !empty($itemNums) ? (new Set())->useTransactionManager()->MboSetRemote("item")->whereIn("itemnum", array_unique($itemNums))->getMboSet()->fetchDataAssoc('itemnum') : [];
			$serviceData = !empty($serviceNums) ? (new Set())->useTransactionManager()->MboSetRemote("serviceitems")->whereIn("serviceitemnum", array_unique($serviceNums))->getMboSet()->fetchDataAssoc('serviceitemnum') : [];
			$woData = !empty($woNums) ? (new Set())->useTransactionManager()->MboSetRemote("workorder")->whereIn("wonum", array_unique($woNums))->getMboSet()->fetchDataAssoc('wonum') : [];

			// Inisialisasi variabel untuk respons
			$createdSQs = [];
			$currentUser = Xbo::getLoginUser();
			$currentDate = Xbo::setEnterDate();
			$userInfo = Xbo::getOrgcode($currentUser);
			$currencyCode = Xbo::getBaseCurrency($userInfo->orgcode);

			// LOOP UTAMA - BUAT SATU SQ PER CUSTOMER
			
			foreach ($itemsByCustomer as $customerCode => $customerItems) {
				
				// 5.1 Proses SalesQuotationLines
				$newSqNum = Xbo::getNextAutoNumberApplication("SALESQUOTATION", "updateNextVal");
				$totalPrice = 0.0;
				$requiredDates = [];
				$sqLineNum = 10;
				$sqLinesToInsert = [];
				$processedCartIds = [];

				foreach ($customerItems as $cart) {
					$processedCartIds[] = $cart['procurementcartid'];
					
					$unitPrice = (float)$cart['unitprice']; 
					if (empty($unitPrice) || $unitPrice <= 0) {
						throw new Exception("The Selling Price (SQ Unit Price) for item '{$cart['sourcenum']}' has not been set or is zero in the cart.");
					}

					$linePrice = (float)$cart['quantity'] * $unitPrice;
					$totalPrice += $linePrice;
					
					$currentWo = $woData[$cart['wonum']] ?? null;
					$requiredDate = $currentWo ? $currentWo['targstartdate'] : $currentDate;
					$requiredDates[] = $requiredDate;
					$gldebitacct = (new Set())->MboSetRemote("customer")->where("customercode=?", $customerCode)->getMboValue("rbniacct");
					$glcreditacct = (new Set())->MboSetRemote("workorder")->where("wonum=?", $cart['wonum'] )->getMboValue("glaccount");
					$sqLine = [
						'salesquotenum' => $newSqNum,
						'salesquotationlinenum' => $sqLineNum,
						'description' => $cart['description'],
						'unitprice' => $unitPrice,
						'lineprice' => $linePrice,
						'sellqty' => (float)$cart['quantity'],
						'customercode' => $customerCode,
						'requireddate' => $requiredDate,
						'enterby' => $currentUser,
						'enterdate' => $currentDate,
						'changeby' => $currentUser,
						'changedate' => $currentDate,
						'sentstatus' => 'NOT-SENT',
						'unitcost' => (float)$cart['unitcost'], 
						'linecost' => (float)$cart['quantity'] * (float)$cart['unitcost'], 
						'margin' => (float)$cart['margin'], 
						'wonum' =>  $cart['wonum'],
						'gldebitacct' => $gldebitacct,
						'glcreditacct' => $glcreditacct,
						'taxcode'=> null
					];

					if ($cart['sourcetype'] == 'ITEM') {
						$currentItem = $itemData[$cart['sourcenum']] ?? null;
						$sqLine['linetype'] = 'item';
						$sqLine['itemnum'] = $cart['sourcenum'];
						$sqLine['orderunit'] = $currentItem ? $currentItem['orderunit'] : null;
					} elseif ($cart['sourcetype'] == 'SERVICE') {
						$currentService = $serviceData[$cart['sourcenum']] ?? null;
						$sqLine['linetype'] = 'services';
						$sqLine['serviceitemnum'] = $cart['sourcenum']; 
						$sqLine['orderunit'] = $currentService ? $currentService['serviceunit'] : null;
					}
					
					$sqLinesToInsert[] = $sqLine;
					$sqLineNum += 10;
				}

				// Simpan semua Sales Quotation Lines
				(new Set())->useTransactionManager()
						->MboSetRemote("salesquotationline")
						->addStream($sqLinesToInsert);

				// Simpan Sales Quotation Header
				$minRequiredDate = !empty($requiredDates) ? min($requiredDates) : $currentDate;

				(new Set())->useTransactionManager()
					->MboSetRemote("salesquotation")
					->setValue("salesquotenum", $newSqNum)
					->setValue("pmcampaignnum", $pmcampaignnum)
					->setValue("description", "Sales Quotation for Campaign: " . $pmcampaignnum)
					->setValue("customercode", $customerCode)
					->setValue("employeesid", $currentUser)
					->setValue("currencycode", $currencyCode)
					->setValue("exchangerate", 1)
					->setValue("pretaxtotal", $totalPrice)
					->setValue("totaltax", 0)
					->setValue("totalprice", $totalPrice)
					->setValue("status", "DRAFT")
					->setValue("statusdate", $currentDate)
					->setValue("sentstatus", "NOT SENT")
					->setValue("enterdate", $currentDate)
					->setValue("enterby", $currentUser)
					->setValue("changeby", $currentUser)
					->setValue("changedate", $currentDate)
					->setValue("orgcode", $userInfo->orgcode)
					->setValue("sitecode", $userInfo->sitecode)
					->add();

				// Update status di procurementcart 
				(new Set())->useTransactionManager()
					->MboSetRemote("procurementcart")
					->whereIn("procurementcartid", $processedCartIds)
					->setValue("status", "PROCESSED")
					->setValue("docnum", $newSqNum)
					->change();
				
				$createdSQs[] = $newSqNum;
			} // Akhir dari loop 

			$tm->commit();
			
			$message = "Sales Quotation has been successfully created: " . implode(', ', $createdSQs);
			Super::sendJsonResponse("success", $message, [
				'salesquotenums' => $createdSQs
			]);

		} catch (Exception $e) {
			$tm->rollback();
			Super::sendJsonResponse("error", "Failed to create Sales Quotation: " . $e->getMessage());
		}
	}


	public static function createPRFromCart() {
		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();

		try {
			$payload = Super::getParam("createPRFromCart");
			
			if (empty($payload["pmcampaignnum"])) {
				throw new Exception("PM Campaign Number is required");
			}
			$pmcampaignnum = $payload["pmcampaignnum"];

			// Pengambilan Data & Validasi Awal (Tetap sama)
			$set = new Set();
			$cartItems = $set->useTransactionManager()
							->MboSetRemote("procurementcart")
							->where("pmcampaignnum = ? AND carttype = 'PR' AND status = 'PENDING'", $pmcampaignnum)
							->getMboSet()
							->fetchData();

			if (empty($cartItems)) {
				Super::sendJsonResponse("success", "There are no PR items in the cart that need to be processed");
				$tm->commit();
				return;
			}

			// Kelompokkan semua item berdasarkan VENDOR
			
			$itemsByVendor = [];
			foreach ($cartItems as $item) {
				// Gunakan key '_DEFAULT_' untuk item yang tidak memiliki vendor
				$vendorKey = !empty($item['vendorcode']) ? $item['vendorcode'] : '_DEFAULT_';
				$itemsByVendor[$vendorKey][] = $item;
			}

			// Pre-fetching (Tetap sama, ini masih efisien)
			$itemNums = []; $serviceNums = []; $woNums = [];
			foreach ($cartItems as $item) {
				if ($item['sourcetype'] == 'ITEM') $itemNums[] = $item['sourcenum'];
				if ($item['sourcetype'] == 'SERVICE') $serviceNums[] = $item['sourcenum'];
				$woNums[] = $item['wonum'];
			}
			
			$itemData = !empty($itemNums) ? (new Set())->useTransactionManager()->MboSetRemote("item")->whereIn("itemnum", array_unique($itemNums))->getMboSet()->fetchDataAssoc('itemnum') : [];
			$serviceData = !empty($serviceNums) ? (new Set())->useTransactionManager()->MboSetRemote("serviceitems")->whereIn("serviceitemnum", array_unique($serviceNums))->getMboSet()->fetchDataAssoc('serviceitemnum') : [];
			$woData = !empty($woNums) ? (new Set())->useTransactionManager()->MboSetRemote("workorder")->whereIn("wonum", array_unique($woNums))->getMboSet()->fetchDataAssoc('wonum') : [];

			// Inisialisasi variabel global
			$currentUser = Xbo::getLoginUser();
			$currentDate = Xbo::setEnterDate();
			$userInfo = Xbo::getOrgcode($currentUser);
			$createdPrNums = []; 

			// LOOP UTAMA - Satu iterasi per VENDOR
			
			foreach ($itemsByVendor as $vendorCode => $vendorItems) {
				
				// Reset variabel agregasi untuk setiap PR baru ---
				$totalCost = 0.0;
				$requiredDates = [];
				$prLineNum = 10;
				$prLinesToInsert = [];
				$processedCartIds = [];
				
			
				$newPrNum = Xbo::getNextAutoNumberApplication("PR", "updateNextVal");

				// Proses item HANYA untuk vendor saat ini ---
				foreach ($vendorItems as $cart) {
					$lineCost = (float)$cart['quantity'] * (float)$cart['unitcost'];
					$totalCost += $lineCost;
					$processedCartIds[] = $cart['procurementcartid'];
					
					$currentWo = $woData[$cart['wonum']] ?? null;
					$requiredDate = $currentWo ? $currentWo['targstartdate'] : $currentDate;
					$requiredDates[] = $requiredDate;

					$prLine = [
						'prnum' => $newPrNum,
						'prlinenum' => $prLineNum,
						'description' => $cart['description'],
						'unitcost' => (float)$cart['unitcost'],
						'linecost' => $lineCost,
						'orderqty' => (float)$cart['quantity'],
						'wonum' => $cart['wonum'],
						'requireddate' => $requiredDate,
						'enterby' => $currentUser,
						'enterdate' => $currentDate,
						'conversionfactor' => 1, 'receiptstatus' => 'NONE', 'warehouse' => null
					];

					if ($cart['sourcetype'] == 'ITEM') {
						$currentItem = $itemData[$cart['sourcenum']] ?? null;
						$prLine['linetype'] = 'item';
						$prLine['itemnum'] = $cart['sourcenum'];
						$prLine['orderunit'] = $currentItem ? $currentItem['orderunit'] : null;
						$prLine['manufacturer'] = $currentItem ? $currentItem['manufacturer'] : null;
						$prLine['gldebitacct'] = null;
					} elseif ($cart['sourcetype'] == 'SERVICE') {
						$currentService = $serviceData[$cart['sourcenum']] ?? null;
						$prLine['linetype'] = 'services';
						$prLine['serviceitemnum'] = $cart['sourcenum'];
						$prLine['orderunit'] = $currentService ? $currentService['serviceunit'] : null;
						$prLine['gldebitacct'] = $currentWo ? $currentWo['glaccount'] : null;
					}

					$prLinesToInsert[] = $prLine;
					$prLineNum += 10;
				}

				// --- Simpan PR & Lines untuk VENDOR ini ---
				if (!empty($prLinesToInsert)) {
					// Simpan PR Lines
					(new Set())->useTransactionManager()->MboSetRemote("prline")->addStream($prLinesToInsert);

					// Simpan PR Header
					$minRequiredDate = !empty($requiredDates) ? min($requiredDates) : $currentDate;
					(new Set())->useTransactionManager()
						->MboSetRemote("pr")
						->setValue("prnum", $newPrNum)
						->setValue("pmcampaignnum", $pmcampaignnum)
						->setValue("description", "PR for Campaign: " . $pmcampaignnum)
						->setValue("requestedby", $currentUser)
						->setValue("priority", "NORMAL")
						->setValue("status", "NEW")
						->setValue("statusdate", $currentDate)
						->setValue("requesteddate", $currentDate)
						->setValue("requireddate", $minRequiredDate)
						->setValue("exchangerate", 1)
						->setValue("pretaxtotal", $totalCost)
						->setValue("totaltax", 0)
						->setValue("totalcost", $totalCost)
						->setValue("totalbasecost", $totalCost)
						->setValue("enterdate", $currentDate)
						->setValue("enterby", $currentUser)
						->setValue("changeby", $currentUser)
						->setValue("changedate", $currentDate)
						->setValue("orgcode", $userInfo->orgcode)
						->setValue("sitecode", $userInfo->sitecode)
						->setValue("currencycode", Xbo::getBaseCurrency($userInfo->orgcode))
						->add();

					// Finalisasi & Audit untuk item-item VENDOR ini
					(new Set())->useTransactionManager()
						->MboSetRemote("procurementcart")
						->whereIn("procurementcartid", $processedCartIds)
						->setValue("status", "PROCESSED")
						->setValue("docnum", $newPrNum)
						->change();
					
					// Tambahkan nomor PR yang berhasil ke daftar
					$createdPrNums[] = $newPrNum;
				}
			} 
				
			$tm->commit();
			Super::sendJsonResponse("success", "Purchase Requisition(s) have been successfully created: " . implode(', ', $createdPrNums), [
				'prnums' => $createdPrNums
			]);

		} catch (Exception $e) {
			$tm->rollback();
			Super::sendJsonResponse("error", "Failed while creating PR: " . $e->getMessage());
		}
	}



	public static function updateProcurementCartBalloon() {
		try {
			$payload = Super::getParam("updateProcurementCartBalloon");
			
			if (empty($payload["pmcampaignnum"])) {
				throw new Exception("PM Campaign Number tidak boleh kosong.");
			}
			$pmcampaignnum = $payload["pmcampaignnum"];

			$set = new Set();
			$resultSet = $set->MboSetRemote("procurementcart")
							->where("pmcampaignnum = ? AND status = ?", $pmcampaignnum, 'PENDING')
							->MboRemote("COUNT(*) as item_count") // Gunakan COUNT(*) untuk efisiensi
							->getMboSet();
			
			$resultRow = $resultSet->fetch();
			
			$count = 0;
			if ($resultRow && isset($resultRow['item_count'])) {
				$count = (int)$resultRow['item_count'];
			}

			
			Super::sendJsonResponse("success", "Cart count retrieved successfully.", [
				'cart_count' => $count
			]);

		} catch (Exception $e) {
			Super::sendJsonResponse("error", $e->getMessage());
		}
	}
	public static function getWOsAndQuantitiesForCart($pmcampaignnum, $sourcetype, $sourcenum) {
	
		$campaignWos = [];
		$pmcampaignlineSet = (new Set())
			->MboSetRemote("pmcampaignline")
			->where("pmcampaignnum = ?", $pmcampaignnum)
			->MboRemote("wonum")
			->getMboSet();

		while ($res = $pmcampaignlineSet->fetch()) {
			$campaignWos[] = $res["wonum"];
		}

	
		if (empty($campaignWos)) {
			return [];
		}

		// Tentukan tabel sumber dan nama kolom yang benar.
		$sourceTable = null;
		$keyColumn = null;
		$quantityColumn = null;

		if ($sourcetype == "ITEM") {
			$sourceTable = "woplanmaterial"; // Tabel rencana material WO
			$keyColumn = "itemnum";      // Kolom untuk kode item
			$quantityColumn = "quantity"; // Kolom untuk kuantitas item
		} elseif ($sourcetype == "SERVICE") {
			$sourceTable = "woplanservice"; // Tabel rencana jasa WO
			$keyColumn = "serviceitemnum";  // Kolom untuk kode jasa
			$quantityColumn = "quantity";   // Kolom untuk kuantitas jasa
		} else {
			throw new Exception("Source Type is not valid: $sourcetype");
		}

		// Cari WO yang membutuhkan item/jasa tersebut, TAPI HANYA yang ada di dalam daftar WO kampanye.
		$resultSet = (new Set())
			->MboSetRemote($sourceTable)
			->whereIn("wonum", $campaignWos) 
			->where("$keyColumn = ?", $sourcenum)
			->MboRemote("wonum, $quantityColumn as quantity") 
			->getMboSet();

		return $resultSet->fetchData(); 
	}
	public static function submitToProcurementCart() {

		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();

		try {
			$payload = Super::getParam("submitToProcurementCart");

			// VALIDASI INPUT AWAL
			
			$requiredFields = ['pmcampaignnum', 'sourcetype', 'sourcenum', 'chargeType', 'description', 'estunitcost'];
			foreach ($requiredFields as $field) {
				if (empty($payload[$field])) {
					throw new Exception("Data tidak lengkap. Field '$field' wajib diisi.");
				}
			}
			
			if ($payload['chargeType'] === 'SQ' && empty($payload['customercode'])) {
				throw new Exception("Untuk Sales Quotation (SQ), Customer Code wajib diisi.");
			}

			// Ekstrak data utama dari payload
			$pmcampaignnum = $payload["pmcampaignnum"];
			$sourcetype    = $payload["sourcetype"];
			$sourcenum     = $payload["sourcenum"];
			$description   = $payload["description"];
			$chargetype    = $payload["chargeType"];
			$estunitcost   = $payload["estunitcost"];
			$customercode  = ($chargetype === 'SQ') ? $payload["customercode"] : null;
			$vendorcode  = ($chargetype === 'PR') ? $payload["vendorcode"] : null;
			$margin = ($chargetype === 'SQ') ? $payload["margin"] : 0;
			// PANGGIL FUNGSI HELPER UNTUK MENDAPATKAN RINCIAN WO
			$woRequirements = self::getWOsAndQuantitiesForCart($pmcampaignnum, $sourcetype, $sourcenum);

			if (empty($woRequirements)) {
				throw new Exception("No Work Order was found in this campaign that requires the specified item/service.");
			}

			// Ambil data dari sisi server (hanya perlu sekali)
			$currentUser = Xbo::getLoginUser();
			$currentDate = Xbo::setEnterDate();

			// LOOPING & PENYIMPANAN DATA PER WO
			foreach ($woRequirements as $req) {
				$wonum    = $req['wonum'];
				$quantity = $req['quantity'];

				// Pengecekan Duplikasi: Pastikan kombinasi WO + Item ini belum ada di keranjang
				$checkSet = new Set();
				$existingItem = $checkSet->useTransactionManager() 
										->MboSetRemote("procurementcart")
										->where("pmcampaignnum = ? AND sourcetype = ? AND sourcenum = ? AND carttype = ? AND wonum = ? AND status = ?", 
												$pmcampaignnum, $sourcetype, $sourcenum, $chargetype, $wonum, 'PENDING')
										->getMboSet();
				
				// Jika item untuk WO spesifik ini sudah ada, lewati ...
				if (!$existingItem->isEmpty()) {
					continue; 
				}

				// Jika kuantitas dari WO plan adalah 0 atau null, lewati...
				if (empty($quantity) || $quantity <= 0) {
					continue;
				}
				

				//Hitung Margin dan Unitprice
				$unitcost = $estunitcost;
				if ($chargetype === 'SQ') {
					$margin = $payload["margin"];
					$unitprice = $unitcost + ($unitcost * $margin / 100); 
				} else {
					$margin = 0;
					$unitprice = 0;
				}


				// Simpan baris baru ke procurementcart
				$set = new Set();
				$set->useTransactionManager() 
					->MboSetRemote("procurementcart")
					->setValue("pmcampaignnum", $pmcampaignnum)
					->setValue("enterby", $currentUser)
					->setValue("carttype", $chargetype)
					->setValue("sourcetype", $sourcetype)
					->setValue("sourcenum", $sourcenum)
					->setValue("description", $description)
					->setValue("quantity", $quantity) 
					->setValue("unitcost", $estunitcost)
					->setValue("customercode", $customercode)
					->setValue("vendorcode", $vendorcode)
					->setValue("wonum", $wonum)
					->setValue("status", 'PENDING')
					->setValue("enterdate", $currentDate)
					->setValue("margin", $margin)
					->setValue("unitprice", $unitprice)
					->add();
			}

			$tm->commit();

			Super::sendJsonResponse("success", "The requirement for the item/service has been successfully added to the cart.");

		} catch (Exception $e) {
			$tm->rollback();
			Super::sendJsonResponse("error", $e->getMessage());
		}
	}
	// Service Health - Fungsi Helper untuk mencari tahu Highest risk
	private static function calculateHighestRiskService($enriched_service_needs) {
		if (empty($enriched_service_needs)) {
			return null;
		}

		$highest_risk_value = -1;
		$highest_risk_service_details = null;

		// Loop melalui setiap jasa untuk menemukan yang memiliki 'risk_value' tertinggi.
		foreach ($enriched_service_needs as $service_detail) {
			// Filter jasa yang memiliki nilai risiko
			if (isset($service_detail['risk_value']) && $service_detail['risk_value'] > 0) {
				
				// Bandingkan dengan "juara bertahan"
				if ($service_detail['risk_value'] > $highest_risk_value) {
				
					$highest_risk_value = $service_detail['risk_value'];
					
					$highest_risk_service_details = [
						'serviceitemnum' => $service_detail['servicenum'],
						'description' => $service_detail['description'],
						'risk_value' => $service_detail['risk_value'],
						'risk_percent' => $service_detail['calculated_risk_percent']
					];
				}
			}
		}
		
		return $highest_risk_service_details;
	}
	
	
	private static function enrichServicesWithRiskData(&$service_needs) {
        if (empty($service_needs)) {
            return;
        }

        // 1. Ambil semua service number unik untuk satu query besar.
        $service_nums = array_keys($service_needs);

        // 2. Query ke tabel 'serviceitems' untuk mendapatkan semua faktor risiko.
        $riskDataSet = (new Set())->useTransactionManager()
                                  ->MboSetRemote("serviceitems")
                                  ->whereIn("serviceitemnum", $service_nums)
                                  ->MboRemote("serviceitemnum, costvolatilityrisk, leadtimerisk, complexityrisk")
                                  ->getMboSet();
        
        $risk_factors = [];
        while ($risk_data = $riskDataSet->fetch()) {
            $risk_factors[$risk_data['serviceitemnum']] = [
                'cost' => (float)$risk_data['costvolatilityrisk'],
                'lead' => (float)$risk_data['leadtimerisk'],
                'complexity' => (float)$risk_data['complexityrisk']
            ];
        }

        // 3. Loop melalui array kebutuhan jasa dan suntikkan data risiko.
        foreach ($service_needs as $serviceitemnum => &$service_detail) { 
            
            $cost_risk = 0;
            $lead_risk = 0;
            $complexity_risk = 0;

            if (isset($risk_factors[$serviceitemnum])) {
                $cost_risk = $risk_factors[$serviceitemnum]['cost'];
                $lead_risk = $risk_factors[$serviceitemnum]['lead'];
                $complexity_risk = $risk_factors[$serviceitemnum]['complexity'];
            }

            // --- Logika Kalkulasi Inti ---
            $base_cost = (float)$service_detail['total_cost'];
            
            // a. Hitung Total Persentase Risiko
            $calculated_risk_percent = $cost_risk + $lead_risk + $complexity_risk;
            
            // b. Hitung Nilai Risiko dalam mata uang
            $risk_value = $base_cost * ($calculated_risk_percent / 100);
            
            // c. Hitung Total Anggaran yang Direkomendasikan
            $recommended_budget = $base_cost + $risk_value;

            // --- Suntikkan hasilnya ke dalam array ---
            $service_detail['calculated_risk_percent'] = $calculated_risk_percent;
            $service_detail['risk_value'] = $risk_value;
            $service_detail['recommended_budget'] = $recommended_budget;
        }
    }
	
	
	private static function getEarliestServiceDate($serviceitemnum, $pmcampaignnum) {
        
        $serviceWoSet = (new Set())->useTransactionManager()
                                   ->MboSetRemote("pmcampaignline")
                                   ->setRelationship("woplanservice", "pmcampaignline.wonum = woplanservice.wonum")
                                   ->where("pmcampaignline.pmcampaignnum = ? AND woplanservice.serviceitemnum = ?", $pmcampaignnum, $serviceitemnum)
                                   ->MboRemote("MIN(COALESCE(pmcampaignline.revtargstartdate, pmcampaignline.targstartdate)) as earliest_date")
                                   ->getMboSet();
        
        if ($serviceWoSet->isEmpty()) {
            return null;
        }

        $result = $serviceWoSet->getMbo();
        return $result ? $result->getString("earliest_date") : null;
    }
	
	
	
	// Service Health - Fungsi helper untuk mencari tahu Pending Procurement (Servcie Item yang belum dilakukan procurement)
	private static function calculatePendingProcurementActions($pmcampaignnum) {
		$pending_actions = 0;

		// 1. Dapatkan daftar semua WO di dalam kampanye.
		$campaignWoSet = (new Set())->useTransactionManager()
									->MboSetRemote("pmcampaignline")
									->where("pmcampaignnum = ?", $pmcampaignnum)
									->MboRemote("wonum")
									->getMboSet();
		
		if ($campaignWoSet->isEmpty()) {
			return 0; 
		}

		$wo_nums_in_campaign = [];
		while ($line = $campaignWoSet->fetch()) {
			$wo_nums_in_campaign[] = $line['wonum'];
		}
		
		$sc = null;

		$externalServicesSet = (new Set())->useTransactionManager()
										  ->MboSetRemote("woplanservice")
										  ->setRelationship("serviceitems", "woplanservice.serviceitemnum = serviceitems.serviceitemnum")
										  ->whereIn("woplanservice.wonum", $wo_nums_in_campaign)
										  ->where("serviceitems.servicetype = 'EXTERNAL'")
										  ->getMboSet($sc);
		
		if ($externalServicesSet->isEmpty()) {
			return 0; 
		}
		
		while ($service_request = $externalServicesSet->fetch()) {
			$wonum = $service_request['wonum'];
			$servicenum = $service_request['serviceitemnum'];
			$is_procured = false; // belum ada jejak pengadaan


			// A. Cek Purchase Order (PO) - The Highest Truth
			$poLineSet = (new Set())->useTransactionManager()
									->MboSetRemote("poline pl")->setRelationship("po", "po.ponum = pl.ponum")
									->where("pl.wonum = ? AND pl.serviceitemnum = ? AND po.status NOT IN ('cancelled', 'closed')", $wonum, $servicenum)
									->getMboSet($sc);
			if (!$poLineSet->isEmpty()) {
				$is_procured = true; // Sudah ada PO, aman...
			}

			// B. Cek Request for Quotation (RFQ) - Jika belum ada PO
			if (!$is_procured) {
				$rfqLineSet = (new Set())->useTransactionManager()
										 ->MboSetRemote("rfqline rfql")->setRelationship("rfq", "rfql.rfqnum = rfq.rfqnum")
										 ->where("rfql.wonum = ? AND rfql.serviceitemnum = ? AND rfq.status IN ('active', 'sent', 'new')", $wonum, $servicenum)
										 ->getMboSet($sc);
				if (!$rfqLineSet->isEmpty()) {
					$is_procured = true; // Sedang dalam proses tender, aman..
				}
			}

			// C. Cek Purchase Requisition (PR) - Jika belum ada PO atau RFQ
			if (!$is_procured) {
				$prLineSet = (new Set())->useTransactionManager()
										->MboSetRemote("prline prl")->setRelationship("pr", "prl.prnum = pr.prnum")
										->where("prl.wonum = ? AND prl.serviceitemnum = ? AND pr.status IN ('approved', 'new')", $wonum, $servicenum)
										->whereNull("prl.rfqnum") // Validasi Konversi: Pastikan PR ini belum menjadi RFQ atau PO
										->whereNull("prl.ponum")
										->getMboSet($sc);
				if (!$prLineSet->isEmpty()) {
					$is_procured = true; // Ada PR yang sedang aktif, aman...
				}
			}
			
			if (!$is_procured) {
				// Jika setelah semua pengecekan flag tetap false, berarti servicen ini benar-benar pending.
				$pending_actions++;
			}
		}
		
		return $pending_actions;
	}

	
	/* Service Health - Helper Function for Internal Hours */
	private static function calculateTotalInternalHours($aggregated_services) {
        if (empty($aggregated_services)) {
            return 0.0;
        }

        $total_hours = 0.0;

        $service_nums = array_keys($aggregated_services);
        
        $serviceMasterData = [];
        if (!empty($service_nums)) {
            $serviceSet = (new Set())->useTransactionManager()
                                     ->MboSetRemote("serviceitems")
                                     ->whereIn("serviceitemnum", $service_nums)
                                     ->getMboSet();
            while ($service = $serviceSet->fetch()) {
                $serviceMasterData[$service['serviceitemnum']] = $service;
            }
        }

        foreach ($aggregated_services as $serviceitemnum => $details) {
            
            if (!isset($serviceMasterData[$serviceitemnum])) {
                continue; 
            }
            
            $master_data = $serviceMasterData[$serviceitemnum];

            if ($master_data['servicetype'] === 'INTERNAL') {
                $quantity = (float)$details['total_need'];
                $unit = $master_data['serviceunit'];
                $std_duration = (float)$master_data['stdduration'];

                switch ($unit) {
                    case 'HOUR':
                        $total_hours += $quantity;
                        break;
                    
                    case 'DAY':
                        $total_hours += $quantity * 8; 
                        break;
                    
                    // KASUS FALLBACK (untuk JOB, METER, UNIT, SESSION)
                    default:
                        // Gunakan stdduration dikalikan dengan kuantitas.
                        // Contoh: 2 JOB, masing-masing butuh 16 jam -> 32 jam.
                        // Contoh: 50 METER, setiap meter butuh 0.5 jam -> 25 jam.
                        if ($std_duration > 0) {
                            $total_hours += $quantity * $std_duration;
                        }
                        break;
                }
            }
        }
        return $total_hours;
    }
	

	private static function getProcurementStatusMap($pmcampaignnum) {
        $status_map = [];
		
        // Daftar semua WO di Campaign
        $campaignWoSet = (new Set())->useTransactionManager()->MboSetRemote("pmcampaignline")->where("pmcampaignnum = ?", $pmcampaignnum)->getMboSet();
        if ($campaignWoSet->isEmpty()) return [];
        $wo_nums = [];
        while ($line = $campaignWoSet->fetch()) {
            $wo_nums[] = $line['wonum'];
        }
        if (empty($wo_nums)) return [];
		
	
        // SEMUA jasa eksternal
        $externalServicesSet = (new Set())->useTransactionManager()->MboSetRemote("woplanservice")
            ->setRelationship("serviceitems", "woplanservice.serviceitemnum = serviceitems.serviceitemnum")
            ->whereIn("woplanservice.wonum", $wo_nums)
            ->where("serviceitems.servicetype = 'EXTERNAL'")
            ->getMboSet();

        if ($externalServicesSet->isEmpty()) return [];

        // 3. Inisialisasi peta: asumsikan SEMUA pending pada awalnya
        while ($service_request = $externalServicesSet->fetch()) {
            $key = $service_request['wonum'] . "_" . $service_request['serviceitemnum'];
            $status_map[$key] = true; // true = pending
        }
		
        // hierarki pengecekan untuk menonaktifkan flag 'pending'
        $all_keys = array_keys($status_map);
		//echo json_encode($all_keys);
        $all_wos = [];
        $all_services = [];
        foreach($all_keys as $key) {
            list($wo, $svc) = explode('_', $key);
            $all_wos[] = $wo;
            $all_services[] = $svc;
        }
        $all_wos = array_unique($all_wos);
        $all_services = array_unique($all_services);

        // Cek PO
       	 $poLineSet = (new Set())->useTransactionManager()->MboSetRemote("poline pl")->setRelationship("po", "pl.ponum = po.ponum")
            ->whereIn("pl.wonum", $all_wos)->whereIn("pl.serviceitemnum", $all_services)
            ->where("po.status NOT IN ('cancel')")->getMboSet();
        
		while($line = $poLineSet->fetch()) {
			$key = $line['wonum'] . "_" . $line['serviceitemnum'];
			if (isset($status_map[$key])) $status_map[$key] = false; // Ada PO, tidak pending
		}

			// Cek RFQ
		$rfqLineSet = (new Set())->useTransactionManager()->MboSetRemote("rfqline")->setRelationship("rfq", "rfqline.rfqnum = rfq.rfqnum")
				->whereIn("rfqline.wonum", $all_wos)->whereIn("rfqline.serviceitemnum", $all_services)
				->where("rfq.status NOT IN ('cancel')")->getMboSet();
		while($line = $rfqLineSet->fetch()) {
			$key = $line['wonum'] . "_" . $line['serviceitemnum'];
			if (isset($status_map[$key])) $status_map[$key] = false; // Ada RFQ, tidak pending
		}
        
			// Cek PR
		$prLineSet = (new Set())->useTransactionManager()->MboSetRemote("prline")->setRelationship("pr", "prline.prnum = pr.prnum")
			->whereIn("prline.wonum", $all_wos)->whereIn("prline.serviceitemnum", $all_services)
			->where("pr.status NOT IN ('cancel')")->whereNull("prline.rfqnum")->whereNull("prline.ponum")->getMboSet();
		while($line = $prLineSet->fetch()) {
			$key = $line['wonum'] . "_" . $line['serviceitemnum'];
			if (isset($status_map[$key])) $status_map[$key] = false; // Ada PR murni, tidak pending
		}
		return $status_map;
    }

    public static function serviceHealth() {
        $pmcampaignnum = Super::getParam("serviceHealth");
        $total_service_cost = 0;
        $service_needs = []; 

        $pmcampaignlineSet = (new Set())->useTransactionManager()->MboSetRemote("pmcampaignline")->where("pmcampaignnum=?", $pmcampaignnum)->getMboSet();
      
        if ($pmcampaignlineSet->isEmpty()) {
            // Jika tidak ada WO, kirim response kosong
            $emptyHealth = [
                "total_service_cost" => 0,
                "pending_procurement" => 0,
                "highest_procurement_risk" => null,
                "total_internal_hours" => 0,
                "service_details" => []
            ];
            Super::sendJsonResponse("success", "No work orders found in this campaign.", $emptyHealth);
            return;
        }


		$procurement_status_map = self::getProcurementStatusMap($pmcampaignnum);

		//Hitung Total Pending Procurement Actions
		$pending_procurement_actions = 0;
		foreach ($procurement_status_map as $is_pending) {
			if ($is_pending) {
				$pending_procurement_actions++;
			}
		}


        $wo_nums_in_campaign = [];
        while ($line = $pmcampaignlineSet->fetch()) {
			$wonum = $line['wonum'];
            $wo_nums_in_campaign[] = $wonum;
        }
		

        // --- Langkah 3: Satu Query Besar untuk semua Rencana Jasa (woplanservice) ---
        if (!empty($wo_nums_in_campaign)) {

            $woplanserviceSet = (new Set())->useTransactionManager()
                                           ->MboSetRemote("woplanservice")
                                           ->whereIn("wonum", $wo_nums_in_campaign)
                                           ->getMboSet();

            // --- Loop & Agregasi Data ---
            while ($service = $woplanserviceSet->fetch()) {
                $service_num = $service['serviceitemnum'];
                $line_cost = (float)$service['linecost'];
                $quantity = (float)$service['quantity'];
				
				if($service_num == null ) {
					$service_num = "With PR : ".$service["prnum"]; 
				}
                // Akumulasi Total Biaya Jasa untuk KPI
                $total_service_cost += $line_cost;

                // Agregasi kebutuhan per service unik
                if (!isset($service_needs[$service_num])) {
                    // Jika jasa ini baru pertama kali ditemukan
                    $service_needs[$service_num] = [
                        'servicenum' => $service_num,
                        'description' => $service['description'], // Ambil dari woplanservice
                        'total_need' => 0.0,
                        'total_cost' => 0.0,
                        'orderunit' => $service['orderunit']
                    ];
                }
                
                $service_needs[$service_num]['total_need'] += $quantity;
                $service_needs[$service_num]['total_cost'] += $line_cost;
            }
        }

        // --- Langkah 5: Siapkan data final ---
        
        // Ambil deskripsi master jika deskripsi di woplanservice kosong
        foreach ($service_needs as &$service_item) { // Pass by reference
            if (empty($service_item['description'])) {
                $masterDesc = (new Set())->useTransactionManager()->MboSetRemote("serviceitems")->where("servicenum=?", $service_item['servicenum'])->getMboSet()->getMbo();
                if ($masterDesc) {
                    $service_item['description'] = $masterDesc->getString("description");
                }
            }
			$earliest_date = self::getEarliestServiceDate($service_item['servicenum'], $pmcampaignnum);
			$service_item['earliest_wo_date'] = $earliest_date;
			$service_item['vendor_lead_time'] = null; 
			$service_item['procurement_gap'] = null;

			//cari pending procurement status
			$is_pending = false;
			$relatedWoSet = (new Set())->useTransactionManager()->MboSetRemote("woplanservice")
                                   ->where("serviceitemnum = ?", $service_item['servicenum'])
                                   ->whereIn("wonum", $wo_nums_in_campaign)
                                   ->getMboSet();

			while ($related_wo = $relatedWoSet->fetch()) {
				$key = $related_wo['wonum'] . "_" . $service_item['servicenum'];
				if (isset($procurement_status_map[$key]) && $procurement_status_map[$key] === true) {
					$is_pending = true;
					break; 
				}
			}
			
			$service_item['is_pending_procurement'] = $is_pending;
	
        }
        
		
		self::enrichServicesWithRiskData($service_needs); 
		$total_recommended_budget = 0;
		foreach ($service_needs as $service_item) {
			$total_recommended_budget += $service_item['recommended_budget'];
		}
	
		 
        $service_details = array_values($service_needs); 
		$total_internal_hours = self::calculateTotalInternalHours($service_needs);
		$pending_procurement_actions = self::calculatePendingProcurementActions($pmcampaignnum);
		$highest_risk_service_info = self::calculateHighestRiskService($service_needs);
        $serviceHealth = [
            "total_service_cost" => $total_service_cost,
			"total_recommended_budget" => $total_recommended_budget,
            "pending_procurement" => $pending_procurement_actions, 
            "highest_risk_service_info" => $highest_risk_service_info, 
            "total_internal_hours" => $total_internal_hours, // Placeholder
            "service_details" => $service_details
        ];

        Super::sendJsonResponse("success", "Service Health indicators loaded", $serviceHealth);
    }

	private static function calculateReadinessGap($itemnum, $earliest_wo_date) {
		if (empty($earliest_wo_date)) {
			return null; 
		}

		
		$vendorSet = (new Set())->useTransactionManager()
								->MboSetRemote("invvendor")
								->where("itemnum = ?", $itemnum)
								->MboRemote("MIN(intleadtime + extleadtime) as best_lead_time")
								->getMboSet();

		$best_lead_time_days = $vendorSet->getMbo() ? (int)$vendorSet->getMbo()->getString("best_lead_time") : null;

		if ($best_lead_time_days === null) {
			return null; // Tidak ada data vendor, tidak bisa menghitung gap.
		}
		
		// 2. Lakukan kalkulasi gap.
		$today = new DateTime();
		$required_date = new DateTime($earliest_wo_date);

		// Kapan barang diperkirakan tiba jika dipesan HARI INI
		$estimated_arrival = (clone $today)->modify("+{$best_lead_time_days} days");

		// Hitung selisihnya dalam hari
		$diff = $required_date->diff($estimated_arrival);
		$gap_days = (int)$diff->format('%r%a'); // %r gives sign (+/-), %a gives total days
		
		// Logika terbalik: Jika tanggal tiba SETELAH tanggal butuh, gap-nya negatif (terlambat)
		return $gap_days * -1;
	}


	private static function getEarliestWoDate($itemnum, $pmcampaignnum) {
		// 1. Dapatkan daftar semua WO di dalam kampanye ini.
		$campaignWoSet = (new Set())->useTransactionManager()
									->MboSetRemote("pmcampaignline")
									->where("pmcampaignnum = ?", $pmcampaignnum)
									->MboRemote("wonum, source, revtargstartdate, targstartdate")
									->getMboSet();

		if ($campaignWoSet->isEmpty()) {
			return null;
		}
		
		$wo_list = [];
		$forecast_list = [];
		while ($line = $campaignWoSet->fetch()) {
			if ($line['source'] === 'workorder') {
				$wo_list[] = $line['wonum'];
			} else {
				$forecast_list[] = $line['wonum'];
			}
		}
		
		$earliest_date = null;

		// 2. Cari tanggal paling awal dari semua WO (baik nyata maupun forecast)
		//    yang membutuhkan item ini.
		
		// Query untuk WO yang sudah ada di woplanmaterial
		if (!empty($wo_list)) {
			$date1 = null;
			$woMaterialSet = (new Set())->useTransactionManager()->MboSetRemote("woplanmaterial")
				->setRelationship("pmcampaignline", "woplanmaterial.wonum = pmcampaignline.wonum")
				->where("pmcampaignline.pmcampaignnum = ? AND woplanmaterial.itemnum = ?", $pmcampaignnum, $itemnum)
				->whereIn("woplanmaterial.wonum", $wo_list)
				->MboRemote("MIN(COALESCE(pmcampaignline.revtargstartdate, pmcampaignline.targstartdate)) as earliest")
				->getMboSet();
			
			$womat = $woMaterialSet->getMbo();
			$date1 = $womat->getString("earliest");
			if ($date1) $earliest_date = $date1;
		}

		// Query untuk WO yang masih di forecast (datanya ada di wtplanmaterial)
		if (!empty($forecast_list)) {
			$date2 = null;
			$forecastMaterialSet = (new Set())->useTransactionManager()->MboSetRemote("wtplanmaterial")
				->setRelationship("pmjpline", "wtplanmaterial.wtnum = pmjpline.wtnum")
				->setRelationship("pmcampaignline", "pmjpline.wonum = pmcampaignline.wonum")
				->where("pmcampaignline.pmcampaignnum = ? AND wtplanmaterial.itemnum = ?", $pmcampaignnum, $itemnum)
				->whereIn("pmjpline.wonum", $forecast_list)
				->MboRemote("MIN(COALESCE(pmcampaignline.revtargstartdate, pmcampaignline.targstartdate)) as earliest")
				->getMboSet();
			
			
			$forecast = $forecastMaterialSet->getMbo();
			$date2 = $forecast->getString("earliest");
			if ($date2 && ($earliest_date === null || new DateTime($date2) < new DateTime($earliest_date))) {
				$earliest_date = $date2;
			}
		}
		return $earliest_date;
	}


	private static function calculatePotentialSavings($shortage_items_list) {
		if (empty($shortage_items_list)) {
			return 0.0;
		}

		$total_savings = 0.0;
		$all_related_items = []; 

		foreach ($shortage_items_list as $item) {
			$all_related_items[] = $item['itemnum'];
			
			// Kumpulkan item alternatif
			$alternatesSet = (new Set())->useTransactionManager()->MboSetRemote("itemalternate")->where("itemnum=?", $item['itemnum'])->getMboSet();
			while ($alt = $alternatesSet->fetch()) {
				$all_related_items[] = $alt['relateditem'];
			}

			// Kumpulkan parent kits
			$parentItemSet = (new Set())->useTransactionManager()->MboSetRemote("itemstruct")->where("subitemnum=?", $item['itemnum'])->getMboSet();
			while ($parent = $parentItemSet->fetch()) {
				$all_related_items[] = $parent['itemnum'];
			}
		}
		
		$all_related_items = array_unique($all_related_items);

		// --- Langkah 2: Satu Query Besar untuk semua data stok dan biaya ---
		$inventory_data = [];
		if (!empty($all_related_items)) {
			$invBalanceSet = (new Set())->useTransactionManager()
										->MboSetRemote("v_invbalance")
										->whereIn("itemnum", $all_related_items)
										->getMboSet();
			while ($balance = $invBalanceSet->fetch()) {
				$inventory_data[$balance['itemnum']] = [
					'avgcost' => (float)$balance['avgcost'],
					'curbal'  => (float)$balance['curbal']
				];
			}
		}

		
		foreach ($shortage_items_list as $item) {
			$itemnum_needed = $item['itemnum'];
			$remaining_shortage = (float)$item['shortage'];
			
			$cost_of_new_item = isset($inventory_data[$itemnum_needed]) ? $inventory_data[$itemnum_needed]['avgcost'] : 0;
			if ($cost_of_new_item <= 0) continue; 

			// --- Prioritas #1: Cek Solusi Alternatif ---
			$alternatesSet = (new Set())->useTransactionManager()->MboSetRemote("itemalternate")->where("itemnum=?", $itemnum_needed)->getMboSet();
			while ($alt = $alternatesSet->fetch()) {
				if ($remaining_shortage <= 0) break; // Kebutuhan sudah terpenuhi

				$alt_itemnum = $alt['relateditem'];
				$alt_stock = isset($inventory_data[$alt_itemnum]) ? $inventory_data[$alt_itemnum]['curbal'] : 0;
				$alt_cost = isset($inventory_data[$alt_itemnum]) ? $inventory_data[$alt_itemnum]['avgcost'] : 0;

				// Cek apakah alternatif ini menguntungkan dan tersedia
				if ($alt_stock > 0 && $alt_cost < $cost_of_new_item) {
					$qty_can_cover = min($remaining_shortage, $alt_stock);
					$saving_per_unit = $cost_of_new_item - $alt_cost;
					$total_savings += $saving_per_unit * $qty_can_cover;
					$remaining_shortage -= $qty_can_cover;
				}
			}

			// --- Prioritas #2: Cek Solusi "Harvest from Kit" ---
			if ($remaining_shortage > 0) {
				$parentItemSet = (new Set())->useTransactionManager()->MboSetRemote("itemstruct")->where("subitemnum=?", $itemnum_needed)->getMboSet();
				while ($parent = $parentItemSet->fetch()) {
					if ($remaining_shortage <= 0) break;

					$parent_itemnum = $parent['itemnum'];
					$parent_stock = isset($inventory_data[$parent_itemnum]) ? $inventory_data[$parent_itemnum]['curbal'] : 0;
					$yields_qty = (float)$parent['quantity'];

					if ($parent_stock > 0 && $yields_qty > 0) {
						// Hitung berapa banyak kit yang perlu dibongkar
						$kits_to_disassemble = ceil($remaining_shortage / $yields_qty);
						//  bongkar sebanyak stok yang ada
						$kits_to_disassemble = min($kits_to_disassemble, $parent_stock);

						$qty_can_cover = $kits_to_disassemble * $yields_qty;
						$qty_actually_covered = min($remaining_shortage, $qty_can_cover);
						
						// Penghematan di sini adalah biaya penuh dari item baru yang tidak perlu dibeli
						$total_savings += $cost_of_new_item * $qty_actually_covered;
						$remaining_shortage -= $qty_actually_covered;
					}
				}
			}
		}
		
		return $total_savings;
	}
	private static function calculateHighestRiskItem($shortage_items) {
		
        if (empty($shortage_items)) {
            return null;
        }
		//echo json_encode($shortage_items)."<hr>";
        $item_nums = array_column($shortage_items, 'itemnum');
        //echo json_encode($item_nums)."<hr>";
        $itemRiskSet = (new Set())->useTransactionManager()
                                  ->MboSetRemote("item")
                                  ->whereIn("itemnum", $item_nums)
                                  ->MboRemote("itemnum, marketrarityranking, criticalityranking, usageranking")
                                  ->getMboSet();
        
        $item_risk_data = [];
        while ($risk_data = $itemRiskSet->fetch()) {
            $item_risk_data[$risk_data['itemnum']] = $risk_data;
        }

        $highest_risk_score = -1;
        $highest_risk_item_details = null;

        foreach ($shortage_items as $item) {
            $itemnum = $item['itemnum'];
		
            $risk_info = isset($item_risk_data[$itemnum]) ? $item_risk_data[$itemnum] : null;

            // --- Logika Konversi Peringkat (A/B/C) ke Skor ---
            // Jika data null/kosong, skornya 0.
            $rarity_score = self::getScoreFromRank($risk_info ? $risk_info['marketrarityranking'] : null);
            $criticality_score = self::getScoreFromRank($risk_info ? $risk_info['criticalityranking'] : null);
            $usage_score = self::getScoreFromRank($risk_info ? $risk_info['usageranking'] : null);
            
            // --- Rumus Kalkulasi Skor Risiko Total ---
            // Bobot yang berbeda bisa diterapkan. Criticality adalah yang paling penting.
            // Rumus: (Skor Kelangkaan * Bobot) + (Skor Kekritisan * Bobot) + (Skor Penggunaan * Bobot)
			/*
			Criticality (Kekritisan) -> Bobot 0.5 (50%) - Paling Tinggi: Ini adalah faktor yang paling penting. Sebuah item mungkin jarang dipakai dan mudah didapat, 
			tapi jika item itu adalah sekering utama yang jika gagal akan menyebabkan seluruh pabrik padam, maka kekritisannya sangat tinggi. 
			Gagalnya item ini memiliki dampak operasional dan finansial terbesar. Oleh karena itu, 
			ia harus memiliki bobot yang paling berat dalam perhitungan risiko.
			
			Market Rarity (Kelangkaan Pasar) -> Bobot 0.3 (30%) - Cukup Tinggi. Ini adalah faktor risiko rantai pasok. Sebuah baut mungkin tidak terlalu kritis, 
			tapi jika baut itu adalah baut khusus titanium yang hanya dibuat oleh satu pabrik di Jerman dengan lead time 6 bulan, 
			maka ketidaktersediaannya adalah risiko besar. Gagal mengelola item ini berarti downtime yang sangat panjang
			
			Usage (Penggunaan) -> Bobot 0.2 (20%) - Paling Rendah
			Ini adalah faktor risiko probabilitas. Item yang sering dipakai (Kelas A dalam analisis ABC) secara statistik memiliki kemungkinan lebih tinggi untuk habis stoknya. 
			Meskipun dampaknya mungkin tidak sebesar item kritis, frekuensi kebutuhannya menjadikannya sebuah risiko yang harus dipantau. 
			Bobotnya paling rendah karena kekurangan item yang sering dipakai biasanya lebih mudah diatasi (misalnya, beli di toko terdekat) dibandingkan kekurangan item kritis atau langka.
			*/
            $total_risk_score = ($rarity_score * 0.3) + ($criticality_score * 0.5) + ($usage_score * 0.2);

            // 4. Bandingkan dan simpan item dengan skor tertinggi.
            if ($total_risk_score > $highest_risk_score) {
                $highest_risk_score = $total_risk_score;
                $highest_risk_item_details = [
                    'itemnum' => $itemnum,
                    'description' => $item['description'],
                    'risk_score' => round($total_risk_score * 10), // Ubah skala 0-10 menjadi 0-100 agar lebih mudah dibaca
                    'details' => [
                        'market_rarity' => $risk_info ? $risk_info['marketrarityranking'] : 'N/A',
                        'criticality' => $risk_info ? $risk_info['criticalityranking'] : 'N/A',
                        'usage' => $risk_info ? $risk_info['usageranking'] : 'N/A'
                    ]
                ];
            }
        }
        
        return $highest_risk_item_details;
    }
	
	private static function getScoreFromRank($rank) {
        switch (strtoupper($rank)) {
            case 'A': return 10;
            case 'B': return 5;
            case 'C': return 1;
            default: return 0; 
        }
    }
	
	public static function materialHealth() {
		$pmcampaignnum = Super::getParam("materialHealth");
		$material_needs = [];
		$total_planned_cost = 0;
		$total_material_cost = 0;
		$total_shortage_items = 0;
		$pmcampaignlineSet = (new Set())->useTransactionManager()->MboSetRemote("pmcampaignline")->where("pmcampaignnum=?", $pmcampaignnum)->getMboSet();
    
		if ($pmcampaignlineSet->isEmpty()) {
			Super::sendJsonResponse("error", "No work orders found in this campaign.", null);
			return;
		}

		while ($campaign_line = $pmcampaignlineSet->fetch()) {
			$wonum = $campaign_line['wonum'];
			$source = $campaign_line['source'];
			$material_source_set = null; 
			$wocost = Xbo::calculateWOCost($wonum);
			if ($source === "workorder") {
				$material_source_set = (new Set())->useTransactionManager()->MboSetRemote("woplanmaterial")->where("wonum=?", $wonum)->getMboSet();
				$total_planned_cost += $wocost->plannedcost;
				$total_material_cost += $wocost->planmaterialcost;
				
			} else if ($source === "pmwoline") {
				$forecastWoCost = Xbo::calculateForecastWOCost($wonum);
				
				if ($forecastWoCost) { 
					$job_plans_to_query = [];
					$jpSet = (new Set())->useTransactionManager()->MboSetRemote("pmjpline")->where("wonum=?", $wonum)->getboSet();
					while ($jp = $jpSet->fetch()) {
						$job_plans_to_query[] = $jp['wtnum'];
					}
					
					if (!empty($job_plans_to_query)) {
						$material_source_set = (new Set())->useTransactionManager()->MboSetRemote("wtplanmaterial")->whereIn("wtnum", $job_plans_to_query)->getboSet();
					}
					
					// Baris ini sekarang aman karena berada di dalam blok 'if'
					$total_planned_cost += $forecastWoCost->plannedcost;
					$total_material_cost += $forecastWoCost->planmaterialcost;
				}
				
			}
		
			// Loop agregasi yang sama untuk kedua sumber
			if ($material_source_set && !$material_source_set->isEmpty()) {
				while ($material = $material_source_set->fetch()) {
					$itemnum = $material['itemnum'];
					
					$unitcost = (new Set())->useTransactionManager()->MboSetRemote("v_invbalance")->where("itemnum=?", $itemnum)->MboRemote("avgcost")
					->getMboSet()->getMbo()->getDecimal("avgcost");
					// tambahkan buffer 15% untuk unitcost
					$estunitcost = $unitcost * 1.15;

					$quantity = (float)$material['quantity'];
					
					if (!isset($material_needs[$itemnum])) {
						$material_needs[$itemnum] = [
							'itemnum' => $itemnum, 
							'total_need' => 0,
							'estunitcost' => 0, // <-- TAMBAHKAN INISIALISASI DI SINI
							'work_orders' => []
							];
					}
					$woSet = (new Set())->useTransactionManager()->MboSetRemote("workorder")->where("wonum=?", $wonum)->MboRemote("assetnum, description, priority")->getMboSet();
					$wo = $woSet->getMbo();
					
					$assetnum = $wo->getString("assetnum");
					$description = $wo->getString("description");
					$priority = $wo->getString("priority");
					$teamnum = $wo->getString("teamnum");
					$leader = $wo->getString("leader");
					$pmnum = $wo->getString("pmnum");
					$material_needs[$itemnum]['total_need'] += $quantity;
					$material_needs[$itemnum]['estunitcost'] += $estunitcost;
					$material_needs[$itemnum]['work_orders'][] = [
						'wonum' => $wonum,
						'required_date' => $campaign_line['revtargstartdate'] ?: $campaign_line['targstartdate'],
						'qty' => $quantity,
						'priority' => $priority,
						'description' => $description,
						'assetnum' => $assetnum,
						'teamnum' => $teamnum,
						'leader' => $leader,
						'pmnum' => $pmnum
					];

				}
			}
		}
		
		$item_keys = array_keys($material_needs);
		if (!empty($item_keys)) {
			//Ambil semua deskripsi item
			$itemDetailsSet = (new Set())->useTransactionManager()->MboSetRemote("item")->whereIn("itemnum", $item_keys)->getMboSet();
			while ($item_detail = $itemDetailsSet->fetch()) {
				if (isset($material_needs[$item_detail['itemnum']])) {
					$material_needs[$item_detail['itemnum']]['description'] = $item_detail['description'];
				}
			}
			//Ambil semua data stok
			$invBalanceSet = (new Set())->useTransactionManager()->MboSetRemote("v_invbalance vib")
			->whereIn("vib.itemnum", $item_keys)
			->setRelationship("item it", "vib.itemnum = it.itemnum")
			->MboRemote("vib.itemnum, vib.curbal, vib.avgcost, it.description")
			->getMboSet();
			 
			$inventory_data = [];
			while ($balance = $invBalanceSet->fetch()) {
				$inventory_data[$balance['itemnum']] = [
					'curbal' => (float)$balance['curbal'],
					'avgcost' => (float)$balance['avgcost'],
					'description' => $balance['description']
				];

			}

			// Lakukan kalkulasi akhir ---
			foreach ($material_needs as &$item) { //  '&' untuk pass by reference
				
				$current_stock = isset($inventory_data[$item['itemnum']]) ? $inventory_data[$item['itemnum']]['curbal'] : 0;
				$item['current_stock'] = $current_stock;
				$item['measureunit'] = (new Set())->useTransactionManager()->MboSetRemote("item")->where("itemnum=?", $item['itemnum'])->MboRemote("issueunit")->getMboSet()->getMbo()->getString("issueunit");
				$shortage = $item['total_need'] - $current_stock;
				$item['shortage'] = ($shortage > 0) ? $shortage : 0;
				if ($item['shortage'] > 0) {
					$total_shortage_items++;
				}
				
				// Hitung Earliest Date & Gap
				$earliest_date = self::getEarliestWoDate($item['itemnum'], $pmcampaignnum);
				$readiness_gap = self::calculateReadinessGap($item['itemnum'], $earliest_date);
				$item['earliest_wo_date'] = $earliest_date;
				$item['readiness_gap'] = $readiness_gap;
				
				 // Inisialisasi array untuk solusi
				$item['internal_solutions'] = [
					'alternates' => [],
					'kits' => []
				];
				
				if ($item['shortage'] > 0) {
					// A. Mencari Item Alternatif
					$alternatesSet = (new Set())->useTransactionManager()->MboSetRemote("itemalternate")->where("itemnum=?", $item['itemnum'])->getMboSet();
					while ($alt = $alternatesSet->fetch()) {
						$alt_itemnum = $alt['relateditem'];
						// Ambil info stok & biaya dari data di Langkah 2
						$alt_stock = isset($inventory_data[$alt_itemnum]) ? $inventory_data[$alt_itemnum]['curbal'] : 0;
						$alt_cost = isset($inventory_data[$alt_itemnum]) ? $inventory_data[$alt_itemnum]['avgcost'] : 0;

						// Hanya tambahkan alternatif yang benar-benar ada stoknya
						if ($alt_stock > 0) {
							$item['internal_solutions']['alternates'][] = [
								'itemnum' => $alt_itemnum,
								'description' => $inventory_data[$alt_itemnum]['description'],
								'current_stock' => $alt_stock,
								'avgcost' => $alt_cost
							];
						}
					}

					// B. Mencari Opsi "Harvest from Kit"
					$parentItemSet = (new Set())->useTransactionManager()->MboSetRemote("itemstruct")->where("subitemnum=?", $item['itemnum'])->getMboSet();
					while ($parent = $parentItemSet->fetch()) {
						$parent_itemnum = $parent['itemnum'];
						$parent_stock = isset($inventory_data[$parent_itemnum]) ? $inventory_data[$parent_itemnum]['curbal'] : 0;
						
						if ($parent_stock > 0) {
							$item['internal_solutions']['kits'][] = [
								'itemnum' => $parent_itemnum,
								'description' => $inventory_data[$parent_itemnum]['description'],
								'current_stock' => $parent_stock,
								'yields_qty' => (float)$parent['quantity']
							];
						}
					}
				}
			}
		}
		
	
		$shortage_items_list = array_filter($material_needs, function($item) {
			return isset($item['shortage']) && $item['shortage'] > 0;
		});
    
		$potential_savings = self::calculatePotentialSavings(array_values($shortage_items_list));
		$highest_risk_item_info = self::calculateHighestRiskItem(array_values($shortage_items_list));
		//echo json_encode($shortage_items_list);
		//echo "<hr>";
		//echo "highest_risk_item_info: <hr>";
		//echo json_encode($highest_risk_item_info);
		//echo "<hr>";
		$materialHealth = [
			"total_material_cost" => $total_material_cost,
			"total_shortage_items" => $total_shortage_items,
			"highest_risk_item" => $highest_risk_item_info,
			"potential_internal_savings" => $potential_savings,
			"material_details" => array_values($material_needs)
		];

		Super::sendJsonResponse("success", "Material Health indicators loaded", $materialHealth);
	}
	
	public static function applyResSmoothing() {
        // 1. Ambil data input dari request.
        $ganttTasks = Super::getParam("applyResSmoothing");
        $pmcampaignnum = Super::getParam("pmcampaignnum");
        
        if (empty($ganttTasks) || empty($pmcampaignnum)) {
            Super::sendJsonResponse("error", "Required data (tasks or campaign number) is missing.");
            return;
        }

        $tm = TransactionManager::getInstance();
        $tm->beginTransaction();

        try {
            $enterby = Xbo::getLoginUser();
            $enterdate = Xbo::setEnterDate();
            foreach ($ganttTasks as $task) {
                $wonum = $task["id"];
                $source = $task["source"];
                $new_start_date = $task["start"];
                $new_end_date = $task["end"];
                
                $new_teamnum = isset($task["teamnum"]) ? $task["teamnum"] : null;
                $new_duration = isset($task["duration"]) ? $task["duration"] : null;
                $new_priority = isset($task["priority"]) ? $task["priority"] : null;
                $new_leader = null;
                if ($new_teamnum) {
                    $new_leader = Xbo::getTeamLeader($new_teamnum)->personid;
                }
                $remarks = "Rescheduled by PM Campaign {$pmcampaignnum} for resource optimization.";
                if ($source === 'workorder') {
                    $updateSet = (new Set())->useTransactionManager()->MboSetRemote("workorder")->where("wonum = ?", $wonum);
                    
                    $updateSet->setValue("targstartdate", $new_start_date);
                    $updateSet->setValue("targcompdate", $new_end_date);
                    $updateSet->setValue("remarks", $remarks);
                    $updateSet->setValue("priority", $new_priority);
					
                    if ($new_teamnum) $updateSet->setValue("teamnum", $new_teamnum);
                    if ($new_leader) $updateSet->setValue("leader", $new_leader);
                    if ($new_duration !== null) $updateSet->setValue("duration", $new_duration);

                    $updateSet->change();
					
					/* update revtargstartdate & revtargenddate */
					(new Set())->useTransactionManager()->MboSetRemote("pmcampaignline")
					->where("wonum = ?", $wonum)
					->setValue("revtargstartdate", $new_start_date)
					->setValue("revtargenddate", $new_end_date)
					->change();
					
                } elseif ($source === 'pmwoline') {
                    $updateSet = (new Set())->useTransactionManager()->MboSetRemote("pmwoline")->where("wonum = ?", $wonum);

                    $updateSet->setValue("targstartdate", $new_start_date);
                    $updateSet->setValue("targenddate", $new_end_date);
                    $updateSet->setValue("remarks", $remarks);

                    if ($new_teamnum) $updateSet->setValue("teamnum", $new_teamnum);
                    if ($new_leader) $updateSet->setValue("leader", $new_leader);
                    if ($new_duration !== null) $updateSet->setValue("duration", $new_duration);
                    
                    $updateSet->change();
					
					/* update revtargstartdate & revtargenddate */
					(new Set())->useTransactionManager()->MboSetRemote("pmcampaignline")
					->where("wonum = ?", $wonum)
					->setValue("revtargstartdate", $new_start_date)
					->setValue("revtargenddate", $new_end_date)
					->change();
					
                }
            }

            $tm->commit();
            Super::sendJsonResponse("success", "Resource Smoothing has been successfully applied to the schedule.");

        } catch (Exception $e) {
            $tm->rollback();
            throw $e; 
        }
    }
	public static function deletePresets() {
       
        $payload = Super::getParam();
        $presets_to_delete = isset($payload["deletePresets"]) ? $payload["deletePresets"] : null;
        $pmcampaignnum = isset($payload["pmcampaignnum"]) ? $payload["pmcampaignnum"] : null;

        if (empty($presets_to_delete) || !is_array($presets_to_delete) || empty($pmcampaignnum)) {
            Super::sendJsonResponse("error", "An array of presets to delete and a campaign number are required.");
            return;
        }

        $pmpresetids = [];
        foreach ($presets_to_delete as $preset_data) {
            if (isset($preset_data['value'])) {
                $pmpresetids[] = (int)$preset_data['value']; // Konversi ke integer untuk keamanan
            }
        }

        if (empty($pmpresetids)) {
            Super::sendJsonResponse("error", "No valid preset IDs found in the payload.");
            return;
        }
        
        $tm = TransactionManager::getInstance();
        $tm->beginTransaction();

        try {
            $placeholders = implode(',', array_fill(0, count($pmpresetids), '?'));
            $validationSet = (new Set())->useTransactionManager()
                                        ->MboSetRemote("pmpreset")
                                        ->where("pmcampaignnum = ? AND pmpresetid IN ({$placeholders})", $pmcampaignnum, ...$pmpresetids)
                                        ->getMboSet();
            
            if ($validationSet->counts() !== count($pmpresetids)) {
                $tm->rollback();
                Super::sendJsonResponse("error", "One or more presets could not be verified or do not belong to this campaign.");
                return;
            }

            (new Set())->useTransactionManager()
                       ->MboSetRemote("pmpresetline")
                       ->where("pmpresetid IN ({$placeholders})", ...$pmpresetids)
                       ->delete();
            
            (new Set())->useTransactionManager()
                       ->MboSetRemote("pmpreset")
                       ->where("pmpresetid IN ({$placeholders})", ...$pmpresetids)
                       ->delete();
            
            $tm->commit();
            Super::sendJsonResponse("success", count($pmpresetids) . " preset(s) have been deleted successfully.");

        } catch (Exception $e) {
            $tm->rollback();
            throw $e;
        }
    }
	
	public static function loadPreset() {
		$pmcampaignnum = Super::getParam("pmcampaignnum");
		$pmpresetid = Super::getParam("loadPreset");

		if (empty($pmcampaignnum) || empty($pmpresetid)) {
			Super::sendJsonResponse("error", "Campaign number and Preset ID are required.", []);
			return;
		}
		
		try {
			$campaign_tasks = self::getPresetTaskForGantt($pmcampaignnum, $pmpresetid);
			Super::sendJsonResponse("success", "Preset has been loaded successfully.", $campaign_tasks);

		} catch (Exception $e) {
			Super::sendJsonResponse("error", "An error occurred while loading the preset: " . $e->getMessage(), []);
		}
	}
	
	private static function getPresetTaskForGantt($pmcampaignnum, $pmpresetid) {
		$campaignLineSet = (new Set())->useTransactionManager()
									  ->MboSetRemote("pmpresetline")
									  ->where("pmpresetline.pmcampaignnum = ? and pmpresetid=?", $pmcampaignnum, $pmpresetid)
									  ->order("pmpresetline.start ASC")
									  ->getMboSet();
		
		if ($campaignLineSet->isEmpty()) {
			return [];
		}
		$importedBy = Xbo::getLoginUser();
		$tasks_for_gantt = [];
		while ($line = $campaignLineSet->fetch()) {
			$wonum = $line['wonum'];
			$teamnum = $line['teamnum'];
			$start_date = new DateTime($line['start']);
			$end_date = new DateTime($line['end']);
			$status = "FORECASTING";
			$pmwoline = (new Set())->useTransactionManager()->MboSetRemote("pmwoline")->where("wonum = ? ", $wonum)->getMboSet()->getMbo();
			$description = $pmwoline->getString("description");
			/*
			$teamnum = $pmwoline->getString("teamnum");
			*/
			$leader = $pmwoline->getString("leader");
			$islocked = ($pmwoline->getString("isschedulable") === 'N') ? 'Y' : 'N'; 
			$pmnum = $pmwoline->getString("pmnum");
			$forecastCostData = Xbo::calculateForecastWOCost($wonum);
			$plannedcost = 0; 
			if ($forecastCostData) { 
				$plannedcost = $forecastCostData->plannedcost;
			}
			$leader = $importedBy;
			$owner = $importedBy;
			/*
			$pm = (new Set())->MboSetRemote("pm")->where("pmnum=?", $pmnum)->getMboSet()->getMbo();
			$orgcode = $pm->getString("orgcode");
			if(empty($teamnum)){
				$teamnum = $pm->getString("teamnum");
			}*/
			$wo = (new Set())->MboSetRemote("workorder")->where("wonum=?", $wonum)->getMboSet()->getMbo();
			$workorderid = $wo->getString("workorderid");
			$priority =  $line['priority'];
			$wostatus = $wo->getString("status");
			$leader = empty($wo->getString("leader")) ? $leader : $wo->getString("leader");
			$owner  = empty($wo->getString("owner"))  ? $owner  : $wo->getString("owner");
			$status  = empty($wo->getString("status"))  ? $status  : $wo->getString("status");
			$hard_locked_statuses = ['SCHED', 'INPROGRESS', 'COMPLETED', 'CLOSE', 'CANCEL']; 
			if (in_array(strtoupper($wostatus), $hard_locked_statuses)) {
				$islocked = 'Y';
			} else {
				if ($owner === $importedBy || $leader === $importedBy) {
					$islocked = 'N'; 
				}
			}
			
			if(empty($priority)) $priority = "NORMAL";
			/*if(empty($teamnum)) {
				$teamnum = $wo->getString("teamnum");
			}*/
			
			
			if(empty($orgcode)){
				$orgcode = $wo->getString("orgcode");
			}
			
			if(empty($description)){
				$description = $wo->getString("description");
			}
			
			$costData = Xbo::calculateWOCost($wonum);
			$plannedcost = 0; // Berikan nilai default
			if ($costData) { // Cek apakah hasilnya bukan null
				$plannedcost = $costData->plannedcost;
			}

			$plannedcost = $costData->plannedcost;
			
			
			$pmcampaignline = (new Set())->MboSetRemote("pmcampaignline")->where("pmcampaignnum=? and wonum=?", $pmcampaignnum, $wonum)->MboRemote("source")->getMboSet()->getMbo();
			
			$source = $pmcampaignline->getString("source");
			$currency = (new Set())->useTransactionManager()->MboSetRemote("organization")->where("orgcode=?", $orgcode)->getMboSet()->getMbo()->getString("basecurrency");
			$tasks_for_gantt[] = [
				'id'       => $wonum, // ID unik untuk Gantt
				'name'     => $description,
				'start'    => $start_date->format('Y-m-d'),
				'end'      => $end_date->format('Y-m-d'),
				'hours'    => (float)$line['hours'], // Gunakan durasi/Duration
				'cost'     => (float)$plannedcost, // Ambil dari workorder
				'currency' => $currency,
				'teamnum'  => $teamnum,
				'isLocked' => $islocked,
				'workorderid' => $workorderid,
				'priority' => strtoupper($priority),
				'progress' => (int)$line['progress'], // Ambil dari workorder
				'status' => $status,
				'source' => $source
			];
		}
		
		return $tasks_for_gantt;
	}




	public static function savepreset() {
		$payload = Super::getParam();
		if (!isset($payload["savepreset"]) || !isset($payload["savepreset"]["pmpreset"]) || !isset($payload["savepreset"]["pmpresetlines"])) {
			self::sendJsonResponse("error", "Invalid payload structure for saving preset.");
			return;
		}

		$pmpreset_header = $payload["savepreset"]["pmpreset"];
		$pmpreset_lines = $payload["savepreset"]["pmpresetlines"];
		
		$pmcampaignnum = isset($pmpreset_header["pmcampaignnum"]) ? $pmpreset_header["pmcampaignnum"] : null;
		$presetnum = isset($pmpreset_header["presetnum"]) ? $pmpreset_header["presetnum"] : null;
		$preset_desc = isset($pmpreset_header["preset_desc"]) ? $pmpreset_header["preset_desc"] : "Saved Preset";

		if (empty($pmcampaignnum) || empty($presetnum)) {
			self::sendJsonResponse("error", "Campaign number and Preset number are required.");
			return;
		}

		$enterby = Xbo::getLoginUser();
		$enterdate = Xbo::setEnterDate();

		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();

		try {
			$existingPresetSet = (new Set())->useTransactionManager()
											->MboSetRemote("pmpreset")
											->where("pmcampaignnum = ? AND pmpresetnum = ?", $pmcampaignnum, $presetnum)
											->getMboSet();
			
			$pmpresetid = null;
			if ($existingPresetSet->isEmpty()) {
				$addPresetSet = (new Set())->useTransactionManager()->MboSetRemote("pmpreset");
				$addPresetSet->setValue("pmcampaignnum", $pmcampaignnum)
							 ->setValue("pmpresetnum", $presetnum)
							 ->setValue("description", $preset_desc)
							 ->setValue("isactive", "Y")
							 ->setValue("enterby", $enterby)
							 ->setValue("enterdate", $enterdate)
							 ->add();
				$pmpresetid = $addPresetSet->getTableID; 
			} else {
				$existingPreset = $existingPresetSet->getMbo();
				$pmpresetid = $existingPreset->getInt("pmpresetid");
				
				(new Set())->useTransactionManager()->MboSetRemote("pmpreset")
						   ->where("pmpresetid = ?", $pmpresetid)
						   ->setValue("description", $preset_desc)
						   ->setValue("changeby", $enterby)
						   ->setValue("changedate", $enterdate)
						   ->change();
			}
			(new Set())->useTransactionManager()
					   ->MboSetRemote("pmpresetline")
					   ->where("pmpresetid = ?", $pmpresetid)
					   ->remove();
				
			foreach ($pmpreset_lines as $line) {
				$linenumber = Xbo::getLinenum("pmpresetline", "pmpresetlinenum", ["pmpresetid" => $pmpresetid]);
				(new Set())->useTransactionManager()
						   ->MboSetRemote("pmpresetline")
						   ->setValue("pmpresetid", $pmpresetid)
						   ->setValue("pmpresetlinenum", $linenumber)
						   ->setValue("pmcampaignnum", $pmcampaignnum)
						   ->setValue("wonum", $line["id"]) // 'id' dari Gantt adalah 'wonum' 
						   ->setValue("start", $line["start"])
						   ->setValue("end", $line["end"])
						   ->setValue("hours", $line["hours"])
						   ->setValue("priority", $line["priority"])
						   ->setValue("progress", $line["progress"])
						   ->setValue("teamnum", $line["teamnum"])
						   ->setValue("islocked", $line["isLocked"] ? "Y" : "N")
						   ->add();
			}
			
			$tm->commit();
			Super::sendJsonResponse("success", "Preset '" . $preset_desc . "' has been saved successfully.");

		} catch (Exception $e) {
			$tm->rollback();
			throw $e;
		}
	}
	public static function refreshHistogram() {
		$pmcampaignnum = Super::getParam("refreshHistogram");
		$delPresetOnRefresh = Super::getParam("delPresetOnRefresh");
		$campaign_tasks = self::getCampaignTasksForGantt($pmcampaignnum);
		if($delPresetOnRefresh == "Y"){
			(new Set())->MboSetRemote("pmpreset")->where("pmcampaignnum=?", $pmcampaignnum)->delete();
			(new Set())->MboSetRemote("pmpresetline")->where("pmcampaignnum=?", $pmcampaignnum)->delete();
		}
		
		Super::sendJsonResponse("success", "Gantt Chart has been Refreshed", $campaign_tasks);
	}
	public static function importWOs(){
		$payload = Super::getParam("importWOs");
		$pmcampaignnum = isset($payload["pmcampaignnum"]) ? $payload["pmcampaignnum"] : null;
		$startdate = isset($payload["startdate"]) ? $payload["startdate"] : null;
		$enddate = isset($payload["enddate"]) ? $payload["enddate"] : null;
		
		if (empty($startdate) || empty($enddate) || empty($pmcampaignnum)) {
			Super::sendJsonResponse("error", "Campaign Number, Start Date, and End Date are required.");
			return;
		}
		
		$enterdate = Xbo::setEnterDate();
		$importedBy = Xbo::getLoginUser();
		
		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();
    
		try{
			$workorderSet = (new Set())->useTransactionManager()
									   ->MboSetRemote("workorder")
									   ->where("status NOT IN ('COMPLETED', 'CLOSE', 'CANCEL') AND targstartdate BETWEEN ? AND ?", $startdate, $enddate)
									   ->getMboSet();

			$imported_count = 0;
			while($wo = $workorderSet->fetch()){
				$wonum = $wo["wonum"];

				$existingLineSet = (new Set())->useTransactionManager()
											  ->MboSetRemote("pmcampaignline")
											  ->where("pmcampaignnum = ? AND wonum = ?", $pmcampaignnum, $wonum)
											  ->getMboSet();
				if (!$existingLineSet->isEmpty()) {
					continue;
				}

				$pmnum = $wo["pmnum"];
				$teamnum = $wo["teamnum"];
				$status = $wo["status"];

				$targstartdate_str = $wo["targstartdate"];
				$targcompdate_str = $wo["targcompdate"];
				$targstartdate_obj = new DateTime($targstartdate_str);
				$owner = $wo["owner"];
				$leader = $wo["leader"];

				$duration_hours = isset($wo['duration']) ? (float)$wo['duration'] : 0;
				if ($duration_hours <= 0) {
					$duration_hours = 3.0;
				}

				if (empty($targcompdate_str) || new DateTime($targcompdate_str) <= $targstartdate_obj) {
					$targcompdate_obj = clone $targstartdate_obj;
					$targcompdate_obj->modify("+2 days");
					
				} else {
					$targcompdate_obj = new DateTime($targcompdate_str);
				}
				
				$islocked = 'Y'; 
				$hard_locked_statuses = ['SCHED', 'INPROGRESS', 'COMPLETED', 'CLOSE']; 
				if (in_array(strtoupper($status), $hard_locked_statuses)) {
					$islocked = 'Y';
				} else {
					if ($owner === $importedBy || $leader === $importedBy) {
						$islocked = 'N'; 
					}
				}
			   
				$linenumber = Xbo::getLinenum("pmcampaignline", "pmcampaignlinenum", ["pmcampaignnum" => $pmcampaignnum]);
				
				(new Set())->useTransactionManager()
						   ->MboSetRemote("pmcampaignline")
						   ->setValue("pmcampaignnum", $pmcampaignnum)
						   ->setValue("wonum", $wonum)
						   ->setValue("pmnum", $pmnum)
						   ->setValue("teamnum", $teamnum)
						   ->setValue("duration", $duration_hours)
						   ->setValue("targstartdate", $targstartdate_obj->format('Y-m-d H:i:s'))
						   ->setValue("targcompdate", $targcompdate_obj->format('Y-m-d H:i:s'))
						   ->setValue("revtargstartdate", $targstartdate_obj->format('Y-m-d H:i:s'))
						   ->setValue("revtargenddate", $targcompdate_obj->format('Y-m-d H:i:s'))
						   ->setValue("islocked", $islocked)
						   ->setValue("pmcampaignlinenum", $linenumber)
						   ->setValue("enterby", $importedBy)
						   ->setValue("enterdate", $enterdate)
						   ->setValue("source", "workorder")
						   ->add();
				$imported_count++;
			}

			$tm->commit();
			
			$campaign_tasks = self::getCampaignTasksForGantt($pmcampaignnum);
			Super::sendJsonResponse("success", $imported_count . " work orders have been imported to the campaign.", $campaign_tasks);
			
		}catch(Exception $e){
			$tm->rollback();
			throw $e;
		}


}
	
	public static function addForecastings() {
		
		$payload = Super::getParam();
		$forecastings = isset($payload["addForecastings"]) ? $payload["addForecastings"] : null;
		$pmcampaignnum = isset($payload["pmcampaignnum"]) ? $payload["pmcampaignnum"] : null;

		if (empty($forecastings) || empty($pmcampaignnum)) {
			Super::sendJsonResponse("error", "Forecast data or campaign number is missing.");
			return;
		}

		$enterby = Xbo::getLoginUser();
		$enterdate = Xbo::setEnterDate();
		
		
		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();

		try {
			foreach ($forecastings as $data) {
				$pmwolineid = $data["value"];
			
				$pmwolineSet = (new Set())->useTransactionManager()
										  ->MboSetRemote("pmwoline")
										  ->where("pmwolineid=?", $pmwolineid)
										  ->getMboSet();
				
				if ($pmwolineSet->isEmpty()) {
					error_log("addForecastings: Could not find pmwoline with id {$pmwolineid}. Skipping.");
					continue; 
				}
				
				$pm_forecast = $pmwolineSet->getMbo();

				$wonum   = $pm_forecast->getString("wonum");
				$pmnum   = $pm_forecast->getString("pmnum");
				$teamnum = $pm_forecast->getString("teamnum");

				$targstartdate_str = $pm_forecast->getString("targstartdate");
				$targcompdate_str  = $pm_forecast->getString("targenddate");

				$targstartdate_obj = new DateTime($targstartdate_str);
				$targcompdate_obj = !empty($targcompdate_str) ? new DateTime($targcompdate_str) : clone $targstartdate_obj;

		
				$duration_hours = (float)$pm_forecast->getString("duration");
				if ($duration_hours <= 0) {
					$duration_hours = 3.0; 

				}
				if ($targcompdate_obj <= $targstartdate_obj) {
					$targcompdate_obj = clone $targstartdate_obj;
					$targcompdate_obj->modify("+{$duration_hours} hours");

					$interval = $targstartdate_obj->diff($targcompdate_obj);
					if ($interval->days < 2) {
						$targcompdate_obj = clone $targstartdate_obj;
						$targcompdate_obj->modify("+2 days");
					}
				}

				$targstartdate_safe = $targstartdate_obj->format('Y-m-d H:i:s');
				$targenddate_safe   = $targcompdate_obj->format('Y-m-d H:i:s');
				
	
				$existingLineSet = (new Set())->useTransactionManager()
											  ->MboSetRemote("pmcampaignline")
											  ->where("pmcampaignnum = ? AND wonum = ?", $pmcampaignnum, $wonum)
											  ->getMboSet();
				
				if (!$existingLineSet->isEmpty()) {
					continue;
				}
				$islocked = ($pm_forecast->getString("isschedulable") === 'N') ? 'Y' : 'N';
				$linenumber = Xbo::getLinenum("pmcampaignline", "pmcampaignlinenum", ["pmcampaignnum" => $pmcampaignnum]);
				(new Set())->useTransactionManager()
						   ->MboSetRemote("pmcampaignline")
                           ->setValue("pmcampaignnum", $pmcampaignnum)
                           ->setValue("wonum", $wonum)
                           ->setValue("pmnum", $pmnum) 
                           ->setValue("teamnum", $teamnum) 
                           ->setValue("duration", $duration_hours) 
                           ->setValue("targstartdate", $targstartdate_safe)
                           ->setValue("targcompdate", $targenddate_safe) 
                           ->setValue("revtargstartdate", $targstartdate_safe)
                           ->setValue("revtargenddate", $targenddate_safe)
                           ->setValue("islocked", $islocked)
                           ->setValue("pmcampaignlinenum", $linenumber)
                           ->setValue("enterby", $enterby)
                           ->setValue("enterdate", $enterdate)
                           ->setValue("changeby", $enterby)
                           ->setValue("changedate", $enterdate)
						   ->setValue("source", "pmwoline")
                           ->add();
			}
			$tm->commit();
			$campaign_tasks = self::getCampaignTasksForGantt($pmcampaignnum);

			Super::sendJsonResponse("success", count($forecastings) . " forecasted work orders have been added to the campaign.", $campaign_tasks);

		} catch (Exception $e) {
			$tm->rollback();
			throw $e;
		}
	}

	
	
	public static function addWorkorders() {
        $payload = Super::getParam();
        $workorders = isset($payload["addWorkorders"]) ? $payload["addWorkorders"] : null;
        $pmcampaignnum = isset($payload["pmcampaignnum"]) ? $payload["pmcampaignnum"] : null;

        if (empty($workorders) || empty($pmcampaignnum)) {
            Super::sendJsonResponse("error", "Work order data or campaign number is missing.");
            return;
        }

        $enterby = Xbo::getLoginUser();
        $enterdate = Xbo::setEnterDate();

        $tm = TransactionManager::getInstance();
        $tm->beginTransaction();

        try {
            foreach ($workorders as $data) {
                $workorderid = $data["value"];

                $workorderSet = (new Set())->useTransactionManager()
                                           ->MboSetRemote("workorder")
                                           ->where("workorderid=?", $workorderid)
                                           ->getMboSet();
                
                if ($workorderSet->isEmpty()) {
                    error_log("addWorkorders: Could not find workorder with id {$workorderid}. Skipping.");
                    continue; 
                }
                
				$wo = $workorderSet->getMbo();

				$wonum = $wo->getString("wonum");
				$pmnum = $wo->getString("pmnum");
				$teamnum = $wo->getString("teamnum");
				$status = $wo->getString("status");

				$targstartdate_str = $wo->getString("targstartdate");
				$targstartdate_obj = new DateTime($targstartdate_str);

				// Ambil duration
				$duration_hours = (float)$wo->getString("duration");
				if ($duration_hours <= 0) {
					$duration_hours = 1.0;
				}

				// Ambil end date
				$targenddate_str = $wo->getString("targcompdate");
				$targenddate_obj = !empty($targenddate_str) ? new DateTime($targenddate_str) : clone $targstartdate_obj;

				if ($targenddate_obj <= $targstartdate_obj) {
					$targenddate_obj = clone $targstartdate_obj;
					$targenddate_obj->modify("+{$duration_hours} hours");

					// kalau hasilnya masih terlalu pendek (< 2 hari) , fallback 2 hari
					$interval = $targstartdate_obj->diff($targenddate_obj);
					if ($interval->days < 2) {
						$targenddate_obj = clone $targstartdate_obj;
						$targenddate_obj->modify("+2 days");
					}
				}

				$targstartdate_safe = $targstartdate_obj->format('Y-m-d H:i:s');
				$targenddate_safe   = $targenddate_obj->format('Y-m-d H:i:s');
				
                $existingLineSet = (new Set())->useTransactionManager()
                                              ->MboSetRemote("pmcampaignline")
                                              ->where("pmcampaignnum = ? AND wonum = ?", $pmcampaignnum, $wonum)
                                              ->getMboSet();
                
                if (!$existingLineSet->isEmpty()) {
                    continue; 
                }

                $locked_statuses = ['SCHED', 'INPROGRESS', 'COMPLETED', 'CLOSE'];
                $islocked = in_array(strtoupper($status), $locked_statuses) ? 'Y' : 'N';
                $linenumber = Xbo::getLinenum("pmcampaignline", "pmcampaignlinenum", ["pmcampaignnum" => $pmcampaignnum]);
                (new Set())->useTransactionManager()
                           ->MboSetRemote("pmcampaignline")
                           ->setValue("pmcampaignnum", $pmcampaignnum)
                           ->setValue("wonum", $wonum)
                           ->setValue("pmnum", $pmnum)
                           ->setValue("teamnum", $teamnum)
                           ->setValue("duration", $duration_hours)
                           ->setValue("targstartdate", $targstartdate_safe)
                           ->setValue("targcompdate", $targenddate_safe)
                           ->setValue("revtargstartdate", $targstartdate_safe)
                           ->setValue("revtargenddate", $targenddate_safe)
                           ->setValue("islocked", $islocked)
                           ->setValue("pmcampaignlinenum", $linenumber)
                           ->setValue("enterby", $enterby)
                           ->setValue("enterdate", $enterdate)
                           ->setValue("changeby", $enterby)
                           ->setValue("changedate", $enterdate)
						   ->setValue("source", "workorder")
                           ->add();
            }
            
            $tm->commit();
			$campaign_tasks = self::getCampaignTasksForGantt($pmcampaignnum);
            Super::sendJsonResponse("success", count($workorders) . " active work orders have been added to the campaign.", $campaign_tasks);

        } catch (Exception $e) {
            $tm->rollback();
            throw $e;
        }
    }
	
	public static function initGanttChart(){
		$pmcampaignnum = Super::getParam("pmcampaignnum");
		$campaign_tasks = self::getCampaignTasksForGantt($pmcampaignnum);
		Super::sendJsonResponse("success", "Gantt Chart Loaded", $campaign_tasks);
	}
	
	private static function getCampaignTasksForGantt($pmcampaignnum) {
		$campaignLineSet = (new Set())->useTransactionManager()
									  ->MboSetRemote("pmcampaignline")
									  ->where("pmcampaignline.pmcampaignnum = ?", $pmcampaignnum)
									  ->order("pmcampaignline.revtargstartdate ASC")
									  ->getMboSet();
		
		if ($campaignLineSet->isEmpty()) {
			return [];
		}
		$importedBy = Xbo::getLoginUser(); 
		$tasks_for_gantt = [];
		while ($line = $campaignLineSet->fetch()) {
			$wonum = $line['wonum'];
			$source = $line['source'];
			$duration = floatval($line['duration']);
			$progress = (new Set())->MboSetRemote("workorder")->where("wonum=?", $wonum)->getMboValue("progress");
			$start_date = new DateTime($line['revtargstartdate']);
			$end_date = new DateTime($line['revtargenddate']);
			
			$status = "FORECASTING";
			$pmwoline = (new Set())->useTransactionManager()->MboSetRemote("pmwoline")->where("wonum = ? ", $wonum)->getMboSet()->getMbo();
			$description = $pmwoline->getString("description");
			$teamnum = $pmwoline->getString("teamnum");
			
			$leader = $pmwoline->getString("leader");
			$islocked = ($pmwoline->getString("isschedulable") === 'N') ? 'Y' : 'N';
			$pmnum = $pmwoline->getString("pmnum");
			if($duration <= 1) {
				$duration = $pmwoline->getString("duration");
			}

			$costData = Xbo::calculateForecastWOCost($wonum);
			
			$plannedcost = 0; 
			if ($costData) { 
				$plannedcost = $costData->plannedcost;
			}
			
			$leader = $importedBy;
			$owner = $importedBy;
			
			$pm = (new Set())->MboSetRemote("pm")->where("pmnum=?", $pmnum)->getMboSet()->getMbo();
			if(empty($teamnum)){
				$teamnum = $pm->getString("teamnum");
			}
			$orgcode = $pm->getString("orgcode");
			
			$wo = (new Set())->MboSetRemote("workorder")->where("wonum=?", $wonum)->getMboSet()->getMbo();
			$workorderid = $wo->getString("workorderid");
			$priority =  $wo->getString("priority");
			$wostatus = $wo->getString("status");
			$leader = empty($wo->getString("leader")) ? $leader : $wo->getString("leader");
			$owner  = empty($wo->getString("owner"))  ? $owner  : $wo->getString("owner");
			$status = empty($wo->getString("status"))  ? $status  : $wo->getString("status");
			
			
			if($duration <= 1) {
				$duration = $wo->getString("duration");
			}
			
			$hard_locked_statuses = ['SCHED', 'INPROGRESS', 'COMPLETED', 'CLOSE', 'CANCEL']; 
			if (in_array(strtoupper($wostatus), $hard_locked_statuses)) {
				$islocked = 'Y';
			} else {
				if ($owner === $importedBy || $leader === $importedBy) {
					$islocked = 'N'; 
				}
			}
			
			
			if(empty($priority)) $priority = "NORMAL";
			if(empty($teamnum)) {
				$teamnum = $wo->getString("teamnum");
			}
			if(empty($orgcode)){
				$orgcode = $wo->getString("orgcode");
			}
			
			if(empty($description)){
				$description = $wo->getString("description");
			}
			
			$costData = Xbo::calculateWOCost($wonum);
			$plannedcost = $costData->plannedcost;
			
			
			if($duration <= 1) {
				$duration = 8;
			}
			$currency = (new Set())->useTransactionManager()->MboSetRemote("organization")->where("orgcode=?", $orgcode)->getMboSet()->getMbo()->getString("basecurrency");
			$tasks_for_gantt[] = [
				'id'       => $wonum, // ID unik untuk Gantt
				'name'     => $description,
				'start'    => $start_date->format('Y-m-d'),
				'end'      => $end_date->format('Y-m-d'),
				'hours'    => $duration,
				'duration' => $duration,
				'cost'     => (float)$plannedcost, // Ambil dari workorder
				'currency' => $currency,
				'teamnum'  => $teamnum,
				'isLocked' => "N",
				'workorderid' => $workorderid,
				'priority' => strtoupper($priority),
				'progress' => $progress,
				'source' => $source,
				'status' => $status
			];
		}
		
		return $tasks_for_gantt;
	}


    
} 

Class ValidateTables {
	public static function tbcx(){
		$payload = Super::getParam("tbcx");
		$dataPackage = $payload["dataPackage"];

		$childTable = $dataPackage["datasetChild"];
		$deleteBy = $dataPackage["ownership"]["userid"];
		$parent = $dataPackage["ownership"]["xboset"];
		$parentid = $dataPackage["ownership"]["xbosetid"];
		$selectedRows =  $dataPackage["selectedRows"];

		//Check SalesQuotationLine & Check PRLINE

		if($childTable == "pmcampaignline"){
			foreach($selectedRows as $row){
				$rowid = $row["pmcampaignlineid"];
				$pmcampaignline = (new Set())->useTransactionManager()->MboSetRemote("pmcampaignline")->where("pmcampaignlineid=?", $rowid)->getMboSet()->getMbo();
			
				$pmcampaignnum = $pmcampaignline->getString("pmcampaignnum");
				$wonum = $pmcampaignline->getString("wonum");

				//Check Sales Quotation Line
				// Check Sales Quotation Header dulu
				$sqSet = (new Set())->useTransactionManager()->MboSetRemote("salesquotation")->where("pmcampaignnum=?", $pmcampaignnum)->getMboSet();
				
				if($sqSet->isEmpty()){
					//Check PR
					$prSet = (new Set())->useTransactionManager()->MboSetRemote("pr")->where("pmcampaignnum=?", $pmcampaignnum)->getMboSet();
					if($prSet->isEmpty()){
						$_POST["delChildPackage"] = $dataPackage;
						Super::deleteChildren();		
					}else{
						//Check PRLINE 
						$pr = $prSet->getMbo();
						$prnum = $$pr->getString("prnum");
						$prlineSet =  (new Set())->useTransactionManager()->MboSetRemote("prline")->where("prnum=? and wonum=?", $prnum, $wonum)->getMboSet();
						Super::sendJsonResponse("error", "Cannot Delete Work Order because it is linked with PR : $prnum, on Line; $prlinenum");
					}
				}else{
					$sq = $sqSet->getMbo();
					$salesquotenum = $sq->getString("salesquotenum");
					$sqlSet = (new Set())->useTransactionManager()->MboSetRemote("salesquotationline")->where("wonum=? and salesquotenum=?", $wonum, $salesquotenum)->getMboSet();
					if($sqlSet->isEmpty()){
						$_POST["delChildPackage"] = $dataPackage;
						Super::deleteChildren();
					}else{
						$sql = $sqlSet->getMbo();
						$salesquotationlinenum = $sql->getString("salesquotationlinenum");
						Super::sendJsonResponse("error", "Cannot Delete Work Order because it is linked with Sales Quotation : $salesquotenum, on Line; $salesquotationlinenum");
					}
				}

			}
		}
		Super::sendJsonResponse("error", "You're not allowed to delete Work Campaign Lines");
	}

}
	

?>