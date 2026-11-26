<?php
require_once ("../../../eat.php");

Class ActionSet {


	public static function validateStatusChange(){
		$payload = Super::getParam("validateStatusChange");
		$new_status = strtolower($payload["new_status"]);
		$sonum = $payload["sonum"];

		try{

			$soSet = (new Set())->useTransactionManager()->MboSetRemote("so")->where("sonum=?", $sonum)->getMboSet();
			$so = $soSet->getMbo();

			$bookingdate = $so->getString("bookingdate");
			$orderdate = $so->getString("orderdate");
			$contactname = $so->getString("contactname");
			$customercode = $so->getString("customercode");
			$description = $so->getString("description");
			$employeesid  = $so->getString("employeesid");
			$missingFields = [];
			if(empty($bookingdate)) {
				$missingFields [] = "Booking Date cannot be null.";
			}
			if(empty($orderdate)) {
				$missingFields [] = "Order Date cannot be null.";
			}
			if(empty($contactname)) {
				$missingFields [] = "Customer Contact cannot be null.";
			}
			if(empty($customercode)) {
				$missingFields [] = "Customer Code cannot be null.";
			}
			if(empty($description)) {
				$missingFields [] = "Description cannot be null.";
			}
			if(empty($employeesid)) {
				$missingFields [] = "Sales Representative cannot be null.";
			}

			if (!empty($missingFields)) {
				$message = "Please fill in the following mandatory fields: <br>" . implode("<br>", $missingFields) . ".";
				Super::sendJsonResponse("error", $message, array_unique($missingFields));
				return;
			}

			// Check apakah ada Sales Order Lines.
			$slSet = (new Set())->useTransactionManager()
				->MboSetRemote("soline")
				->where("sonum = ?", $sonum)
				->MboRemote("solinenum, linetype, itemnum, serviceitemnum, description, warehouse, sellqty, unitprice, lineprice, gldebitacct, glcreditacct")
				->getMboSet();

			if ($slSet->isEmpty()) {
				// Blokir perubahan status ke level lanjut jika tidak ada detail
				if (in_array($new_status, ["sent", "booked", "sold", "approved"])) {
					Super::sendJsonResponse("error", "Sales Order Lines Not Found. Cannot proceed to status '$new_status'. Please add items or services first.");
				}
			} else {
				$errors = [];
				while ($sl = $slSet->fetch()) {
					$lineNum = $sl["solinenum"];
					$itemIdentifier = $sl["itemnum"] ?: ($sl["serviceitemnum"] ?: "Unknown Item");
					$lineContext = "Line #$lineNum ($itemIdentifier)"; // Konteks untuk pesan error

					// 1. Validasi Deskripsi (Wajib)
					if (empty(trim($sl["description"]))) {
						$errors[] = "$lineContext: Description is required.";
					}

					// 2. Validasi Kuantitas (Wajib & > 0)
					// Gunakan (float) untuk memastikan perbandingan angka yang benar
					if (empty($sl["sellqty"]) || (float)$sl["sellqty"] <= 0) {
						$errors[] = "$lineContext: Quantity must be greater than 0.";
					}

					// 3. Validasi Harga (Wajib & >= 0)
					// Harga boleh 0 jika itu bonus, tapi tidak boleh null/kosong.
					// Jika kebijakan bisnis Anda melarang harga 0, ubah menjadi <= 0.
					if (!is_numeric($sl["unitprice"]) || (float)$sl["unitprice"] < 0) {
						$errors[] = "$lineContext: Unit Price cannot be negative.";
					}
					
					// 4. Validasi GL Account (Wajib)
					if (empty($sl["gldebitacct"])) {
						$errors[] = "$lineContext: GL Debit Account is missing.";
					}
					if (empty($sl["glcreditacct"])) {
						$errors[] = "$lineContext: GL Credit Account is missing.";
					}

					// 5. Validasi Khusus Tipe ITEM (Wajib Warehouse)
					if (strtolower($sl["linetype"]) === "item") {
						if (empty($sl["itemnum"])) {
							$errors[] = "$lineContext: Item Number is required for item line type.";
						}
						if (empty($sl["warehouse"])) {
							$errors[] = "$lineContext: Warehouse is required for item type.";
						}
					} 
					// 6. Validasi Khusus Tipe SERVICE (Wajib Service Item)
					elseif (strtolower($sl["linetype"]) === "services") {
						if (empty($sl["serviceitemnum"]) && empty($sl["description"])) {
							$errors[] = "$lineContext: Service Item or Description is required.";
						}
					}
				}

				// Jika ada error yang terkumpul, hentikan proses dan laporkan SEMUANYA
				if (!empty($errors)) {
					// Format pesan error menjadi list HTML agar mudah dibaca di frontend
					$errorMessage = "Validation Failed:<br><ul><li>" . implode("</li><li>", $errors) . "</li></ul>";
					Super::sendJsonResponse("error", $errorMessage);
				}
			}

			if($new_status == "cancel" || $new_status == "new"){
				//check matsaletrans & servsaletrans
				$mSet = (new Set())->useTransactionManager()->MboSetRemote("matsaletrans")->where("sonum=?", $sonum)->getMboSet();
				
				if(!$mSet->isEmpty()){
					Super::sendJsonResponse("error", "Cannot change status to $new_status because it is linked to Material Sales Transactions");
				}
				$sSet = (new Set())->useTransactionManager()->MboSetRemote("servsaletrans")->where("sonum=?", $sonum)->getMboSet();
				if(!$sSet->isEmpty()){
					Super::sendJsonResponse("error", "Cannot change status to $new_status because it is linked to Service Sales Transactions");
				}
			}


			//Jika semua validasi sukses...
			Super::sendJsonResponse("success", "Validation passed. Ready to change status.");

		}catch (Exception $e) {
			Super::sendJsonResponse("error", $e->getMessage());
		}

	}
	private static function executeServiceReturn(array $context): array 
	{
		// 1. Ekstrak dan Konversi Variabel Utama dari Konteks
		$returnQty = (float)$context['returnqty'];
		$returnPricePerUnit = (float)$context['return_price_per_unit'];
		$returnCostPerUnit = (float)$context['return_cost_per_unit'];
		$originalUnitPrice = (float)$context['unitprice']; 
		$commissionPercentage = (float)($context['commission'] ?? 0);
		$taxCode = $context['taxcode'] ?? null;
		$linePriceReturn = $returnPricePerUnit * $returnQty;
		$lineCostReturn = $returnCostPerUnit * $returnQty;

		// Profitabilitas
		$unitProfitReturn = $returnPricePerUnit - $returnCostPerUnit;
		$lineProfitReturn = $unitProfitReturn * $returnQty;

		// Komisi (dihitung dari harga asli sebelum diskon)
		$unitCommissionReturn = $originalUnitPrice * ($commissionPercentage / 100);
		$lineCommissionReturn = $unitCommissionReturn * $returnQty;

		// Pajak (berdasarkan harga setelah diskon)
		$taxRate = $taxCode ? XboSet::getTaxRate($taxCode) : 0;
		$taxPriceReturn = $linePriceReturn * ($taxRate / 100);

		(new Set())->useTransactionManager()
			->MboSetRemote("servsaletrans")
			->setValue("sonum", $context['sonum'])
			->setValue("solinenum", $context['solinenum'])
			->setValue("transtype", "RETURN") 
			->setValue("customercode", $context['customercode'])
			->setValue("serviceitemnum", $context['serviceitemnum'])
			->setValue("wonum", $context['wonum'])
			->setValue("description", $context['description'])
			->setValue("quantity", $returnQty) // positif
			->setValue("unitcost", $returnCostPerUnit)
			->setValue("linecost", $lineCostReturn)
			->setValue("unitprice", $originalUnitPrice)
			->setValue("unitpricediscount", $returnPricePerUnit)
			->setValue("lineprice", $originalUnitPrice * $returnQty) 
			->setValue("linepricediscount", $linePriceReturn)
			->setValue("unitprofitdiscount", $unitProfitReturn)
			->setValue("lineprofitdiscount", $lineProfitReturn)
			->setValue("unitcommission", $unitCommissionReturn)
			->setValue("linecommission", $lineCommissionReturn)
			->setValue("taxcode", $taxCode)
			->setValue("taxprice", $taxPriceReturn)
			->setValue("gldebitacct", $context['gldebitacct']) 
			->setValue("glcreditacct", $context['glcreditacct']) 
			->setValue("financialperiod", Xbo::getFinancialPeriod())
			->setValue("actualdate", $context['actualdate'])
			->setValue("enterby", $context['enterby'])
			->setValue("enterdate", $context['enterdate'])
			->setValue("changeby", $context['enterby'])
			->setValue("changedate", $context['enterdate'])
			->setValue("salesman", $context['employeesid'])
			->setValue("orgcode", $context['orgcode'])
			->setValue("sitecode", $context['sitecode'])
			->add();

		// 4. Kembalikan Hasil Sukses
		return ['status' => 'success', 'message' => 'Service return recorded successfully.'];
	}

	public static function returnServiceSales() {

	
		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();

		try {
			$payload = Super::getParam("returnServiceSales");

			
			if (empty($payload['sonum'])) {
				throw new Exception("Sales Order number (sonum) is required.");
			}
			if (empty($payload['data']) || !is_array($payload['data'])) {
				throw new Exception("Invalid or empty service data for return.");
			}
			
			$sonum = $payload['sonum'];
			$itemsToReturn = $payload['data'];
			
			$processedCount = 0;
			$errorMessages = [];

			// 3. Ambil data SO Header (sekali saja untuk efisiensi)
			$soHeaderMbo = (new Set())->useTransactionManager()
									->MboSetRemote("so")
									->where("sonum = ?", $sonum)
									->getMboSet()->getMbo();
			if (!$soHeaderMbo) {
				throw new Exception("Sales Order '{$sonum}' not found.");
			}

			// 4. Loop untuk setiap JASA yang akan diretur
			foreach ($itemsToReturn as $item) {
				
				$solinenum = $item['solinenum'] ?? null;
				$returnqty = (float)($item['returnqty'] ?? 0);

				if (empty($solinenum) || $returnqty <= 0) {
					$errorMessages[] = "Invalid data (missing solinenum or invalid return quantity).";
					continue;
				}

				// --- Langkah A: Dapatkan Konteks dari Sales Order Line ---
				$soLineMbo = (new Set())->useTransactionManager()
										->MboSetRemote("soline")
										->where("sonum = ? AND solinenum = ?", $sonum, $solinenum)
										->getMboSet()->getMbo();

				if (!$soLineMbo || $soLineMbo->getString('linetype') !== 'services') {
					$errorMessages[] = "Sales Order Line {$solinenum} is not found or is not a SERVICE type.";
					continue;
				}

		
				// Hitung total yang sudah dikirim untuk baris ini
				$deliveredQty = (new Set())->useTransactionManager()
										->MboSetRemote("servsaletrans")
										->where("sonum = ? AND solinenum = ? AND transtype = 'SALE'", $sonum, $solinenum)
										->MboRemote("SUM(quantity) as total")
										->getMboSet()->getMbo()->getDecimal('total') ?? 0;

				// Hitung total yang sudah pernah diretur untuk baris ini
				$previouslyReturnedQty = (new Set())->useTransactionManager()
													->MboSetRemote("servsaletrans")
													->where("sonum = ? AND solinenum = ? AND transtype = 'RETURN'", $sonum, $solinenum)
													->MboRemote("SUM(quantity) as total")
													->getMboSet()->getMbo()->getDecimal('total') ?? 0;
				
				$maxAllowableReturn = ($deliveredQty*-1) - $previouslyReturnedQty;

				if ($returnqty > $maxAllowableReturn) {
					$errorMessages[] = "Return quantity ({$returnqty}) for service line {$solinenum} exceeds the maximum allowable ({$maxAllowableReturn}). deliveredQty: $deliveredQty,  previouslyReturnedQty:$previouslyReturnedQty";
					continue;
				}
				
				$context = [
					'sonum' => $sonum,
					'solinenum' => $solinenum,
					'linetype' => 'services',
					'description' => $soLineMbo->getString('description'),
					'employeesid' => $soHeaderMbo->getString('employeesid'),
					'returnqty' => $returnqty,
					'serviceitemnum' => $soLineMbo->getString('serviceitemnum'),
					'unitprice' => $soLineMbo->getDecimal('unitprice'),
					'return_price_per_unit' => $soLineMbo->getDecimal('unitpricediscount'), 
					'return_cost_per_unit' => $soLineMbo->getDecimal('unitcost'), 
					'customercode' => $soHeaderMbo->getString('customercode'),
					'actualdate' => $payload['returndate'] ?? Xbo::setEnterDate(),
					'wonum' => $soLineMbo->getString('wonum'),
					'enterby' => Xbo::getLoginUser(),
					'enterdate' => Xbo::setEnterDate(),
					'glcreditacct' => $soLineMbo->getString('gldebitacct'),  // Kredit: Akun HPP Jasa (COGS)
					'gldebitacct' => $soLineMbo->getString('glcreditacct'), // Debit: Akun Pendapatan Jasa
					'orgcode' => $soHeaderMbo->getString("orgcode"),
					'commission' => $soLineMbo->getDecimal('commission'),
					'sitecode' => $soHeaderMbo->getString("sitecode")
				];

				$returnResult = self::executeServiceReturn($context);
				
				if ($returnResult && $returnResult['status'] === 'success') {
					$processedCount++;
				} else {
					$errorMessages[] = "For service line {$solinenum}: " . ($returnResult['message'] ?? 'Unknown error.');
				}
			} // Akhir dari foreach loop

			// 5. Finalisasi Transaksi
			if (!empty($errorMessages)) {
				throw new Exception(implode(' | ', $errorMessages));
			}
			
			$tm->commit();
			Super::sendJsonResponse("success", "{$processedCount} service(s) have been successfully returned.");

		} catch (Exception $e) {
			$tm->rollback();
			$finalMessage = "Process failed. Details: " . $e->getMessage();
			Super::sendJsonResponse("error", $finalMessage);
		}
	}

	private static function executeServiceDelivery(array $context): array 
	{
		
		$quantity = (float)$context['deliveredqty'];
		$unitPrice = (float)$context['unitprice'];
		$unitCost = (float)$context['unitcost'];
		$unitPriceDiscount = (float)$context['unitpricediscount'];
		$commissionPercentage = (float)($context['commission'] ?? 0);
		$taxCode = $context['taxcode'];

	
		// Total
		$linePrice = $unitPrice * $quantity;
		$lineCost = $unitCost * $quantity;
		$linePriceDiscount = $unitPriceDiscount * $quantity;

		// Profitabilitas
		$unitProfit = $unitPrice - $unitCost;
		$lineProfit = $unitProfit * $quantity;
		$unitProfitDiscount = $unitPriceDiscount - $unitCost;
		$lineProfitDiscount = $unitProfitDiscount * $quantity;

		// Komisi
		$unitCommission = $unitPrice * ($commissionPercentage / 100);
		$lineCommission = $unitCommission * $quantity;

		// Pajak (berdasarkan harga setelah diskon)
		$taxRate = XboSet::getTaxRate($taxCode); 
		$taxPrice = $linePriceDiscount * ($taxRate / 100);

		// 3. Lakukan INSERT ke servsaletrans dengan data yang sudah lengkap
		(new Set())->useTransactionManager()
			->MboSetRemote("servsaletrans")
			->setValue("sonum", $context['sonum'])
			->setValue("solinenum", $context['solinenum'])
			->setValue("transtype", "SALE")
			->setValue("linetype", "services")
			->setValue("financialperiod", Xbo::getFinancialPeriod())
			->setValue("customercode", $context['customercode'])
			->setValue("serviceitemnum", $context['serviceitemnum'])
			->setValue("wonum", $context['wonum'])
			->setValue("description", $context['description'])
			->setValue("quantity", $quantity)
			->setValue("unitcost", $unitCost)
			->setValue("linecost", $lineCost)
			->setValue("unitprice", $unitPrice)
			->setValue("lineprice", $linePrice)
			->setValue("unitpricediscount", $unitPriceDiscount)
			->setValue("linepricediscount", $linePriceDiscount)
			->setValue("unitprofit", $unitProfit)
			->setValue("lineprofit", $lineProfit*-1)
			->setValue("unitprofitdiscount", $unitProfitDiscount)
			->setValue("lineprofitdiscount", $lineProfitDiscount*-1)
			->setValue("unitcommission", $unitCommission)
			->setValue("linecommission", $lineCommission)
			->setValue("taxcode", $taxCode)
			->setValue("taxprice", $taxPrice)
			->setValue("transdate", Xbo::setEnterDate())
			->setValue("gldebitacct", $context['gldebitacct'])
			->setValue("glcreditacct", $context['glcreditacct'])
			->setValue("actualdate", $context['actualdate'])
			->setValue("enterby", $context['enterby'])
			->setValue("enterdate", $context['enterdate'])
			->setValue("changeby", $context['enterby'])
			->setValue("changedate", $context['enterdate']) 
			->setValue("salesman", $context['employeesid'])
			->setValue("orgcode", $context['orgcode'])
			->setValue("sitecode", $context['sitecode'])
			->add();

		
		return ['status' => 'success', 'message' => 'Service delivery recorded successfully.'];
	}
	public static function deliverServiceSales() {
		
		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();

		try {
			$payload = Super::getParam("deliverServiceSales");

			if (empty($payload['sonum'])) {
				throw new Exception("Sales Order number (sonum) is required.");
			}
			if (empty($payload['data']) || !is_array($payload['data'])) {
				throw new Exception("Invalid or empty service data provided.");
			}
			
			$sonum = $payload['sonum'];
			$itemsToDeliver = $payload['data'];
			
			$processedCount = 0;
			$errorMessages = [];

			// Ambil data SO Header
			$soHeaderMbo = (new Set())->useTransactionManager()
									->MboSetRemote("so") // Asumsi nama tabel header
									->where("sonum = ?", $sonum)
									->getMboSet()->getMbo();
			if (!$soHeaderMbo) {
				throw new Exception("Sales Order '{$sonum}' not found.");
			}

			// Loop untuk setiap JASA yang akan dikirim
			foreach ($itemsToDeliver as $item) {
				
				$solinenum = $item['solinenum'] ?? null;
				$deliveredqty = (float)($item['deliveredqty'] ?? 0);

				if (empty($solinenum) || $deliveredqty <= 0) {
					$errorMessages[] = "Invalid data (missing solinenum or invalid quantity).";
					continue;
				}
				// Dapatkan Konteks dari Sales Order Line ---
				$soLineMbo = (new Set())->useTransactionManager()
										->MboSetRemote("soline") // Asumsi nama tabel line
										->where("sonum = ? AND solinenum = ?", $sonum, $solinenum)
										->getMboSet()->getMbo();

				if (!$soLineMbo) {
					$errorMessages[] = "Sales Order Line {$solinenum} not found.";
					continue;
				}

				
				if ($soLineMbo->getString('linetype') !== 'services') {
					$errorMessages[] = "Sales Order Line {$solinenum} is not a SERVICE type item.";
					continue;
				}
			
				$context = [
					'sonum' => $sonum,
					'solinenum' => $solinenum,
					'customercode' => $soHeaderMbo->getString("customercode"),
					'deliveredqty' => $deliveredqty * -1,
					'actualdate' => $payload['deliverydate'] ?? Xbo::setEnterDate(),
					'employeesid' => $soHeaderMbo->getString('employeesid'),
					'orgcode' => $soHeaderMbo->getString("orgcode"),
					'sitecode' => $soHeaderMbo->getString("sitecode"),
					'linetype' => 'services',
					'description' => $soLineMbo->getString('description'),
					'wonum' => $soLineMbo->getString('wonum'),
					'unitprice' => $soLineMbo->getDecimal('unitprice'),
					'unitpricediscount' => $soLineMbo->getDecimal('unitpricediscount'),
					'unitcost' => $soLineMbo->getDecimal('unitcost'),
					'taxcode' => $soLineMbo->getString('taxcode'),
					'gldebitacct' => $soLineMbo->getString('gldebitacct'),
					'glcreditacct' => $soLineMbo->getString('glcreditacct'),
					'serviceitemnum' => $soLineMbo->getString('serviceitemnum'),
					'commission' => $soLineMbo->getDecimal('commission'),
					'enterby' => Xbo::getLoginUser(),
					'enterdate' => Xbo::setEnterDate()
				];

				$deliveryResult = self::executeServiceDelivery($context);
				
				if ($deliveryResult && $deliveryResult['status'] === 'success') {
					$processedCount++;
				} else {
					$errorMessages[] = "For service line {$solinenum}: " . ($deliveryResult['message'] ?? 'Unknown error.');
				}
			}
		
			if (!empty($errorMessages)) {
				throw new Exception(implode(' | ', $errorMessages));
			}
			
			$tm->commit();
			Super::sendJsonResponse("success", "{$processedCount} service(s) have been successfully delivered.");

		} catch (Exception $e) {
			$tm->rollback();
			$finalMessage = "Process failed. Details: " . $e->getMessage();
			Super::sendJsonResponse("error", $finalMessage);
		}
	}
	private static function executeReturnToStock(array $context): array 
	{
		
		$return_line_cost = (float)$context['returnqty'] * (float)$context['return_cost_per_unit'];
		$return_line_price = (float)$context['returnqty'] * (float)$context['return_price_per_unit'];
		$return_batch_num = "RETURN-" . $context['sonum'] . "-" . $context['solinenum'];

		// Tambah Stok Fisik ke invfifolifocost ---
		(new Set())->useTransactionManager()
			->MboSetRemote("invfifolifocost")
			->setValue("itemnum", $context['itemnum'])
			->setValue("warehouse", $context['towarehouse'])
			->setValue("racknum", $context['toracknum'])
			->setValue("quantity", (float)$context['returnqty']) // Kuantitas positif (masuk)
			->setValue("unitcost", (float)$context['return_cost_per_unit']) // Biaya asli
			->setValue("costdate", $context['actualdate']) // Tanggal barang kembali
			->setValue("batchnum", $return_batch_num)
			->setValue("transtype", "RETURN")
			->setValue("orgcode", $context['orgcode'])
			->setValue("sitecode", $context['sitecode'])
			->setValue("enterby", $context['enterby'])
			->setValue("enterdate", $context['enterdate'])
			->setValue("expdate", $context['expdate'])
			->setValue("conditiontype", $context['conditiontype'])
			->add();

		// Catat Transaksi Finansial audit dan jurnal pembalik.
		(new Set())->useTransactionManager()
			->MboSetRemote("matsaletrans")
			->setValue("sonum", $context['sonum'])
			->setValue("solinenum", $context['solinenum'])
			->setValue("customercode", $context['customercode'])
			->setValue("itemnum", $context['itemnum'])
			->setValue("transtype", "RETURN")
			->setValue("conditiontype", $context['conditiontype'])
			->setValue("description", $context['description'])
			->setValue("issuetype", $context['costtype'])
			->setValue("actualdate", $context['actualdate'])
			->setValue("quantity", (float)$context['returnqty']) // Kuantitas positif
			->setValue("unitcost", (float)$context['return_cost_per_unit']) // Biaya asli
			->setValue("linecost", $return_line_cost) // Total biaya
			->setValue("unitprice", (float)$context['return_price_per_unit']) // Harga jual asli
			->setValue("lineprice", $return_line_price) // Total harga jual
			->setValue("towarehouse", $context['towarehouse'])
			->setValue("torack", $context['toracknum'])
			->setValue("batchnum", $return_batch_num)
			->setValue("expdate", $context['expdate'])
			->setValue("gldebitacct", $context['gldebitacct'])  // Debit: Akun Aset Inventaris
			->setValue("glcreditacct", $context['glcreditacct']) // Kredit: Akun HPP (COGS)
			->setValue("orgcode", $context['orgcode'])
			->setValue("sitecode", $context['sitecode'])
			->setValue("enterby", $context['enterby'])
			->setValue("enterdate", $context['enterdate'])
			->setValue("salesman", $context['salesman'])
			->add();

		// Jika kedua operasi insert berhasil (tidak ada exception), kembalikan status sukses.
		return ['status' => 'success', 'message' => 'Return to stock executed successfully.'];
	}


	/* Main Function for Return Sales Item  */
	public static function returnItemSales() {
	
		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();

		try {
			$payload = Super::getParam("returnItemSales");

			if (empty($payload['sonum'])) {
				throw new Exception("Sales Order number (sonum) is required.");
			}
			if (empty($payload['data']) || !is_array($payload['data'])) {
				throw new Exception("Invalid or empty item data for return.");
			}
			
			$sonum = $payload['sonum'];
			$itemsToReturn = $payload['data'];
			
			$processedCount = 0;
			$errorMessages = [];

			$soHeaderMbo = (new Set())->useTransactionManager()->MboSetRemote("so")->where("sonum = ?", $sonum)->getMboSet()->getMbo();
			if (!$soHeaderMbo) {
				throw new Exception("Sales Order '{$sonum}' is not found.");
			}

			// 3. Loop untuk setiap item yang akan diretur
			foreach ($itemsToReturn as $item) {
				$solinenum = $item['solinenum'] ?? null;
				$returnqty = (float)($item['returnqty'] ?? 0);
				$towarehouse = $item['towarehouse'] ?? null;

				// Validasi data per item
				if (empty($solinenum) || $returnqty <= 0 || empty($towarehouse)) {
					$errorMessages[] = "Incomplete data for one of the items (missing solinenum, returnqty, or towarehouse).";
					continue; // Lanjut ke item berikutnya
				}

				// Dapatkan Konteks salesorderline 
				$soLineMbo = (new Set())->useTransactionManager()
										->MboSetRemote("soline")
										->where("sonum = ? AND solinenum = ?", $sonum, $solinenum)
										->getMboSet()->getMbo();

				if (!$soLineMbo) {
					$errorMessages[] = "Sales Order Line {$solinenum} not found for SO {$sonum}.";
					continue;
				}

				$itemnum = $soLineMbo->getString('itemnum');
				$customercode = $soHeaderMbo->getString('customercode');
			
				$context = [
					'sonum' => $sonum,
					'solinenum' => $solinenum,
					'itemnum' => $itemnum,
					'returnqty' => $returnqty,
					'towarehouse' => $towarehouse,
					'toracknum' => $item['toracknum'] ?? null,
					'return_cost_per_unit' => $soLineMbo->getDecimal('unitcost'),
					'return_price_per_unit' => $soLineMbo->getDecimal('unitpricediscount'),
					'conditiontype' => $soLineMbo->getString('conditiontype'),
					'description' => $soLineMbo->getString('description'),
					'costtype' => $soLineMbo->getString('costtype'),
					'customercode' => $customercode,
					'enterby' => Xbo::getLoginUser(),
					'enterdate' => Xbo::setEnterDate(),
					'actualdate' => $payload['returndate'] ?? Xbo::setEnterDate(),
					'glcreditacct' => Xbo::getCustomerAcct($customercode),      // Kredit: Akun HPP (COGS)
					'gldebitacct' => Xbo::getwhgl($towarehouse, "noprint"),             // Debit: Akun Aset Inventaris
					'orgcode' => $soHeaderMbo->getString("orgcode"),
					'sitecode' => $soHeaderMbo->getString("sitecode"),
					'salesman' => $soHeaderMbo->getString("employeesid")
				];

	
				$returnResult = self::executeReturnToStock($context);

				if ($returnResult['status'] === 'success') {
					$processedCount++;
				} else {
					$errorMessages[] = "For item {$itemnum} (SO Line {$solinenum}): " . ($returnResult['message'] ?? 'Unknown error.');
				}
			} 

		
			if (!empty($errorMessages)) {
				
				throw new Exception(implode(' | ', $errorMessages));
			}
			
		
			$tm->commit();
			Super::sendJsonResponse("success", "{$processedCount} item(s) have been successfully returned to stock.");

		} catch (Exception $e) {
			$tm->rollback();
			$finalMessage = "Process failed. Details: " . $e->getMessage();
			Super::sendJsonResponse("error", $finalMessage);
		}
	}

	private static function executeFifoLifoSelling($itemnum, $quantity_needed, $issuetype, $context, $warehouse) {

		$sorter = "costdate ASC"; // Default FIFO
		if ($issuetype === "LIFO") $sorter = "costdate DESC";
		elseif ($issuetype === "EXPDATE") $sorter = "expdate ASC";

		$whereClause = "itemnum='$itemnum' AND warehouse='$warehouse' AND quantity > 0";
		
		// Ambil semua batch fisik yang tersedia, sudah diurutkan sesuai aturan.
		$setFifo = new Set();
		$fifoStockSet = $setFifo->useTransactionManager()
								->MboSetRemote("invfifolifocost")
								->setWhere($whereClause)
								->order($sorter)
								->getMboSet();

		// VALIDASI STOK REAL-TIME (Anti-Race Condition)
		$total_available_realtime = 0;
		$allBatches = $fifoStockSet->fetchData(); // Ambil semua data sekali untuk efisiensi

		if (empty($allBatches)) {
			return ['status' => 'error', 'message' => "Issuing failed for item {$itemnum}. No stock available in warehouse {$warehouse}."];
		}

		foreach ($allBatches as $batch) {
			$total_available_realtime += (float)$batch['quantity'];
		}

		if ($total_available_realtime < $quantity_needed) {
			$errorMessage = "Issuing failed for item {$itemnum}. Real-time stock is insufficient. Needed: {$quantity_needed}, Available: {$total_available_realtime}.";
			return ['status' => 'error', 'message' => $errorMessage];
		}
		
		// Harvesting item quantity
		$remaining_needed = $quantity_needed;
		foreach ($allBatches as $fifoMbo) {
			if ($remaining_needed <= 0) break; // Hentikan jika kebutuhan sudah terpenuhi

			$expdate = $fifoMbo["expdate"]; 
			
			if ($issuetype === "EXPDATE" && empty($expdate)) {
				$batch_info = $fifoMbo['batchnum'] ?? $fifoMbo['invfifolifocostid'];
				return ['status' => 'error', 'message' => "Issuing failed. Item {$itemnum} requires an expiration date, but batch {$batch_info} does not have one."];
			}
			
			$fifo_id = $fifoMbo['invfifolifocostid'];
			$old_qty = (float)$fifoMbo['quantity'];
			
			$take_from_this_batch = min($old_qty, $remaining_needed);
			
			$new_qty = $old_qty - $take_from_this_batch;
			(new Set())->useTransactionManager()
					->MboSetRemote("invfifolifocost")
					->setWhere("invfifolifocostid='$fifo_id'")
					->setValue("quantity", $new_qty)
					->change();
			
			$unitcost_from_batch = (float)$fifoMbo["unitcost"];
			$linecost_cogs = $unitcost_from_batch * $take_from_this_batch; // Ini adalah HPP (COGS)

			// PERHITUNGAN PROFIT BARU
			$unitPrice = (float)$context['unitprice'];
			$discount = (float)$context['discountpercentage'];
			$linepricediscount = (float)$context['unitpricediscount'] * $take_from_this_batch * -1;
			// Profit sebelum diskon
			$unitProfit = $unitPrice - $unitcost_from_batch;
			$lineProfit = $unitProfit * $take_from_this_batch;

			// Profit setelah diskon
			$discountedUnitPrice = $unitPrice * (1 - ($discount / 100));
			$unitProfitDiscount = $discountedUnitPrice - $unitcost_from_batch;
			$lineProfitDiscount = $unitProfitDiscount * $take_from_this_batch;

			//Commission Percentage
			$unit_price = (float)$context['unitprice'];
			$commission_percentage = (float)($context['commission'] ?? 0); 
			$unit_commission = $unit_price * ($commission_percentage / 100);
			$line_commission = $unit_commission * $take_from_this_batch;

			// Hitung Tax Price
			$taxrate = XboSet::getTaxRate($context["taxcode"]);
			$taxprice = $linepricediscount * ($taxrate / 100);
			
			(new Set())->useTransactionManager()
				->MboSetRemote("matsaletrans")
				->setValue("sonum", $context['sonum'])
				->setValue("solinenum", $context['solinenum'])
				->setValue("customercode", $context['customercode'])
				->setValue("itemnum", $itemnum)
				->setValue("transtype", "SHIPMENT")
				->setValue("actualdate", $context["actualdate"])
				->setValue("quantity", $take_from_this_batch * -1) // Kuantitas keluar
				->setValue("unitcost", $unitcost_from_batch)      // Biaya barang (COGS) per unit
				->setValue("linecost", $linecost_cogs * -1)       // Total biaya (COGS)
				->setValue("unitprice", $context['unitprice'])    // Harga Jual per unit (untuk referensi)
				->setValue("lineprice", (float)$context['unitprice'] * $take_from_this_batch * -1) // Total Harga Jual
				->setValue("unitpricediscount", $context['unitpricediscount'])    
				->setValue("linepricediscount", $linepricediscount)
				->setValue("warehouse", $fifoMbo["warehouse"])
				->setValue("racknum", $fifoMbo["racknum"])
				->setValue("batchnum", $fifoMbo["batchnum"])
				->setValue("expdate", $expdate) 
				->setValue("gldebitacct", $context["gldebitacct"])  // Akun HPP (COGS)
				->setValue("glcreditacct", $context["glcreditacct"]) // Akun Aset Inventaris
				->setValue("orgcode", $context["orgcode"])
				->setValue("sitecode", $context["sitecode"])
				->setValue("enterdate", $context["enterdate"])
				->setValue("enterby", $context["enterby"])
				->setValue("unitprofit", $unitProfit)
				->setValue("lineprofit", $lineProfit)
				->setValue("unitprofitdiscount", $unitProfitDiscount)
				->setValue("lineprofitdiscount", $lineProfitDiscount)
				->setValue("unitcommission", $unit_commission)
    			->setValue("linecommission", $line_commission * -1) 
				->setValue("taxcode", $context["taxcode"])
				->setValue("taxprice", $taxprice)
				->setValue("costdate", $fifoMbo["costdate"])
				->setValue("costtype", $context["costtype"])
				->setValue("issuetype", $context["issuetype"])
				->setValue("pricecode", $context["pricecode"])
				->setValue("conditiontype", $context["conditiontype"])
				->setValue("description", $context["description"])
				->setValue("linetype", $context["linetype"])
				->setValue("paymenttype", $context["paymenttype"])
				->setValue("salesman", $context["salesman"])
				->setValue("financialperiod", $context["financialperiod"])
				->setValue("transdate", $context["transdate"])
				->setValue("shiplinenum", $context["shiplinenum"])
				->add();

			//paymenttype contactname financialperiod transdate
			$remaining_needed -= $take_from_this_batch;
		}
		
		$measureunit = (new Set())->useTransactionManager()->MboSetRemote("item")->setWhere("itemnum='$itemnum'")->getMboSet()->getMbo()->getString("issueunit");
		return ['status' => 'success', 'message' => "Successfully shipped {$quantity_needed} {$measureunit} of {$itemnum}."];
	}


	private static function executeAvgSelling($itemnum, $quantity_needed, $warehouse, $racknum, $context) {
		
	
		$whereClause = "itemnum='$itemnum' AND warehouse='$warehouse' AND quantity > 0";

		if (!empty($racknum)) {
			$safe_racknum = addslashes($racknum);
			$whereClause .= " AND racknum = '{$safe_racknum}'";
		} else {
			$whereClause .= " AND (racknum IS NULL OR racknum = '')";
		}
		
		// Ambil semua batch dari lokasi yang ditentukan, diurutkan FIFO sebagai default
		$availableBatchesSet = (new Set())->useTransactionManager()
										->MboSetRemote("invfifolifocost")
										->setWhere($whereClause)
										->order("costdate ASC") // FIFO di dalam lokasi terpilih
										->getMboSet();
		
		// VALIDASI STOK REAL-TIME di lokasi tersebut
		$total_available_in_location = 0;
		$allBatchesInLocation = $availableBatchesSet->fetchData();

		if (empty($allBatchesInLocation)) {
			$location_info = $warehouse . (!empty($racknum) ? "/{$racknum}" : "");
			return ['status' => 'error', 'message' => "Issuing failed for item {$itemnum}. No stock found at the specified location ({$location_info})."];
		}

		foreach ($allBatchesInLocation as $batch) {
			$total_available_in_location += (float)$batch['quantity'];
		}
		
		if ($total_available_in_location < $quantity_needed) {
			$location_info = $warehouse . (!empty($racknum) ? "/{$racknum}" : "");
			$errorMessage = "Issuing failed for item {$itemnum}. Not enough stock at location {$location_info}. Needed: {$quantity_needed}, Available: {$total_available_in_location}.";
			return ['status' => 'error', 'message' => $errorMessage];
		}

		// 4. Ambil Biaya Rata-Rata (Average Cost) dari item ini
		$inventoryMbo = (new Set())->useTransactionManager()
								->MboSetRemote("v_invbalance")
								->setWhere("itemnum='$itemnum' AND warehouse='$warehouse'")
								->getMboSet()->getMbo();
		
		if(!$inventoryMbo){
			return ['status' => 'error', 'message' => "Could not retrieve average cost for item {$itemnum} in warehouse {$warehouse}."];
		}
		$avgcost = (float)$inventoryMbo->getString('avgcost');

		// PROSES ISSUING dari LOKASI yang DITENTUKAN
		$remaining_needed = $quantity_needed;
		foreach ($allBatchesInLocation as $batchMbo) {
			if ($remaining_needed <= 0) break;

			$batch_id = $batchMbo['invfifolifocostid'];
			$old_qty = (float)$batchMbo['quantity'];
			$expdate = $batchMbo['expdate'];
			$batchnum = $batchMbo['batchnum'];
			$costdate = $batchMbo['costdate'];
			$take_from_this_batch = min($old_qty, $remaining_needed);
			
			// --- AKSI A: Kurangi Stok Fisik di invfifolifocost ---
			$new_qty = $old_qty - $take_from_this_batch;
			(new Set())->useTransactionManager()
					->MboSetRemote("invfifolifocost")
					->setWhere("invfifolifocostid='$batch_id'")
					->setValue("quantity", $new_qty)
					->change();
			
			// --- AKSI B: Catat Transaksi di matsaletrans ---
			// Untuk AVG, linecost dihitung dari avgcost, bukan biaya batch.
			$linecost_cogs = $avgcost * $take_from_this_batch; // Ini adalah HPP (COGS)
			$unitPrice = (float)$context['unitprice'];
			$discount = (float)$context['discountpercentage'];
			$linepricediscount = (float)$context['unitpricediscount'] * $take_from_this_batch * -1;

			// Profit sebelum diskon
			$unitProfit = $unitPrice - $avgcost;
			$lineProfit = $unitProfit * $take_from_this_batch;


			// Profit setelah diskon
			$discountedUnitPrice = $unitPrice * (1 - ($discount / 100));
			$unitProfitDiscount = $discountedUnitPrice - $avgcost;
			$lineProfitDiscount = $unitProfitDiscount * $take_from_this_batch;

			// Hitung nilai komisi
			$unit_price = (float)$context['unitprice'];
			$commission_percentage = (float)($context['commission'] ?? 0);
			$unit_commission = $unit_price * ($commission_percentage / 100);
			$line_commission = $unit_commission * $take_from_this_batch;

			// Hitung Tax Price
			$taxrate = XboSet::getTaxRate($context["taxcode"]);
			$taxprice = $linepricediscount * ($taxrate / 100);

			(new Set())->useTransactionManager()
				->MboSetRemote("matsaletrans")
				->setValue("sonum", $context['sonum'])
				->setValue("solinenum", $context['solinenum'])
				->setValue("customercode", $context['customercode'])
				->setValue("itemnum", $itemnum)
				->setValue("transtype", "SHIPMENT")
				->setValue("actualdate", $context["actualdate"])
				->setValue("costtype", $context["costtype"])
				->setValue("quantity", $take_from_this_batch * -1)
				->setValue("unitcost", $avgcost) 
				->setValue("linecost", $linecost_cogs * -1)   // Total biaya (COGS)
				->setValue("unitprice", $context['unitprice']) // Harga Jual per unit (untuk referensi)
				->setValue("lineprice", (float)$context['unitprice'] * $take_from_this_batch * -1) // Total Harga Jual
				->setValue("unitpricediscount", $context['unitpricediscount']) 
				->setValue("linepricediscount", $linepricediscount) 
				->setValue("taxcode", $taxcode) 
				->setValue("taxprice", $taxprice) 
				->setValue("costdate",  $batchMbo["costdate"])
				->setValue("warehouse", $batchMbo["warehouse"])
				->setValue("racknum", $batchMbo["racknum"])
				->setValue("batchnum", $batchMbo["batchnum"])
				->setValue("expdate", $batchMbo["expdate"]) 
				->setValue("gldebitacct", $context["gldebitacct"])  // Akun HPP (COGS)
				->setValue("glcreditacct", $context["glcreditacct"]) // Akun Aset Inventaris
				->setValue("orgcode", $context["orgcode"])
				->setValue("sitecode", $context["sitecode"])
				->setValue("enterdate", $context["enterdate"])
				->setValue("enterby", $context["enterby"])
				->setValue("unitprofit", $unitProfit)
				->setValue("lineprofit", $lineProfit)
				->setValue("unitprofitdiscount", $unitProfitDiscount)
				->setValue("lineprofitdiscount", $lineProfitDiscount)
				->setValue("unitcommission", $unit_commission)
    			->setValue("linecommission", $line_commission * -1)
				->setValue("issuetype", $context["issuetype"])
				->setValue("pricecode", $context["pricecode"])
				->setValue("conditiontype", $context["conditiontype"])
				->setValue("description", $context["description"])
				->setValue("linetype", $context["linetype"])
				->setValue("transdate", $context["transdate"])
				->setValue("financialperiod", $context["financialperiod"])
				->setValue("salesman", $context["salesman"])
				->setValue("paymenttype", $context["paymenttype"])
				->setValue("shiplinenum", $context["shiplinenum"])
				->add();
				$remaining_needed -= $take_from_this_batch;

				//paymenttype contactname financialperiod transdate
		}
		
	
		$measureunit = (new Set())->useTransactionManager()->MboSetRemote("item")->setWhere("itemnum='$itemnum'")->getMboSet()->getMbo()->getString("issueunit");
		
		return ['status' => 'success', 'message' => "Successfully shipped {$quantity_needed} {$measureunit} of {$itemnum} (AVG Cost)."];
	}
	
	/* Main Function for Deliver Sales Item  */
	public static function deliverItemSales() {
	
		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();

		try {
			$payload = Super::getParam("deliverItemSales");

			if (empty($payload['sonum'])) {
				throw new Exception("Sales Order number (sonum) is required.");
			}
			if (empty($payload['data']) || !is_array($payload['data'])) {
				throw new Exception("Invalid or empty item data provided.");
			}
			

			$sonum = $payload['sonum'];
			$itemsToDeliver = $payload['data'];
			$actualdate = $payload["actualdate"];
			$shippingcost = $payload["shippingcost"];
			$shiplinenum = 0; // berarti tidak ada shiplinenum
			if ($shippingcost > 0) {
				$shippingdate =  $actualdate ?? Xbo::setEnterDate();
				$shiplinenum = Xbo::getLineNumber("soshipment", "shiplinenum", "sonum='$sonum'");
				(new Set())->useTransactionManager()
				->MboSetRemote("soshipment")
				->setValue("sonum", $sonum) // Referensi ke Sales Order
                ->setValue("shiplinenum", $shiplinenum)
				->setValue("enterby", Xbo::getLoginUser())
				->setValue("enterdate", Xbo::setEnterDate())
				->setValue("shippingdate", $shippingdate)
				->setValue("shippingcost", $shippingcost)
				->setValue("description", "Shipping Cost for Sales Order " . $sonum)
				->add();
			}

			
			$processedCount = 0;
			$errorMessages = [];

			$soHeaderMbo = (new Set())->useTransactionManager()->MboSetRemote("so")->where("sonum = ?", $sonum)->getMboSet()->getMbo();
			if (!$soHeaderMbo) {
				throw new Exception("Sales Order '{$sonum}' not found.");
			}
			$discountPercentage = $soHeaderMbo->getDecimal('discountpercentage') ?? 0;


			foreach ($itemsToDeliver as $item) {
				
				$solinenum = $item['solinenum'] ?? null;
				$deliveredqty = (float)($item['deliveredqty'] ?? 0);

				if (empty($solinenum) || $deliveredqty <= 0) {
					$errorMessages[] = "Invalid data for one of the items (missing solinenum or invalid quantity).";
					continue;
				}

				// Dapatkan Konteks Lengkap dari Database ---
				$soLineMbo = (new Set())->useTransactionManager()
										->MboSetRemote("soline")
										->where("sonum = ? AND solinenum = ?", $sonum, $solinenum)
										->getMboSet()->getMbo();

				if (!$soLineMbo) {
					$errorMessages[] = "Sales Order Line {$solinenum} not found for SO {$sonum}.";
					continue;
				}
				

				$itemnum = $soLineMbo->getString("itemnum");
				$warehouse = $soLineMbo->getString("warehouse");
				$racknum = $soLineMbo->getString("racknum");

				$inventoryMbo = (new Set())->useTransactionManager()
										->MboSetRemote("inventory")
										->where("itemnum = ? AND warehouse = ?", $itemnum, $warehouse)
										->getMboSet()->getMbo();
				$issuetype = $inventoryMbo ? $inventoryMbo->getString("issuetype") : null;
				
				if (empty($issuetype)) {
					$errorMessages[] = "Issuetype not defined for item {$itemnum} in warehouse {$warehouse}.";
					continue;
				}
				$context = [
					'sonum' => $sonum,
					'solinenum' => $solinenum,
					'itemnum' => $itemnum,
					'shiplinenum' => $shiplinenum,
					'issuetype' => $issuetype,
					'customercode' => $soHeaderMbo->getString('customercode'),
					'paymenttype' => $soHeaderMbo->getString('paymenttype'),
					'salesman' => $soHeaderMbo->getString('employeesid'),
					'financialperiod' => Xbo::getFinancialPeriod(),
					'linetype' => $soLineMbo->getString('linetype'),
					'description' => $soLineMbo->getString('description'),
					'unitprice' => $soLineMbo->getDecimal('unitprice'),
					'unitpricediscount' => $soLineMbo->getDecimal('unitpricediscount'),
					'discountpercentage' => $discountPercentage,
					'commission' => $soLineMbo->getDecimal('commission'), 
					'taxcode' => $soLineMbo->getString('taxcode'),
					'pricecode' => $soLineMbo->getString('pricecode'),
					'conditiontype' => $soLineMbo->getString('conditiontype'),
					'costtype' => $soLineMbo->getString('costtype'),
					'issuetype' => "SALE",
					'enterby' => Xbo::getLoginUser(),
					'enterdate' => Xbo::setEnterDate(),
					'transdate' => Xbo::setEnterDate(),
					'actualdate' => $actualdate ?? Xbo::setEnterDate(), // Ambil dari payload jika ada, jika tidak gunakan tanggal sekarang
					'glcreditacct' => $soLineMbo->getString('glcreditacct'),
					'gldebitacct' => $soLineMbo->getString('gldebitacct'),
					'orgcode' => $soHeaderMbo->getString("orgcode"),
					'sitecode' => $soHeaderMbo->getString("sitecode")
				];

				$issueResult = null;
				if ($issuetype === "FIFO" || $issuetype === "LIFO" || $issuetype === "EXPDATE") {
					$issueResult = self::executeFifoLifoSelling($itemnum, $deliveredqty, $issuetype, $context, $warehouse);
				} elseif ($issuetype === "AVG") {
					$issueResult = self::executeAvgSelling($itemnum, $deliveredqty, $warehouse, $racknum, $context);
				} else {
					$issueResult = ['status' => 'error', 'message' => "Unsupported issuetype: {$issuetype}"];
				}
				
				if ($issueResult && $issueResult['status'] === 'success') {
					$processedCount++;
				} else {
					$errorMessages[] = "For item {$itemnum} (SO Line {$solinenum}): " . ($issueResult['message'] ?? 'Unknown error.');
				}
			} 

			
			if (!empty($errorMessages)) {
				throw new Exception(implode(' | ', $errorMessages));
			}
			
			
		
			$tm->commit();
			Super::sendJsonResponse("success", "{$processedCount} item(s) have been successfully delivered to the customer.");

		} catch (Exception $e) {
		
			$tm->rollback();
			$finalMessage = "Process failed. Details: " . $e->getMessage();
			Super::sendJsonResponse("error", $finalMessage);
		}
	}


	public static function getPriceCodeValue(){
		$payload = Super::getParam("getPriceCodeValue");
		$itemnum = $payload["itemnum"];
		$warehouse = $payload["warehouse"];
		$conditiontype = $payload["conditiontype"];
		$pricecode = $payload["unitpricecode"];
		$unitprice = 0;
		if(empty($itemnum)) {
			Super::sendJsonResponse("error", "Itemnum is not defined on payload");
		}
		if (empty($conditiontype)) {
			$condval = " AND conditiontype IS NULL";
			$params = [$itemnum, $pricecode, $warehouse]; 
		} else {
			$condval = " AND conditiontype = ?";
			$params = [$itemnum, $pricecode, $warehouse, $conditiontype];
		}

		$unitprice = (new Set())
			->useTransactionManager()
			->MboSetRemote("invprice")
			->where("itemnum = ? AND pricecode = ? AND warehouse = ? $condval", ...$params)
			->getMboValue("unitprice");

		Super::sendJsonResponse("success", "Unit Cost Retrieved", ["unitprice"=>$unitprice]);
	}
	public static function getWoGLAccount(){
		$payload = Super::getParam("getWoGLAccount");
		$sonum = $payload["sonum"];
		$wonum = $payload["wonum"];

		$glaccount = (new Set())->MboSetRemote("workorder")->where("wonum=?", $wonum)->getMboValue("glaccount");
		$customercode = (new Set())->MboSetRemote("so")->where("sonum=?", $sonum)->getMboValue("customercode");
		$rbniacc = (new Set())->useTransactionManager()->MboSetRemote("customer")->where("customercode=?", $customercode)->getMboValue("rbniacct");

		Set::throws(["glaccount"=>$glaccount, "rbniacc"=>$rbniacc]);
	}
	public static function getServiceOrderInfo(){
		$serviceitemnum = Super::getParam("getServiceOrderInfo");
		$sonum = Super::getParam("sonum");
		$set = new Set();
		$serviceitemSet = $set->MboSetRemote("serviceitems")->setWhere("serviceitemnum='$serviceitemnum'")->MboRemote("description, costvolatilityrisk, leadtimerisk, complexityrisk, serviceunit")->MboRemote("stdcost")->getMboSet();
		$serviceitem = $serviceitemSet->getMbo();
		$description = $serviceitem->getString("description");
		$stdcost = $serviceitem->getString("stdcost");
		$costvaolatilityrisk = $serviceitem->getString("costvolatilityrisk");
		$leadtimerisk = $serviceitem->getString("leadtimerisk");
		$complexityrisk = $serviceitem->getString("complexityrisk");
		$serviceunit = $serviceitem->getString("serviceunit");
		$riskfactor = (1 + ($costvaolatilityrisk + $leadtimerisk + $complexityrisk) / 100);
		$unitcost = $stdcost * $riskfactor;
		$unitcost = number_format((float)$unitcost, 2, '.', '');
		$unitprice= 0;
		$soSet = (new Set())->useTransactionManager()->MboSetRemote("so")->where("sonum=?", $sonum)->getMboSet();
		$so = $soSet->getMbo();
		$customercode = $so->getString("customercode");
		$gldebitacct = (new Set())->useTransactionManager()->MboSetRemote("customer")->where("customercode=? ", $customercode)->getMboValue("rbniacct");

		Set::throws(["description"=>$description, "unitcost"=>$unitcost, "unitprice"=>$unitprice, "gldebitacct"=>$gldebitacct, "measureunit"=>$serviceunit]);
	}
	public static function getItemOrderInfo(){
		$itemnum = Super::getParam("getItemOrderInfo");
		$sonum = Super::getParam("sonum");
		$recordid = Super::getParam("recordid");

		if(!$itemnum){
			Super::sendJsonResponse("error", "Itemnum is null");
		}
		
		$soSet = (new Set())->useTransactionManager()->MboSetRemote("so")->where("sonum=?", $sonum)->getMboSet();
		$so = $soSet->getMbo();
		$customercode = $so->getString("customercode");

		$itemSet = (new Set())->useTransactionManager()->MboSetRemote("item i")
		->setRelationship("v_invbalance vib", "i.itemnum = vib.itemnum")
		->setRelationship("invprice ip", "i.itemnum = ip.itemnum")
		->limit(1)
		->where("i.itemnum=? ", $itemnum)
		->MboRemote("i.description, vib.avgcost, i.issueunit")
		->getMboSet();

		$item = $itemSet->getMbo();

		$description = $item->getString("description");
		$unitcost = $item->getString("unitcost");
		$unitprice= 0;
		$issueunit = $item->getString("issueunit");

		$issuetype = (new Set())->useTransactionManager()->MboSetRemote("inventory")->where("itemnum=?", $itemnum)->limit(1)->MboRemote("issuetype")->getMboSet()->getMbo()->getString("issuetype");
		
		if($issuetype == "FIFO") {
			$filter = "ASC";
			$costdate_colummn = "costdate";
		}
		else if($issuetype == "LIFO") {
			$filter = "DESC";
			$costdate_colummn = "costdate";
		}
		else if($issuetype == "EXPDATE"){
			$filter = "ASC";
			$costdate_colummn = "expdate";
		}else{
			$filter = "ASC";
			$costdate_colummn = "costdate";
		}

		$v_inv_row = (new Set())->MboSetRemote("v_invbalance")
                           ->where("v_invbalanceid = ?", $recordid)
                           ->getMboSet()
                           ->fetch();

		if (!$v_inv_row) {
			throw new Exception("Data v_invbalance with ID '$recordid' is not found.");
		}
		
		// 2. Ekstrak nilai-nilai yang dibutuhkan
		$itemnum = $v_inv_row['itemnum']; // Kita butuh itemnum juga
		$warehouse = $v_inv_row['warehouse'];
		$racknum = $v_inv_row['racknum'];
		$conditiontype = $v_inv_row['conditiontype'];
		$avgcost = $v_inv_row['avgcost'];
		$remarks = null;

		$invfifolifoSet = (new Set())->MboSetRemote("invfifolifocost")
									->where("itemnum = ?", $itemnum)
									->where("warehouse = ?", $warehouse);

		 if ($racknum === null || $racknum === '') {
			$invfifolifoSet->whereNull("racknum");
		} else {
			$invfifolifoSet->where("racknum = ?", $racknum);
		}


		if ($conditiontype === null || $conditiontype === '') {
			$invfifolifoSet->whereNull("conditiontype");
		} else {
			$invfifolifoSet->where("conditiontype = ?", $conditiontype);
		}

		$results = $invfifolifoSet->order("$costdate_colummn $filter")->getMboSet()->getMbo();
		$unitcost = floatval($results->getString("unitcost"));

		$wh_glaccount = (new Set())->useTransactionManager()->MboSetRemote("warehouse")->where("warehouse=?", $warehouse)->getMboValue("controlacc");


		$invprice = (new Set())->useTransactionManager()->MboSetRemote("invprice")->where("itemnum =? and defaultprice='Y' ", $itemnum)->MboRemote("pricecode, unitprice, commission")
		->getMboSet()->getMbo();
		$unitprice = floatval($invprice->getString("unitprice"));
		$pricecode = $invprice->getString("pricecode");
		$commission =  $invprice->getString("commission");

		if ($unitcost > $unitprice) {
			$percentage = (($unitcost - $unitprice) / $unitprice) * 100;
			$remarks = "The Unit Cost (" . number_format($unitcost, 0) . 
					") is higher than The Unit Price (" . number_format($unitprice, 2) . 
					") by " . number_format($percentage, 2) . "%.";
		} elseif ($unitprice > $unitcost) {
			$percentage = (($unitprice - $unitcost) / $unitcost) * 100;
			$remarks = "The Unit Price (" . number_format($unitprice, 0) . 
					") is higher than The Unit Cost (" . number_format($unitcost, 2) . 
					") by " . number_format($percentage, 2) . "%.";
		} else {
			$remarks = "The Unit Cost and Unit Price are equal.";
		}

		$gldebitacct = (new Set())->useTransactionManager()->MboSetRemote("customer")->where("customercode=? ", $customercode)->getMboValue("rbniacct");

		Set::throws(["description"=>$description, "unitcost"=>$unitcost, "unitprice"=>$unitprice, "avgcost"=>$avgcost,
		"pricecode"=>$pricecode, "measureunit"=>$issueunit, "gldebitacct"=> $gldebitacct, "glcreditacct"=>$wh_glaccount, 
		"conditiontype"=>$conditiontype, "warehouse"=> $warehouse, "racknum"=>$racknum, "issuetype"=>$issuetype, "remarks"=>$remarks, "commission" => $commission]);
	}
	public static function CreateWOforSOLine()
	{
		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();

		try {
			$inputData = Super::getParam("assignwoforsoline");
			if (empty($inputData) || !is_array($inputData)) {
				throw new InvalidArgumentException("Invalid data provided.");
			}

			// 1. Ambil data SOLINE
			// butuh data soline untuk validasi dan copy data
			// KEYS (sonum + solinenum)
			$solineKeys = [];
			foreach ($inputData as $row) {
				$key = $row['sonum'] . '|' . $row['solinenum'];
				$solineKeys[$key] = [
					'sonum' => $row['sonum'],
					'solinenum' => $row['solinenum']
				];
			}
			
			// Ambil data soline dari DB
			// Karena kuncinya komposit, kita loop sebentar untuk build query atau ambil satu per satu di loop utama
			// Untuk amannya, kita ambil di dalam loop utama tapi dengan cara yang efisien.

			// Ambil max sodistlinenum untuk SONUM pertama
			$firstSoNum = $inputData[0]['sonum'];
			$currentDistLineNum = (new Set())->useTransactionManager()
				->MboSetRemote("solinewo")
				->where("sonum = ?", $firstSoNum)
				->MboRemote("max(sodistlinenum) as currentDistLineNum")->getMboSet()->getMbo()->getString("currentDistLineNum");

			foreach ($inputData as $row) {
				$sonum = $row["sonum"];
				$solinenum = $row["solinenum"];
				$assignedQty = (float)$row["assignedqty"];
				$location = $row["location"];

				if ($assignedQty <= 0) {
					throw new Exception("Assigned Quantity must be greater than 0 for SO Line $solinenum.");
				}

				// A. Ambil Data SOLINE Terbaru & Validasi Stok
				$solineSet = (new Set())->useTransactionManager()
					->MboSetRemote("soline")
					->where("sonum = ? AND solinenum = ?", $sonum, $solinenum)
					->getMboSet();
				
				$soline = $solineSet->fetch();
				if (!$soline) {
					throw new Exception("SO Line $solinenum not found.");
				}

				$sellQty = (float)$soline['sellqty'];
				$currentRemaining = (new Set())->useTransactionManager()->MboSetRemote("v_solinewo")->where("sonum=? AND solinenum=?", $sonum, $solinenum)->getMboValue("remainingqty");
				
				// Validasi: Apakah cukup sisa untuk didistribusikan?
				if ($assignedQty > $currentRemaining) {
					throw new Exception("Cannot assign $assignedQty. Only $currentRemaining remaining for SO Line $solinenum.");
				}

				// B. Hitung Sisa Baru
				$newRemaining = $currentRemaining - $assignedQty;

				// C. Update SOLINE (Kurangi Sisa)
				(new Set())->useTransactionManager()
					->MboSetRemote("soline")
					->where("sonum = ? AND solinenum = ?", $sonum, $solinenum)
					->setValue("remainingqty", $newRemaining)
					->change();

				// D. Siapkan Data Distribusi Baru
				$unitPrice = (float)$soline['unitprice'];
				$unitCost = (float)$soline['unitcost'];
				$linePrice = $assignedQty * $unitPrice;
				$lineCost = $assignedQty * $unitCost;
				
				// Hitung persentase untuk referensi
				$percentage = ($sellQty > 0) ? round(($assignedQty / $sellQty) * 100, 2) : 0;

				// Handle Item Order Unit
				$orderUnit = $soline['orderunit'];
				if ($soline['linetype'] == 'item') {
					$orderUnit = (new Set())->useTransactionManager()->MboSetRemote("item")->where("itemnum=?", $soline["itemnum"])->getMboValue("issueunit");
				}

				$currentDistLineNum += 10; 
				// E. Insert ke SOLINEWO (Distribusi)
				(new Set())->useTransactionManager()
					->MboSetRemote("solinewo")
					->setValue("sonum", $sonum)
					->setValue("solinenum", $solinenum)
					->setValue("sodistlinenum", $currentDistLineNum)
					->setValue("linetype", $soline['linetype'])
					->setValue("itemnum", $soline['itemnum'])
					->setValue("description", $soline['description'])
					->setValue("quantity", $assignedQty)
					->setValue("unitprice", $unitPrice)
					->setValue("lineprice", $linePrice)
					->setValue("location", $location)
					->setValue("orderunit", $orderUnit)
					->setValue("warehouse", $soline['warehouse'])
					->setValue("racknum", $soline['racknum'])
					->setValue("conditiontype", $soline['conditiontype']) 
					->setValue("unitcost", $unitCost)
					->setValue("linecost", $lineCost)
					->setValue("percentage", $percentage)
					->add();

				
			}

			$tm->commit();
			Super::sendJsonResponse("success", "Distribution added successfully.");

		} catch (Exception $e) {
			$tm->rollback();
			Super::sendJsonResponse("error", "Failed to distribute: " . $e->getMessage());
		}
	}
		
	public static function CreatePO()
	{
		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();

		try {
			$sonum = Super::getParam('createpo');
			if (empty($sonum)) {
				throw new InvalidArgumentException("SO Number is required.");
			}

			$enterBy = Xbo::getLoginUser();
			$enterDate = Xbo::setEnterDate();

			// 1. Ambil semua SO Lines yang valid untuk dibuat PO
			// Kita perlu join dengan item -> inventory untuk mendapatkan vendor
			$soLines = (new Set())->useTransactionManager()
				->MboSetRemote("soline")
				->setRelationship("so", "soline.sonum = so.sonum")
				->setRelationship("inventory inv", "soline.itemnum = inv.itemnum AND soline.warehouse = inv.warehouse")
				->setRelationship("item", "soline.itemnum = item.itemnum")
				->where("soline.sonum = ? AND soline.linetype = 'item'", $sonum)
				->whereNull("soline.ponum") 
				->MboRemote("soline.*, so.orgcode, so.sitecode, so.description as so_desc, inv.vendorcode, item.issueunit")
				->getMboSet()
				->fetchData();

			if (empty($soLines)) {
				$tm->commit(); 
				Super::sendJsonResponse("success", "No eligible lines found to create PO.");
				return;
			}

			// 2. Kelompokkan Lines berdasarkan VENDOR
			$groupedLines = [];
			foreach ($soLines as $line) {
				// Jika vendor kosong, gunakan placeholder 'UNKNOWN' atau biarkan null
				// Strategi: Buat PO terpisah untuk item tanpa vendor agar user bisa isi manual
				$vendor = !empty($line['vendorcode']) ? $line['vendorcode'] : 'NO_VENDOR';
				$groupedLines[$vendor][] = $line;
			}

			$createdPOs = [];

			// 3. Loop per Vendor dan Buat PO
			foreach ($groupedLines as $vendorCode => $lines) {
				
				// Ambil data umum dari baris pertama
				$firstLine = $lines[0];
				$orgcode = $firstLine['orgcode'];
				$sitecode = $firstLine['sitecode'];
				$soDesc = $firstLine['so_desc'];
				$warehouse = $firstLine['warehouse']; 
				// A. Buat Header PO
				$ponum = (new Super())->getAutoNumber("PO", "update_next_value")->autonumber;
				$poDesc = "Supply for SO: $sonum - " . ($vendorCode === 'NO_VENDOR' ? 'Pending Vendor' : $vendorCode);
				
				// Ambil alamat warehouse
				$whSet = (new Set())->useTransactionManager()
					->MboSetRemote("warehouse")
					->where("warehouse = ?", $warehouse)
					->MboRemote("billingaddress, shippingaddress")
					->getMboSet()
					->fetch();

				(new Set())->useTransactionManager()->MboSetRemote("po")
					->setValue("ponum", $ponum)
					->setValue("description", $poDesc)
					->setValue("buyer", $enterBy)
					->setValue("priority", "NORMAL")
					->setValue("status", "NEW")
					->setValue("vendor", ($vendorCode === 'NO_VENDOR' ? null : $vendorCode)) 
					->setValue("statusdate", $enterDate)
					->setValue("orderdate", $enterDate)
					->setValue("enterdate", $enterDate)
					->setValue("enterby", $enterBy)
					->setValue("billtoaddress", $whSet['billingaddress'] ?? null)
					->setValue("shiptoaddress", $whSet['shippingaddress'] ?? null)
					->setValue("currencycode", "IDR") 
					->setValue("exchangerate", 1)
					->setValue("orgcode", $orgcode)
					->setValue("sitecode", $sitecode)
					->add();

				// PO Status
				(new Set())->useTransactionManager()->MboSetRemote("postatus")
					->setValue("ponum", $ponum)
					->setValue("status", "new")
					->setValue("changeby", $enterBy)
					->setValue("changedate", $enterDate)
					->setValue("remarks", "Generated from SO $sonum")
					->add();

				// B. Siapkan Data PO Lines untuk addStream
				$poLinesData = [];
				$lineNum = 10;
				$totalPreTax = 0;

				foreach ($lines as $line) {
					$itemnum = $line['itemnum'];
					$orderQty = $line['sellqty']; // Qty jual = Qty beli (back-to-back)
					
					// Ambil harga terakhir vendor (Cost)
					// Optimasi: Bisa di-move ke luar loop dengan whereIn jika mau lebih cepat
					$lastPrice = 0;
					if ($vendorCode !== 'NO_VENDOR') {
						$vendorPrice = (new Set())->useTransactionManager()
							->MboSetRemote("invvendor")
							->where("itemnum = ? AND vendor = ?", $itemnum, $vendorCode)
							->order("lastorderdate DESC") // Ambil yang terbaru
							->limit(1)
							->getMboValue("lastprice");

						$lastPrice = (float)$vendorPrice;
					}

					$lineCost = $lastPrice * $orderQty;
					$totalPreTax += $lineCost;

					// Konversi unit (jika perlu)
					$conversionFactor = 1; 
					
					// GL Account
					$glDebit = Xbo::getwhgl($warehouse, "noprint");
					$glCredit = ($vendorCode !== 'NO_VENDOR') ? Xbo::getVendorAcct($vendorCode, $orgcode, $sitecode) : null;

					$poLinesData[] = [
						"ponum" => $ponum,
						"polinenum" => $lineNum,
						"linetype" => "item",
						"itemnum" => $itemnum,
						"description" => $line['description'],
						"unitcost" => $lastPrice,
						"linecost" => $lineCost,
						"orderqty" => $orderQty,
						"orderunit" => $line['orderunit'], // Atau issueunit
						"conversionfactor" => $conversionFactor,
						"warehouse" => $warehouse,
						"enterby" => $enterBy,
						"receiptstatus" => "NONE",
						"gldebitacct" => $glDebit,
						"glcreditacct" => $glCredit,
						"sonum" => $sonum,
						"solinenum" => $line['solinenum'] 
					];

					// Update SO Line dengan PO yang baru dibuat (untuk tracking)
					(new Set())->useTransactionManager()->MboSetRemote("soline")
						->where("solineid = ?", $line['solineid']) // Pakai ID lebih aman
						->setValue("ponum", $ponum)
						->setValue("polinenum", $lineNum)
						->change();

					$lineNum += 10;
				}

				// C. Insert PO Lines Massal
				if (!empty($poLinesData)) {
					(new Set())->useTransactionManager()->MboSetRemote("poline")->addStream($poLinesData);
				}

				// D. Update Total Header PO
				(new Set())->useTransactionManager()->MboSetRemote("po")
					->where("ponum = ?", $ponum)
					->setValue("pretaxtotal", $totalPreTax)
					->setValue("totalcost", $totalPreTax)
					->setValue("totalbasecost", $totalPreTax)
					->change();

				$createdPOs[] = $ponum;
			}

			$tm->commit();
			
			$msg = "Success. Created " . count($createdPOs) . " PO(s): " . implode(", ", $createdPOs);
		
			Super::sendJsonResponse("success", $msg);
		} catch (Exception $e) {
			$tm->rollback();
			Super::sendJsonResponse("error", "Error: ".$e->getMessage());
		}
	}

	public static function addAdditionalRequestFromInvoice(){
		$invoicenum = $_POST["addAdditionalRequestFromInvoice"];
		$sonum = $_POST["sonum"];
		$linetype = $_POST["linetype"];
		$itemnum = $_POST["itemnum"];
		$partnum = $_POST["partnum"];
		$description = $_POST["description"];
		$remarks = $_POST["remarks"];
		$warehouse = $_POST["warehouse"];
		$racknum = $_POST["racknum"];
		$conditiontype = $_POST["conditiontype"];
		$costtype = $_POST["costtype"];
		$pricecode = $_POST["pricecode"];
		$unitprice = $_POST["unitprice"];
		$lineprice = $_POST["lineprice"];
		$unitpricediscount = $_POST["unitpricediscount"];
		$linepricediscount = $_POST["linepricediscount"];
		$taxcode = $_POST["taxcode"];
		$taxprice = $_POST["taxprice"];
		$taxpricediscount = $_POST["taxpricediscount"];
		$enterby = $_POST["enterby"];
		$enterdate = $_POST["enterdate"];
		$requireddate = $_POST["requireddate"];
		$sellqty = $_POST["qtysell"];
		$orderunit = $_POST["orderunit"];
		$customercode = $_POST["customercode"];
		$gldebitacct = $_POST["gldebitacct"];
		$glcreditacct = $_POST["glcreditacct"];
		$sentstatus = "NONE";
		$set = new Set();
		$msg = "";
		$invoiceSet = $set->useTransactionManager()->MboSetRemote("invoice")->setWhere("invoicenum='$invoicenum'")
		->MboRemote("sonum")->getMboSet();
		$invoice = $invoiceSet->getMbo();
		$sonum = $invoice->getString("sonum");
		
		if($sonum!="" || $sonum != null){
			$set = new Set();
			$soSet = $set->useTransactionManager()->MboSetRemote("so")->setWhere("sonum='$sonum'")->MboRemote("discountedtotalprice")->MboRemote("discountpercentage")->getMboSet()->getMbo();
			
			$discountpercentage = $soSet->getString("discountpercentage");
			if($discountpercentage == null)$discountpercentage = 0;
			$discountedtotalprice = $soSet->getString("discountedtotalprice");
			if($discountedtotalprice == null)$discountedtotalprice = 0;
			
			$solinenum = Xbo::getLineNumber("soline", "solinenum", "sonum='$sonum'");
			$set = new Set();
			$set->useTransactionManager()->MboSetRemote("soline")
			->setValue("sonum", $sonum)->setValue("solinenum", $solinenum)->setValue("linetype", $linetype)->setValue("itemnum", $itemnum)
			->setValue("partnum", $partnum)->setValue("description", $description)->setValue("remarks", $remarks)->setValue("warehouse", $warehouse)
			->setValue("racknum", $racknum)->setValue("conditiontype", $conditiontype)->setValue("costtype", $costtype)->setValue("pricecode", $pricecode)
			->setValue("unitprice", $unitprice)->setValue("lineprice", $lineprice)->setValue("unitpricediscount", $unitpricediscount)->setValue("linepricediscount", $linepricediscount)
			->setValue("taxcode", $taxcode)->setValue("taxprice", $taxprice)->setValue("taxpricediscount", $taxpricediscount)->setValue("enterby", $enterby)
			->setValue("enterdate", $enterdate)->setValue("requireddate", $requireddate)->setValue("sellqty", $sellqty)->setValue("orderunit", $orderunit)
			->setValue("customercode", $customercode)->setValue("gldebitacct", $gldebitacct)->setValue("glcreditacct", $glcreditacct)->setValue("sentstatus", $sentstatus)
			->add();
			$msg = "added";
			
			
		
		ActionSet::SumSOCostBackend($sonum);
		
		}else{
			$msg = "sonumnotfound";
		}
		$ss= new stdClass;
		$ss->msg = $msg;
		Set::throws($ss);
		
	}
	
	public static function CreateWOFromServiceDistribution()
	{
		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();

		try {
			$sonum = Super::getParam("createwofromservicedistribution");
			$reqParentParam  = Super::getParam("reqParent"); //Y or N
			$requiresParent = ($reqParentParam === 'Y' || $reqParentParam === 'true');

			if (empty($sonum)) {
				throw new InvalidArgumentException("SO Number is required.");
			}

			$enterBy = Xbo::getLoginUser();
			$enterDate = Xbo::setEnterDate();

			// 1. Ambil Data Header SO
			$soData = (new Set())->useTransactionManager()
				->MboSetRemote("so")
				->where("sonum = ?", $sonum)
				->MboRemote("orgcode, sitecode, customercode, description")
				->getMboSet()
				->fetch();

			if (!$soData) {
				$tm->commit();
				Super::sendJsonResponse("error", "Sales Order '$sonum' not found.");
			}

			$solinewoData = (new Set())->useTransactionManager()->MboSetRemote("solinewo")->where("sonum = ?", $sonum)->getMboSet();
			
			if($solinewoData->isEmpty()){
				Super::sendJsonResponse("error", "Sales Order '$sonum' does not have distributed work orders.");
			}
			

			$orgcode = $soData['orgcode'];
			$sitecode = $soData['sitecode'];
			$customercode = $soData['customercode'];
			$soDescription = $soData['description'];

			$parentWonum = null;
			$wbscode = null;
			$budgetnum = null;
			$budgetlinenum = null;

			if ($requiresParent) {
				// A. Buat WO Parent Baru
				$parentWonum = (new Super())->getAutoNumber("WORKORDER", "update_next_value")->autonumber;
				
				// Deskripsi Parent WO (bisa disesuaikan)
				$parentDesc = "Main Project for SO: $sonum - $soDescription";

				(new Set())->useTransactionManager()->MboSetRemote("workorder")
					->setValue("wonum", $parentWonum)
					->setValue("description", $parentDesc)
					->setValue("sonum", $sonum)
					->setValue("enterby", $enterBy)
					->setValue("enterdate", $enterDate)
					->setValue("changeby", $enterBy)
					->setValue("changedate", $enterDate)
					->setValue("status", "waitappr")
					->setValue("statusdate", $enterDate)
					->setValue("orgcode", $orgcode)
					->setValue("sitecode", $sitecode)
					->setValue("customercode", $customercode)
					->add();

				// Insert Status untuk Parent
				(new Set())->useTransactionManager()->MboSetRemote("workorderstatus")
					->setValue("wonum", $parentWonum)
					->setValue("status", "waitappr")
					->setValue("changeby", $enterBy)
					->setValue("changedate", $enterDate)
					->setValue("remarks", "Auto-generated Parent WO for SO $sonum")
					->add();
			}


			// 2. Ambil Data Distribusi yang BELUM punya WO
			// Menggunakan JOIN dan filter IS NULL untuk efisiensi
			$distLines = (new Set())->useTransactionManager()
				->MboSetRemote("solinewo")
				->setRelationship("soline", "solinewo.sonum = soline.sonum AND solinewo.solinenum = soline.solinenum")
				->setRelationship("budget", "solinewo.budgetnum = budget.budgetnum")
				->where("solinewo.sonum = ? AND solinewo.wonum is null ", $sonum)
				->MboRemote("solinewo.sodistlinenum, solinewo.percentage, solinewo.solinenum, solinewo.description, solinewo.location, 
				solinewo.budgetnum, solinewo.budgetlinenum, soline.salesquotenum, soline.salesquotationlinenum, budget.wbscode")
				->getMboSet()
				->fetchData();
			
			if (empty($distLines)) {
				$tm->commit();
				Super::sendJsonResponse("error", "No eligible service distribution lines found to create WO.");
				return;
			}

			$createdWOs = [];
			$woStatusData = [];
			$woSubsData = []; 
			// 3. Loop untuk Membuat WO
			foreach ($distLines as $line) {
				$solinenum = $line['solinenum'];
				$sodistlinenum = $line['sodistlinenum'];
				$description = $line['description'];
				$location = $line['location'];
				$budgetnum = $line['budgetnum'];
				$budgetlinenum = $line['budgetlinenum'];
				$wbscode = $line['wbscode'];
				$distPercent = (float)($line['percentage'] ?? 100); 
				
    			$factor = $distPercent / 100;
				
				// Generate WO Number Unik
				$wonum = (new Super())->getAutoNumber("WORKORDER", "update_next_value")->autonumber;

				// Insert WO
				
				 $woSet = (new Set())->useTransactionManager()->MboSetRemote("workorder")
					->setValue("wonum", $wonum)
					->setValue("description", $description)
					->setValue("sonum", $sonum)
					->setValue("solinenum", $solinenum)
					->setValue("sodistlinenum", $sodistlinenum) // Penting untuk tracking
					->setValue("enterby", $enterBy)
					->setValue("enterdate", $enterDate)
					->setValue("changeby", $enterBy)
					->setValue("changedate", $enterDate)
					->setValue("status", "waitappr")
					->setValue("statusdate", $enterDate)
					->setValue("orgcode", $orgcode)
					->setValue("sitecode", $sitecode)
					->setValue("budgetnum", $budgetnum)
					->setValue("budgetlinenum", $budgetlinenum)
					->setValue("location", $location)
					->setValue("customercode", $customercode)
					->setValue("wbscode", $wbscode);
				
				// JIKA ADA PARENT, SET KOLOM PARENT
				if ($parentWonum) {
					$woSet->setValue("parent", $parentWonum);
					// Siapkan data wosubs
					$woSubsData[] = [
						"wonum" => $parentWonum, // Parent
						"subwonum" => $wonum,    // Child
						"enterby" => $enterBy,
						"enterdate" => $enterDate
					];
				}

				$woSet->add();

				if (!empty($line['salesquotenum']) && !empty($line['salesquotationlinenum'])) {
					ActionSet::copySqPlanToWoPlan($wonum, $sonum, $solinenum, $factor);
				}

				// Siapkan status untuk bulk insert
				$woStatusData[] = [
					"wonum" => $wonum,
					"status" => "waitappr",
					"changeby" => $enterBy,
					"changedate" => $enterDate,
					"remarks" => "Created from SO : $sonum , SO Line : $solinenum, Service Distribution Line : $sodistlinenum"
				];
				
				// Update solinewo dengan WO Number
				(new Set())->useTransactionManager()->MboSetRemote("solinewo")
					->where("sonum = ? AND solinenum = ? AND sodistlinenum = ?", $sonum, $solinenum, $sodistlinenum)
					->setValue("wonum", $wonum)
					->change();
				
				$createdWOs[] = $wonum;
			}

			// 4. Bulk Insert Status
			if (!empty($woStatusData)) {
				(new Set())->useTransactionManager()->MboSetRemote("workorderstatus")->addStream($woStatusData);
			}
			// 5. Bulk Insert WO Subs (BAGIAN BARU)
			if (!empty($woSubsData)) {
				(new Set())->useTransactionManager()->MboSetRemote("wosubs")->addStream($woSubsData);
			}

			//6. Update WBSCode 
			(new Set())->useTransactionManager()->MboSetRemote("workorder")->where("wonum=? ", $parentWonum)
			->setValue("budgetnum", $budgetnum)
			->setValue("budgetlinenum", $budgetlinenum)
			->setValue("wbscode", $wbscode)
			->change();

			$tm->commit();
			
			$msg = "Success. Created " . count($createdWOs) . " Work Order(s).";

			if ($parentWonum) {
				$msg .= " grouped under Parent WO: $parentWonum";
			}

			// Sertakan parentWonum di respons jika ada
			$responseData = ["wonums" => $createdWOs];
			if ($parentWonum) {
				$responseData["parent_wonum"] = $parentWonum;
			}
			Super::sendJsonResponse("success", $msg, $responseData);

		} catch (Exception $e) {
			$tm->rollback();
			Super::sendJsonResponse("error", "Failed to create WO from distribution: " . $e->getMessage());
		}
	}


	public static function copySqPlanToWoPlan($wonum, $sonum, $solinenum, $distributionPercentage = 1.0)
	{
		if ($distributionPercentage <= 0) return;

		$soLineData = (new Set())->useTransactionManager()
			->MboSetRemote("soline")
			->where("sonum = ? AND solinenum = ?", $sonum, $solinenum)
			->MboRemote("salesquotenum, salesquotationlinenum") 
			->getMboSet()
			->fetch();
		
		if (!$soLineData) return;

		$sqNum = $soLineData['salesquotenum'];
		$sqLineNum = $soLineData['salesquotationlinenum'];

		// ====================================================================
		// <<< HELPER CLOSURE YANG DIMODIFIKASI >>>
		// ====================================================================
		$copyTable = function($sourceTable, $targetTable, $columnsMap, $qtyColName, $costColName, $lineNumColName = 'planlinenum') 
					use ($sqNum, $sqLineNum, $wonum, $distributionPercentage) {
			
			$sourceColumns = implode(',', array_keys($columnsMap));
			$sourceData = (new Set())->useTransactionManager()
				->MboSetRemote($sourceTable)
				->where("salesquotenum = ? AND salesquotationlinenum = ?", $sqNum, $sqLineNum)
				->MboRemote($sourceColumns) // Kita ambil kolom sumber apa adanya
				->getMboSet()
				->fetchData();

			if (!empty($sourceData)) {
				$targetData = [];
				$currentLineNum = 10; // Mulai dari 10

				foreach ($sourceData as $row) {
					$newRow = ['wonum' => $wonum]; 
					
					// ISI LINE NUMBER OTOMATIS DI SINI!
					$newRow[$lineNumColName] = $currentLineNum;

					foreach ($columnsMap as $srcCol => $targetCol) {
						$value = $row[$srcCol];

						// Terapkan persentase
						if ($distributionPercentage < 1.0) {
							if ($srcCol === $qtyColName || $srcCol === $costColName) {
								$value = (float)$value * $distributionPercentage;
							}
						}
						
						$newRow[$targetCol] = $value;
					}
					$targetData[] = $newRow;
					
					$currentLineNum += 10; // Tambah 10 untuk baris berikutnya
				}
				
				(new Set())->useTransactionManager()->MboSetRemote($targetTable)->addStream($targetData);
			}
		};

		// 2. Salin MATERIAL
		$copyTable('sqestmaterial', 'woplanmaterial', [
			'itemnum' => 'itemnum', 
			'quantity' => 'quantity', 
			'unitcost' => 'unitcost', 
			'linecost' => 'linecost', 
			'warehouse' => 'warehouse', 
			'conditiontype' => 'conditiontype', 
			'racknum' => 'racknum', 
			'remarks' => 'remarks', 
			'gldebitacct' => 'gldebitacct', 
			'glcreditacct' => 'glcreditacct'
		], 'quantity', 'linecost', 'planlinenum'); 

		// 3. Salin SERVICE
		$copyTable('sqestservice', 'woplanservice', [
			'serviceitemnum' => 'serviceitemnum', 
			'description' => 'description', 
			'quantity' => 'quantity', 
			'orderunit' => 'orderunit', 
			'unitcost' => 'unitcost', 
			'linecost' => 'linecost'
		], 'quantity', 'linecost', 'planlinenum'); 
		
		// 4. Salin TOOL
		$copyTable('sqesttool', 'woplantool', [
			'toolnum' => 'toolnum', 
			'description' => 'description', 
			'quantity' => 'quantity',
			'orderunit' => 'orderunit', 
			'unitcost' => 'unitcost', 
			'linecost' => 'linecost'
		], 'quantity', 'linecost', 'planlinenum'); 

		// 5. Salin LABOR
		$copyTable('sqestlabor', 'woplanlabor', [
			'teamnum' => 'teamnum', 
			'personid' => 'personid',
			'reghours' => 'reghours',
			'workhours' => 'workhours', 
			'unitcost' => 'unitcost',
			'linecost' => 'linecost',
			'linecost' => 'prelinecost',
			'startdate' => 'startdate', 
			'enddate' => 'enddate'
		], 'workhours', 'linecost', 'laborlinenum'); 
	}

	public static function CreateWO()
	{
		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();

		try {
			$sonum = Super::getParam("createwo");
			$reqParentParam  = Super::getParam("reqParent");
			$requiresParent = ($reqParentParam === 'Y' || $reqParentParam === 'true');

			if (empty($sonum)) {
				throw new InvalidArgumentException("SO Number is required.");
			}

			$enterBy = Xbo::getLoginUser();
			$enterDate = Xbo::setEnterDate();

			// 1. Ambil Data Header SO
			$soData = (new Set())->useTransactionManager()
				->MboSetRemote("so")
				->where("sonum = ?", $sonum)
				->MboRemote("orgcode, sitecode, description, customercode") 
				->getMboSet()
				->fetch();

			if (!$soData) {
				$tm->commit(); 
				Super::sendJsonResponse("error", "Sales Order '$sonum' not found.");
				return;
			}

			$orgcode = $soData['orgcode'];
			$sitecode = $soData['sitecode'];
			$soDescription = $soData['description'];
			$customercode = $soData['customercode'];

			// 2. Ambil Semua Line SO yang BELUM Punya WO
			$soLines = (new Set())->useTransactionManager()
				->MboSetRemote("soline")
				->where("sonum = ? AND (wonum IS NULL OR wonum = '')", $sonum)
				->MboRemote("solinenum, description")
				->getMboSet()
				->fetchData();

			if (empty($soLines)) {
				$tm->commit();
				Super::sendJsonResponse("error", "No eligible lines found to create WO (maybe already created).");
				return;
			}

			// Inisialisasi $parentWonum dengan null
			$parentWonum = null;

			if ($requiresParent) {
				// A. Buat WO Parent Baru
				$parentWonum = (new Super())->getAutoNumber("WORKORDER", "update_next_value")->autonumber;
				$parentDesc = "Main Project for SO: $sonum - $soDescription";

				(new Set())->useTransactionManager()->MboSetRemote("workorder")
					->setValue("wonum", $parentWonum)
					->setValue("description", $parentDesc)
					->setValue("sonum", $sonum)
					->setValue("enterby", $enterBy)
					->setValue("enterdate", $enterDate)
					->setValue("changeby", $enterBy)
					->setValue("changedate", $enterDate)
					->setValue("status", "waitappr")
					->setValue("statusdate", $enterDate)
					->setValue("orgcode", $orgcode)
					->setValue("sitecode", $sitecode)
					->setValue("customercode", $customercode)
					->add();

				// Insert Status untuk Parent
				(new Set())->useTransactionManager()->MboSetRemote("workorderstatus")
					->setValue("wonum", $parentWonum)
					->setValue("status", "waitappr")
					->setValue("changeby", $enterBy)
					->setValue("changedate", $enterDate)
					->setValue("remarks", "Auto-generated Parent WO for SO $sonum")
					->add();
			}

			$createdWOs = [];
			$woStatusData = [];
			$woSubsData = []; 

			// 3. Loop untuk Membuat WO Child
			foreach ($soLines as $line) {
				$solinenum = $line['solinenum'];
				$description = $line['description'];
				$wonum = (new Super())->getAutoNumber("WORKORDER", "update_next_value")->autonumber;

				// Insert WO
				$woSet = (new Set())->useTransactionManager()->MboSetRemote("workorder")
					->setValue("wonum", $wonum)
					->setValue("description", $description)
					->setValue("sonum", $sonum)
					->setValue("solinenum", $solinenum)
					->setValue("enterby", $enterBy)
					->setValue("enterdate", $enterDate)
					->setValue("changeby", $enterBy)
					->setValue("changedate", $enterDate)
					->setValue("status", "waitappr")
					->setValue("statusdate", $enterDate)
					->setValue("orgcode", $orgcode)
					->setValue("sitecode", $sitecode)
					->setValue("customercode", $customercode); // Jangan lupa customercode di child juga

				if ($parentWonum) {
					$woSet->setValue("parent", $parentWonum);
					$woSubsData[] = [
						"wonum" => $parentWonum, 
						"subwonum" => $wonum,    
						"enterby" => $enterBy,
						"enterdate" => $enterDate
					];
				}

				$woSet->add();

				ActionSet::copySqPlanToWoPlan($wonum, $sonum, $solinenum);

				// Siapkan status
				$woStatusData[] = [
					"wonum" => $wonum,
					"status" => "waitappr",
					"changeby" => $enterBy,
					"changedate" => $enterDate,
					"remarks" => "Created From SO : $sonum , SO Line : $solinenum"
				];

				// Update soline
				(new Set())->useTransactionManager()->MboSetRemote("soline")
					->where("sonum = ? AND solinenum = ?", $sonum, $solinenum)
					->setValue("wonum", $wonum)
					->change();

				$createdWOs[] = $wonum;
			}

			// 4. Bulk Insert
			if (!empty($woStatusData)) {
				(new Set())->useTransactionManager()->MboSetRemote("workorderstatus")->addStream($woStatusData);
			}
			if (!empty($woSubsData)) {
				(new Set())->useTransactionManager()->MboSetRemote("wosubs")->addStream($woSubsData);
			}

			$tm->commit();
			
			$msg = "Success. Created " . count($createdWOs) . " Work Order(s).";
			if ($parentWonum) {
				$msg .= " grouped under Parent WO: $parentWonum";
			}

			$responseData = ["wonums" => $createdWOs];
			if ($parentWonum) {
				$responseData["parent_wonum"] = $parentWonum;
			}
			Super::sendJsonResponse("success", $msg, $responseData);

		} catch (Exception $e) {
			$tm->rollback();
			Super::sendJsonResponse("error", "Failed to create WO: " . $e->getMessage());
		}
	}

	public static function GetCustomerGLAccount(){
		//UNTUK JASA
		$customercode = $_POST["getcustglaccount"];
		$paymenttype = $_POST["paymenttype"];
		$orgcode = $_POST["orgcode"];
		$sitecode = $_POST["sitecode"];
		$set = new Set();
		
		$glcreditacct = null;
		$gldebitacct = null;
		
		if($paymenttype == "kredit"){
			$gldebitacct = Xbo::getCustomerAPAcct($customercode, $orgcode, $sitecode);
		}else{
			if($paymenttype == "cashonbank"){
				$gldebitacct = Xbo::getCashOnBankAcct($orgcode, $sitecode);
			}else{
				if($paymenttype == "cashonhand"){
					$gldebitacct = Xbo::getCashOnHandAcct($orgcode, $sitecode);
				}
			}
		}
		$glcreditacct = Xbo::getCustomerRBNIAcct($customercode, $orgcode, $sitecode);
		Set::throws(["gldebitacct" =>$gldebitacct , "glcreditacct"=>$glcreditacct]);
	}
	public static function UpdateSOLineGL(){
		$sonum = $_POST["updatesolinegl"];
		$paymenttype = $_POST["value"];
		$orgcode = $_POST["orgcode"];
		$sitecode = $_POST["sitecode"];
		$customercode = $_POST["sitecode"];
		
		$set = new Set();
		
		$solineSet = $set->useTransactionManager()->MboSetRemote("soline")->setWhere("sonum='$sonum'")->MboRemote("linetype")->MboRemote("warehouse")->MboRemote("solineid")->getMboSet();
		$slcount = $solineSet->counts();
		
		
		
		for($i=0; $i<$slcount; $i++){
			$soline = $solineSet->getMbo();
			$warehouse = $soline->getString("warehouse");
			$solineid = $soline->getString("solineid");
			$linetype = $soline->getString("linetype");
			
			if($linetype == "item"){
				$glcreditacct = Xbo::getControlAcct($warehouse, $orgcode, $sitecode);
				if($paymenttype == "cashonbank"){
						$gldebitacct = Xbo::getCashOnBankAcct($orgcode, $sitecode); // nambah kas BANK perusahaan
					}else{
						if($paymenttype == "cashonhand"){
							$gldebitacct = Xbo::getCashOnHandAcct($orgcode, $sitecode); // nambah kas on HAND perusahaan
						}else{
							if($paymenttype == "kredit"){
								$glcreditacct = Xbo::getCustomerAPAcct($customercode, $orgcode, $sitecode); 
								$gldebitacct = Xbo::getCustomerRBNIAcct($customercode, $orgcode, $sitecode); 
							}
						}
					}
			}else{
				if($linetype == "services"){
					$glcreditacct = Xbo::getCustomerRBNIAcct($customercode, $orgcode, $sitecode);
					if($paymenttype == "cashonbank"){
						$gldebitacct = Xbo::getCashOnBankAcct($orgcode, $sitecode); // nambah kas BANK perusahaan
					}else{
						if($paymenttype == "cashonhand"){
							$gldebitacct = Xbo::getCashOnHandAcct($orgcode, $sitecode); // nambah kas on HAND perusahaan
						}else{
							if($paymenttype == "kredit"){
								$gldebitacct  = Xbo::getCustomerAPAcct($customercode, $orgcode, $sitecode); 
								$glcreditacct = Xbo::getCustomerRBNIAcct($customercode, $orgcode, $sitecode); 
							}
						}
					}
				}
			}
			
			$set2= new Set();
			$set2->useTransactionManager()->MboSetRemote("soline")->setWhere("solineid='$solineid'")
			->setValue("glcreditacct", $glcreditacct)
			->setValue("gldebitacct", $gldebitacct)
			->change();
			
		}
		
		
		

	}
	public static function PostExpense(){
		$sonum = $_POST["postexpense"];
		
		$set = new Set();
		$sotrackingSet = $set ->useTransactionManager()->MboSetRemote("sotracking")->setWhere("sonum='$sonum'")->getMboSet();
		echo $sotrackingSet->sql;
		$stcount = $sotrackingSet->counts();
		for($i=0; $i<$stcount; $i++){
			$st = $sotrackingSet->getMbo();
			$tracklinenum = $st->getString("tracklinenum");
			$customercode = $st->getString("customercode");
			$tracktype = $st->getString("tracktype");
			$description = $st->getString("description");
			$followupdate = $st->getString("followupdate");
			$cost = $st->getString("cost");
			$glaccount = $st->getString("glaccount");
			$charged = $st->getString("charged");
			$posted = $st->getString("posted");
			$taxcost = $st->getString("taxcost");
			if($posted != "Y"){
				$set2 = new Set();
				
			}
		}
	}
	public static function CalculateUnitPrice()
	{
		$itemnum = $_POST["itemnum"];
		$warehouse = $_POST["warehouse"];
		$conditiontype =  $_POST["conditiontype"];
		$pricecode = $_POST["unitpricecode"];
		$taxcode = $_POST["taxcode"];
		$lineprice = $_POST["lineprice"];
		$sellqty = $_POST["sellqty"];
		if (empty($conditiontype)) $condval = " and (conditiontype ='' or conditiontype is null) ";
		else $condval = " and conditiontype='$conditiontype'";
		$set = new Set();
		
		$invPriceSet = $set->useTransactionManager()->MboSetRemote("invprice")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' $condval and pricecode='$pricecode'")->MboRemote("unitprice")->getMboSet();
		$invPrice = $invPriceSet->getMbo();
		$unitprice = $invPrice->getString("unitprice");
		
		$lineprice = $unitprice*$sellqty;
		
		$set = new Set();
		$taxSet = $set->useTransactionManager()->MboSetRemote("tax")->setWhere("taxcode='$taxcode'")->MboRemote("value")->getMboSet();
		$tax = $taxSet->getMbo();
		$taxvalue = $tax->getString("value");
		if(empty($taxvalue)) {
			$taxvalue = 0;
			$taxprice = 0;
		}else{
			$taxprice = ($taxvalue*$lineprice)/100;
		}
		
		Set::throws(["unitprice"=>$unitprice, "lineprice"=>$lineprice, "taxprice"=>$taxprice]);
	
	}
	
	public static function SellQty()

	{
		
		$itemnum = $_POST['itemnum'];
		$warehouse = $_POST['warehouse'];
		$conditiontype = $_POST['conditiontype'];
		$sellqty = $_POST['sellqty'];
		$racknum =$_POST['racknum'];
		$unitprice =$_POST['unitprice'];
		
		if(empty($conditiontype)) {
			$condval = "and (conditiontype is null or conditiontype = '')";
		}else{
			$condval = "and conditiontype='$conditiontype'";
		}
		
		if(empty($racknum)) {
			$rackval = "and (racknum is null or racknum = '')";
		}else{
			$rackval = "and racknum='$racknum'";
		}
		
		$set = new Set();
		$curbal = $set->useTransactionManager()->MboSetRemote("invbalance")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' $condval $rackval")->MboRemote("curbal")->getMboSet()->getMbo()->getString("curbal");
		if(empty($curbal)){
			Set::throws(["msg" => "allowedToSell", "curbal" => $curbal]);
		}else{
			/*
			if ($curbal < $sellqty) {
				Set::throws(["msg" => "curbalNotEnoughToSell", "curbal" => $curbal]);
			}
			else {
				*/
				Set::throws(["msg" => "allowedToSell", "curbal" => $curbal]);
			//}
		}
		
	}
	
	public static function setSOServsaleSentStatus($sonum, $solinenum)

	{
		$set = new Set();
		$sumservsale = $set->useTransactionManager()->MboSetRemote("servsaletrans")->setWhere("sonum='$sonum' and solinenum='$solinenum'")->MboRemote("sum(quantity) as sumqty")->getMboSet()->getMbo()->getString("sumqty");
		$sellqty = $set->useTransactionManager()->MboSetRemote("soline")->setWhere("sonum='$sonum' and solinenum='$solinenum'")->MboRemote("sellqty")->getMboSet()->getMbo()->getString("sellqty");
		if ($sumservsale == 0 || $sumservsale === null) $sentstatus = 'NONE';
		if ($sumservsale > 0 || ($sumservsale < $sellqty && $sumservsale > 0)) $sentstatus = 'PARTIAL';
		if ($sumservsale == $sellqty) $sentstatus = 'COMPLETE';
		$set->useTransactionManager()->MboSetRemote("soline")->setWhere(" sonum='$sonum' and solinenum='$solinenum'")->setValue("sentstatus", $sentstatus)->change();
	}
	public static function setSOMatsaleSentStatus($sonum, $solinenum)

	{
		$set = new Set();
		$sumMatSale = $set->useTransactionManager()->MboSetRemote("matsaletrans")->setWhere("sonum='$sonum' and solinenum='$solinenum'")->MboRemote("sum(quantity) as sumqty")->getMboSet()->getMbo()->getString("sumqty");
		if(empty($sumMatSale)) $sumMatSale =0;
		else $sumMatSale = $sumMatSale*-1;
		
		
		if(empty($sumMatSale)) $sumMatSale = 0;
		$sellqty = $set->useTransactionManager()->MboSetRemote("soline")->setWhere("sonum='$sonum' and solinenum='$solinenum'")->MboRemote("sellqty")->getMboSet()->getMbo()->getString("sellqty");
		if ($sumMatSale == 0 || $sumMatSale === null) $sentstatus = 'NONE';
		if ($sumMatSale > 0 || ($sumMatSale < $sellqty && $sumMatSale > 0)) $sentstatus = 'PARTIAL';
		if ($sumMatSale == $sellqty) $sentstatus = 'COMPLETE';
		else $sentstatus = "PARTIAL";
		$set->useTransactionManager()->MboSetRemote("soline")->setWhere(" sonum='$sonum' and solinenum='$solinenum'")->setValue("sentstatus", $sentstatus)->change();
	}
	public static function setSOSentStatus($sonum, $solinenum, $linetype)

	{
		if ($linetype == "item") ActionSet::setSOMatsaleSentStatus($sonum, $solinenum);
		if ($linetype == "services") ActionSet::setSOServsaleSentStatus($sonum, $solinenum);
		$set = new Set();
		$solineSet = $set->useTransactionManager()->MboSetRemote("soline")->setWhere("sonum='$sonum'")->MboRemote("sentstatus")->getMboSet("remove_duplicate");
		$soline = $solineSet->getMbo();
		$soreceipt = "NONE";
		$numrows = $solineSet->counts();
		
		
		if ($numrows > 1) $soreceipt = 'PARTIAL';
		if ($numrows < 1) $soreceipt = 'NONE';
		if ($numrows == 1) $soreceipt = $soline->getString("sentstatus");
		
		$set = new Set();
		$set->useTransactionManager()->MboSetRemote("so")->setWhere("sonum='$sonum'")->setValue("sentstatus", $soreceipt)->change();
		
	}
	
	public static function InsertOrUpdateINVFIFOLIFOCOST($ob){
		$itemnum = $ob->itemnum;
		$towarehouse = $ob->towarehouse;
		$toracknum = $ob->toracknum;
		$conditiontype = $ob->conditiontype;
		$enterby = $ob->enterby;
		$enterdate = $ob->enterdate;
		$orgcode = $ob->orgcode;
		$sitecode = $ob->sitecode;
		$quantity = $ob->quantity;
		$costdate = $ob->costdate;
		$financialperiod = $ob->financialperiod;
		$conditionrate = $ob->conditionrate;
		$unitcost = $ob->unitcost;
		$batchnum = $ob->batchnum;
		$expdate = $ob->expdate;
		$retqty = $ob->retqty;
		
		
		if (empty($conditiontype)) $condval = " and (conditiontype is null or conditiontype='')";
		else $condval = " and conditiontype='$conditiontype'";
			
		if(empty($toracknum)) $rackval = " and (racknum is null or racknum='')"; 
		else $rackval = " and racknum='$toracknum'";
		
		$set = new Set();
		$wccostdate = Super::whereDate("costdate", $costdate);
		
		$wcinv = "itemnum='$itemnum' and warehouse='$towarehouse'";
		$wcfifolifo = "$wcinv $condval $wccostdate $rackval";
		$wcfifolifopovendor = "$wcinv $condval $wccostdate ";
		$iflSet = $set->useTransactionManager()->MboSetRemote("invfifolifocost")->setWhere($wcfifolifo)->MboRemote("quantity")->getMboSet();
		
		$iflcnt = $iflSet->counts();
		if($iflcnt > 0){
			$ifl = $iflSet->getMbo();
			$oldflqty = $ifl->getString("quantity");
			$newflqty = $oldflqty+$retqty;
			$set4 = new Set();
			$set4->useTransactionManager()->MboSetRemote("invfifolifocost")->setWhere($wcfifolifo)->setValue("quantity", $newflqty)->change();
		}else{
			
			$pv = new Set();
			$povendorSet = $pv->useTransactionManager()->MboSetRemote("invfifolifocost")->setWhere($wcfifolifopovendor)->MboRemote("vendorcode")->MboRemote("ponum")->getMboSet();
			$povendor = $povendorSet->getMbo();
			$vendorcode = $povendorSet->getString("vendorcode");
			$ponum = $povendorSet->getString("ponum");
			
			$set4 = new Set();
			if($quantity < 0 ) $quantity = $quantity*-1;
			$quantity = $retqty;
			$set4->useTransactionManager()->MboSetRemote("invfifolifocost")->setValue("itemnum", $itemnum)->setValue("conditiontype", $conditiontype)->setValue("costdate", $costdate)->setValue("expdate", $expdate)
			->setValue("batchnum", $batchnum)->setValue("financialperiod", $financialperiod)->setValue("unitcost", $unitcost)->setValue("quantity", $quantity)
			->setValue("conditionrate", $conditionrate)->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)->setValue("warehouse", $towarehouse)->setValue("racknum", $toracknum)
			->setValue("ponum", $ponum)->setValue("vendorcode", $vendorcode)->add();
		}
	}

	public static function ReturnTransaction($value = null)
	{
		
		$table = $value["sourcetable"];
		$sonum = $value["sonum"];
		$solinenum = $value["solinenum"];
		$quantity = $value["restqty"];
		$actualdate = $value["actualdate"];
		$remarks = $value["remarks"];
		
		$set = new Set();
		$soSet = $set->useTransactionManager()->MboSetRemote("so")->setWhere("sonum='$sonum'")->MboRemote("customercode")->MboRemote("orgcode")->MboRemote("sitecode")->MboRemote("discountpercentage")->getMboSet()->getMbo();
		$customercode = $soSet->getString("customercode");
		$orgcode = $soSet->getString("orgcode");
		$sitecode = $soSet->getString("sitecode");
		$discountpercentage = $soSet->getString("discountpercentage");
		
		
		$solineSet = $set->useTransactionManager()->MboSetRemote("soline")->setWhere("sonum='$sonum' and solinenum='$solinenum'")->MboRemote("linetype")->MboRemote("itemnum")->MboRemote("description")
		->MboRemote("warehouse")->MboRemote("racknum")->MboRemote("conditiontype")->MboRemote("sellqty")->MboRemote("costtype")->MboRemote("unitcost")->MboRemote("pricecode")
		->MboRemote("unitprice")->MboRemote("unitpricediscount")->MboRemote("gldebitacct")->MboRemote("glcreditacct")->MboRemote("taxcode")->MboRemote("taxprice")
		->getMboSet()->getMbo();
		
		$linetype = $solineSet->getString("linetype");
		$itemnum = $solineSet->getString("itemnum");
		$description = $solineSet->getString("description");
		$pricecode = $solineSet->getString("pricecode");
		$unitprice = $solineSet->getString("unitprice");
		$unitpricediscount = $solineSet->getString("unitpricediscount");
		$costtype = $solineSet->getString("costtype");
		$conditiontype = $solineSet->getString("conditiontype");
		$fromwarehouse = $solineSet->getString("warehouse");
		
		if(empty($costtype)) $costtype = Xbo::getCostType($orgcode);
		$sellQty = $solineSet->getString("sellqty");
		
		$taxcode = $solineSet->getString("taxcode");
		$taxprice = $solineSet->getString("taxprice");
		
		if(empty($taxprice)) {
			$taxprice = 0;
			$unittaxprice = 0;
		}else{
			$unittaxprice = $taxprice/$sellQty;
		}
		
		$gldebitacct = $solineSet->getString("glcreditacct");
		$glcreditacct = $solineSet->getString("gldebitacct");
		$retvalObj = new stdClass();
		
		if ($linetype == "services") {
			$TotalSentQty = $set->useTransactionManager()->MboSetRemote("servsaletrans")->setWhere("linetype='services' and sonum='$sonum' and solinenum='$solinenum'")
			->MboRemote("sum(quantity) as TotalSentQty")->getMboSet()->getMbo()->getString("TotalSentQty") * -1;
			if ($quantity >= 0) {
				$retvalObj->msg = "cannotReceiptService";
				return $retvalObj;
			}
			else {
				$sellQtyMinus = $sellQty * -1;
				
				if ($quantity < $sellQtyMinus) {
					$retvalObj->msg = "cannotReturnMoreThanQtySend";
					return $retvalObj;
				}
				else {
					if ($TotalSentQty <= $quantity) {
						$unitprofit = 0;
						$lineprice = $quantity * $unitprice;
						$linepricediscount = $quantity * $unitpricediscount;
						$lineprofit = 0;
						$taxprice = $unittaxprice*$quantity;
						
						$enterdate = Xbo::setEnterDate();
						$transdate = $enterdate;
						$transtype = "RETURN";
						$enterby = Xbo::getLoginUser();
						$enterdate = Xbo::setEnterDate();
						$finparts = explode("-", $enterdate);
						$financialperiod = $finparts[0] . $finparts[1];
						$set->useTransactionManager()->MboSetRemote("servsaletrans")->setValue("sonum", $sonum)->setValue("solinenum", $solinenum)->setValue("linetype", $linetype)
						->setValue("description", $description)->setValue("quantity", $quantity)->setValue("unitprice", $unitprice)->setValue("lineprice", $lineprice)
						->setValue("unitpricediscount", $unitpricediscount)->setValue("linepricediscount", $linepricediscount)
						->setValue("lineprofit", $lineprofit)->setValue("unitprofit", $unitprofit)->setValue("gldebitacct", $gldebitacct)->setValue("glcreditacct", $glcreditacct)
						->setValue("transdate", $transdate)->setValue("transtype", $transtype)->setValue("customercode", $customercode)->setValue("enterby", $enterby)
						->setValue("enterdate", $enterdate)->setValue("changeby", $enterby)->setValue("changedate", $enterdate)->setValue("orgcode", $orgcode)
						->setValue("sitecode", $sitecode)->setValue("financialperiod", $financialperiod)->setValue("taxcode", $taxcode)
						->setValue("taxprice", $taxprice)->setValue("actualdate", $actualdate)->setValue("remarks", $remarks)
						->add();
						
						$name = Xbo::getCustomerName($customercode);
						$activity = "refund the service cost of <font color=blue><b>$description</b></font> on Sales Order: <font color=blue><b>$sonum </b>
						</font> for customer <font color=blue><b>$name - $customercode </b></font>";
						Xbo::setActivity($activity);
						ActionSet::setSOSentStatus($sonum, $solinenum, $linetype);
						$retvalObj->msg = "returned";
						return $retvalObj;
					}
					else {
						
						$retvalObj->msg="cannotReturnMoreThanQtySend";
						return $retvalObj;
					}
				}
			}
		}
		if ($linetype == "item") {
			$costdate = $value["costdate"];
			$wheredate = Super::whereDate("costdate", $costdate);
			if($costtype == "AVG"){
				$unitcost = Xbo::getUnitCost($orgcode, $itemnum, $fromwarehouse, $conditiontype);
				$unitprofit = $unitprice-$unitcost;
			}
			
			$ms = new Set();
			$matsaletransSet = $ms->useTransactionManager()->MboSetRemote("matsaletrans")->setWhere("linetype='item' and sonum='$sonum' and solinenum='$solinenum' $wheredate")
			->MboRemote("sum(quantity) as TotalSentQty")->MboRemote("batchnum")->MboRemote("unitcost")->MboRemote("issuetype")->MboRemote("expdate")
			->MboRemote("primarydiscount")->MboRemote("secondarydiscount")->MboRemote("discountprice")->MboRemote("paymenttype")->MboRemote("salesman")
			->MboRemote("costdate")
			->getMboSet();
			
			$matsaletrans = $matsaletransSet->getMbo();
			$TotalSentQty = $matsaletrans->getString("TotalSentQty");
			$unitcostfifolifo = $matsaletrans->getString("unitcost");
			$batchnum = $matsaletrans->getString("batchnum");
			$expdate = $matsaletrans->getString("expdate");
			$issuetype = $matsaletrans->getString("issuetype");
			$primarydiscount = $matsaletrans->getString("primarydiscount");
			$secondarydiscount = $matsaletrans->getString("secondarydiscount");
			$discountprice = $matsaletrans->getString("discountprice");
			$paymenttype = $matsaletrans->getString("paymenttype");
			$salesman = $matsaletrans->getString("salesman");
			if(empty($costdate)){
				$costdate = $matsaletrans->getString("costdate");
			}
			if($discountprice == null) $discountprice =0;
			$discountpricemin = $discountprice*-1;
			if(empty($TotalSentQty)) $TotalSentQty = 0;
			if($TotalSentQty<0) $TotalSentQty = $TotalSentQty* -1;
			$canReceipt = $TotalSentQty - $quantity;
			
			if ($canReceipt < 0) {
				$retvalObj->msg = "cannotReturnMoreThanQtySend";
				return $retvalObj;
			}
			if ($quantity <= 0) {
				$retvalObj->msg="cannotReturnLessThanZero";
				return $retvalObj;
			}
			if ($quantity > 0 && $quantity <= $TotalSentQty) {
				$toWH = $value["warehouse"];
				$toRc = $value["racknum"];
				$quantity = $quantity;
				$lineprice = ($quantity * $unitprice);
				$linepricediscountmin = ($quantity * $unitpricediscount);
				if(empty($costtype)) $costtype = Xbo::getCostType($orgcode);
				
				if($issuetype == "FIFO" || $issuetype == "LIFO"){
					$unitcost = $unitcostfifolifo;
					$costtype = $issuetype;
				}else{
					$unitcost = Xbo::getUnitCost($orgcode, $itemnum, $fromwarehouse, $conditiontype);
				}
				
				$unitprofit = $unitprice-$unitcost;
				$unitprofitdiscount = $unitpricediscount-$unitcost;
				$lineprofitdiscount = $unitprofitdiscount*$quantity;
				$lineprofitdiscountmin = $lineprofitdiscount*-1;
				$lineprofit = $quantity * $unitprofit;
				$lineprofitmin = $lineprofit*-1;
				$towarehouse = $toWH;
				$torack = $toRc;
				$linecost = $unitcost*$quantity;
				$taxprice = $unittaxprice*$quantity;
				$unitcommission = Xbo::GetUnitCommission($itemnum, $fromwarehouse, $pricecode, $conditiontype);
				$linecommission = $unitcommission*$quantity;
				$linecommissionmin = $linecommission*-1;
				
				if(empty($towarehouse) || $towarehouse == "''"){
					$retvalObj->msg="warehouseisnull";
					return $retvalObj;
				}else{
					$linepricemin = $lineprice*-1;		
					$linepricediscountmin =  $linepricediscountmin*-1;	
					$enterdate = Xbo::setEnterDate();
					$transdate = $enterdate;
					$transtype = "RETURN";
					$enterby = Xbo::getLoginUser();
					$enterdate = Xbo::setEnterDate();
					$financialperiod = Xbo::getFinancialPeriod();
					$taxprice = $taxprice*-1;
					
					
					$set->useTransactionManager()->MboSetRemote("matsaletrans")->setValue("sonum", $sonum)->setValue("solinenum", $solinenum)->setValue("linetype", $linetype)
					->setValue("itemnum", $itemnum)->setValue("description", $description)->setValue("pricecode", $pricecode)->setValue("quantity", $quantity)
					->setValue("unitprice", $unitprice)->setValue("lineprice", $linepricemin)->setValue("unitpricediscount", $unitpricediscount)->setValue("linepricediscount", $linepricediscountmin)
					->setValue("costtype", $costtype)->setValue("lineprofit", $lineprofitmin)->setValue("unitprofit", $unitprofit)
					->setValue("unitprofitdiscount", $unitprofitdiscount)->setValue("lineprofitdiscount", $lineprofitdiscountmin)
					->setValue("conditiontype", $conditiontype)->setValue("towarehouse", $towarehouse)->setValue("torack", $torack)
					->setValue("gldebitacct", $gldebitacct)->setValue("glcreditacct", $glcreditacct)->setValue("transdate", $transdate)->setValue("transtype", $transtype)
					->setValue("customercode", $customercode)->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)->setValue("orgcode", $orgcode)
					->setValue("sitecode", $sitecode)->setValue("financialperiod", $financialperiod)->setValue("costdate", $costdate)->setValue("unitcost", $unitcost)
					->setValue("issuetype", $issuetype)->setValue("linecost", $linecost)->setValue("batchnum", $batchnum)
					->setValue("primarydiscount", $primarydiscount)->setValue("secondarydiscount", $secondarydiscount)->setValue("discountprice", $discountpricemin)
					->setValue("paymenttype", $paymenttype)->setValue("salesman", $salesman)->setValue("taxcode", $taxcode)->setValue("taxprice", $taxprice)
					->setValue("actualdate", $actualdate)->setValue("remarks", $remarks)
					->setValue("unitcommission", $unitcommission)->setValue("linecommission", $linecommissionmin)
					->add();
					$condval = "";
					$rackval = "";
					if (empty($conditiontype)) {
						$condval = " and (conditiontype is null or conditiontype='')";
					}
					else {
						$condval = " and conditiontype='$conditiontype'";
					}
				
					if(empty($torack)){
						$rackval = " and (racknum is null or racknum='')";
					}else{
						$rackval = " and racknum='$torack'";
					}
					
					
					$ob = new stdClass();
					$ob->itemnum = $itemnum;
					$ob->towarehouse = $towarehouse;
					$ob->toracknum = $torack;
					$ob->conditiontype = $conditiontype;
					$ob->enterby = $enterby;
					$ob->enterdate = $enterdate;
					$ob->orgcode = $orgcode;
					$ob->sitecode = $sitecode;
					$ob->quantity = $quantity;
					$ob->costdate = $costdate;
					$ob->financialperiod = $financialperiod;
					$ob->conditionrate = Xbo::getConditionRate($conditiontype, "itemcondition", $itemnum, $towarehouse);
					$ob->unitcost = $unitcost;
					$ob->batchnum = $batchnum;
					$ob->expdate = $expdate;
					$ob->retqty = $quantity;
					$ob->unitcommission = $unitcommission;
					if(!empty($costdate)) ActionSet::InsertOrUpdateINVFIFOLIFOCOST($ob);
					
					$si = new Set();
					$invbalSet = $si->useTransactionManager()->MboSetRemote("invbalance")->setWhere("itemnum='$itemnum' and warehouse='$towarehouse' $rackval $condval")
					->MboRemote("curbal")->getMboSet();
					
					$ib = $invbalSet->getMbo();
					$oldcurbal = $ib->getString("curbal");
					if(empty($oldcurbal)) $oldcurbal=0;
					$newcurbal = $oldcurbal + $quantity;
					if (!empty($oldcurbal)) {
						$set->useTransactionManager()->MboSetRemote("invbalance")->setWhere("itemnum='$itemnum' and warehouse='$towarehouse' $rackval $condval")
						->setValue("curbal", $newcurbal)->setValue("oldcurbal", $oldcurbal)->change();
					}
					else { 
						$changeby = $enterby;
						$changedate = $enterdate;
						$oldcurbal = 0;
						$newcurbal = $quantity;
						
						$set->useTransactionManager()->MboSetRemote("invbalance")->setValue("itemnum", $itemnum)->setValue("conditiontype", $conditiontype)->setValue("warehouse", $towarehouse)
						->setValue("racknum", $torack)->setValue("curbal", $newcurbal)->setValue("oldcurbal", $oldcurbal)->setValue("enterby", $enterby)
						->setValue("enterdate", $enterdate)->setValue("changedate", $changedate)->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)->add();
												
						$invItemnum = $set->useTransactionManager()->MboSetRemote("inventory")->setWhere("itemnum='$itemnum' and warehouse = '$towarehouse'")->MboRemote("itemnum")
						->getMboSet()->getMbo()->getString("itemnum");
						
						if (empty($invItemnum)) {
							$set->useTransactionManager()->MboSetRemote("inventory")->setValue("itemnum", $itemnum)->setValue("warehouse", $towarehouse)->setValue("racknum", $torack)
							->setValue("reorderqty", 0)->setValue("leadtime", 0)->setValue("eoqqty", 1)->setValue("safetystock", 0)->setValue("reorder", "Y")->setValue("changeby", $enterby)
							->setValue("changedate", $enterdate)->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)->add();
							
							$invCostSet = $set->useTransactionManager()->MboSetRemote("invcost")->setWhere("itemnum='$itemnum' and conditiontype='$conditiontype' and warehouse='$fromwarehouse'")
							->MboRemote("stdcost")->MboRemote("avgcost")->MboRemote("oldcost")->MboRemote("lastcost")->getMboSet()->getMbo();
							$stdcost = $invCostSet->getString("stdcost");
							$avgcost = $invCostSet->getString("avgcost");
							$oldcost = $invCostSet->getString("oldcost");
							$lastcost = $invCostSet->getString("lastcost");
							
							$glaccount = Xbo::getGLAccount($itemnum);
							$shrinkageacc = Xbo::getShrinkageAcct($towarehouse, $orgcode, $sitecode);
							$invcostadjacc = Xbo::getInvCostAdjAcct($towarehouse, $orgcode, $sitecode);
							$conditionrate = Xbo::getConditionRate($conditiontype, "itemcondition", $itemnum, $towarehouse);
							$set->useTransactionManager()->MboSetRemote("invcost")->setValue("itemnum", $itemnum)->setValue("warehouse", $towarehouse)
							->setValue("conditiontype", $conditiontype)->setValue("conditionrate", $conditionrate)
							->setValue("stdcost", $stdcost)->setValue("avgcost", $avgcost)->setValue("oldcost", $oldcost)
							->setValue("lastcost", $lastcost)->setValue("glaccount", $glaccount)->setValue("shrinkageacc", $shrinkageacc)
							->setValue("invcostadjacc", $invcostadjacc)->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)->add();
						}
					}
				
					XboSet::AdjustConversionStock($itemnum, $towarehouse);
					ActionSet::setSOSentStatus($sonum, $solinenum, $linetype);
					
					$retvalObj->msg="returned";
					return $retvalObj;
				}
			}
		}
		
	}

	
	public static function ReceiptTransaction($value = null, $other = null)

	{
		$set = new Set();
		$retvalObj = new stdClass();
		$table = $value["sourcetable"];
		$parts = explode("`", $value["selectedrows"]);
		$soldQty = $value["sellqty"];
		$remarks = $value["remarks"];
		$actualdate = $value["actualdate"];
		$tableid = $parts[1];
		
		if($other != null){
			$paymenttype = $other->paymenttype;
			$salesman = $other->salesman;
		}
		
		
		
		$soldQty2 = $soldQty;
		$solineSet = $set->useTransactionManager()->MboSetRemote("soline")->setRelationship("so", "soline.sonum = so.sonum")
		->setWhere("solineid='$tableid'")->MboRemote("itemnum")
		->MboRemote("sellqty")->MboRemote("racknum")->MboRemote("orgcode")->MboRemote("sitecode")->MboRemote("linetype")->MboRemote("costtype")
		->MboRemote("orderunit")->MboRemote("warehouse")->MboRemote("solinenum")->MboRemote("unitprice")->MboRemote("lineprice")->MboRemote("unitpricediscount")
		->MboRemote("pricecode")->MboRemote("unitprofit")->MboRemote("lineprofit")->MboRemote("gldebitacct")->MboRemote("soline.sonum")
		->MboRemote("glcreditacct")->MboRemote("conditiontype")->MboRemote("so.customercode")->MboRemote("soline.description")->MboRemote("so.employeesid")
		->MboRemote("soline.taxcode")->MboRemote("soline.taxprice")
		->getMboSet()->getMbo();
		
		$sonum = $solineSet->getString("sonum");
		$linetype = $solineSet->getString("linetype");
		$solinenum = $solineSet->getString("solinenum");
		$description = $solineSet->getString("description");
		$unitprice = $solineSet->getString("unitprice");
		$unitpricediscount = $solineSet->getString("unitpricediscount");
		$quantity = $solineSet->getString("sellqty");
		$customercode = $solineSet->getString("customercode");
		$sellqty = $quantity;
		$lineprice = $soldQty * $unitprice;
		$linepricediscount = $soldQty * $unitpricediscount;
		$gldebitacct = $solineSet->getString("gldebitacct");
		$glcreditacct = $solineSet->getString("glcreditacct");
		$unitprofit = $solineSet->getString("unitprofit");
		$itemnum = $solineSet->getString("itemnum");
		$saleunit = $solineSet->getString("orderunit");
		$quantityMinus = $quantity * -1;
		$conditiontype = $solineSet->getString("conditiontype");
		$warehouse = $solineSet->getString("warehouse");
		$racknum = $solineSet->getString("racknum");
		$pricecode = $solineSet->getString("pricecode");
		$orgcode = $solineSet->getString("orgcode");
		$costtype = $solineSet->getString("costtype");
		$salesman = $solineSet->getString("employeesid");
		$taxcode = $solineSet->getString("taxcode");
		$taxprice = $solineSet->getString("taxprice");
		
		$unitcommission = Xbo::GetUnitCommission($itemnum, $warehouse, $pricecode, $conditiontype);
		
		
		if(empty($taxprice)) {
			$taxprice = 0;
			$unittaxprice = 0;
		}else{
			$unittaxprice = $taxprice/$sellqty;
		}
		
		
		if(empty($costtype)) $costtype = Xbo::getCostType($orgcode);
		
		if($linetype == "item"){
			if($costtype == "AVG") {
				$unitcost = Xbo::getUnitCost($orgcode, $itemnum, $warehouse, $conditiontype);
				$unitprofit = $unitprice-$unitcost;
				$lineprofit = $quantity * $unitprofit;
				$unitprofitdiscount = $unitpricediscount-$unitcost;
				$lineprofitdiscount = $unitprofitdiscount*$quantity;
				$linecost = $quantity*$unitcost;
			}
		}else{
			if($linetype == "services") {
				$unitcost =0;
				$unitprofit = 0;
				$lineprofit = 0;
				$unitprofitdiscount = 0;
				$lineprofitdiscount = 0;
				$linecost = 0;
			}
		}
		
		
		$sitecode = $solineSet->getString("sitecode");
		$enterby = Xbo::getLoginUser();
		
		$enterdate = Xbo::setEnterDate();
		$transdate = $enterdate;
		$financialperiod = Xbo::getFinancialPeriod();
		
		if ($linetype == "services"){
			$totalLineQty = $set->useTransactionManager()->MboSetRemote("servsaletrans")->setWhere("linetype='services' and sonum='$sonum' and solinenum='" . $solinenum . "'")->MboRemote("sum(quantity) as restQty")->getMboSet()->getMbo()->getString("restQty");
			if($totalLineQty == null) $totalLineQty=0;
			$restQty = $sellqty + ($totalLineQty * -1);
		
			if ($soldQty2 > $quantity) {
				$retvalObj->msg = "cannotSellMoreThanLineQty";
				$retvalObj->sellqty = $quantity;
				$retvalObj->saleunit = $saleunit;
				return $retvalObj;
			}else{
				if ($soldQty2 <= $quantity) {
					if ($soldQty2 < 0 || $soldQty2 == 0) {
						$retvalObj->msg = "cannotSendZeroQuantity";
						return $retvalObj;
					}else{
						if ($soldQty2 > 0) {
							if ($soldQty2 > $restQty) {
								$retvalObj->msg = "cannotSellMoreThanLineQty";
								$retvalObj->sellqty = $quantity;
								$retvalObj->saleunit = $saleunit;
								return $retvalObj;
							}
							else {
								$transtype = "SALE";
								$taxprice = $soldQty2*$unittaxprice;
								$set->useTransactionManager()->MboSetRemote("servsaletrans")->setValue("sonum", $sonum)->setValue("solinenum", $solinenum)->setValue("linetype", $linetype)
								->setValue("description", $description)->setValue("quantity", $soldQty2)->setValue("unitprice", $unitprice)->setValue("lineprice", $lineprice)
								->setValue("unitpricediscount", $unitpricediscount)->setValue("linepricediscount", $linepricediscount)
								->setValue("lineprofit", $lineprofit)->setValue("unitprofit", $unitprofit)->setValue("gldebitacct", $gldebitacct)->setValue("glcreditacct", $glcreditacct)
								->setValue("transdate", $transdate)->setValue("transtype", $transtype)->setValue("customercode", $customercode)->setValue("enterby", $enterby)
								->setValue("enterdate", $enterdate)->setValue("changeby", $enterby)->setValue("changedate", $enterdate)->setValue("orgcode", $orgcode)
								->setValue("sitecode", $sitecode)->setValue("financialperiod", $financialperiod)->setValue("salesman", $salesman)
								->setValue("taxcode", $taxcode)->setValue("taxprice", $taxprice)->setValue("actualdate", $actualdate)->setValue("remarks", $remarks)
								->add();
								
								$name = Xbo::getCustomerName($customercode);
								
								$sellqtyFormat = number_format($quantity, 2);
								$activity = "has provided service of <font color=blue><b>$sellqtyFormat $saleunit</b></font> of <font color=blue><b>$description </b></font> to customer  <font color=blue><b>$name/$customercode</b></font>";
								Xbo::setActivity($activity);
								ActionSet::setSOSentStatus($sonum, $solinenum, $linetype);
								$retvalObj->msg = "sent";
								return $retvalObj;
							}
						}
					}
				}
			}
		}else{
			if ($linetype == "item") {
				$totalLineQty = $set->useTransactionManager()->MboSetRemote("matsaletrans")->setWhere("linetype='item' and sonum='$sonum' and solinenum='" . $solinenum . "'")
				->MboRemote("sum(quantity) as restQty")->getMboSet()->getMbo()->getString("restQty");
				
				if($totalLineQty == null) $totalLineQty=0;
				$restQty = $sellqty - ($totalLineQty * -1);
				if ($soldQty <= 0) {
					$retvalObj->msg  = "cannotSendZeroQuantity";
					return $retvalObj;
				}else{
					if ($soldQty > $restQty) {
							$saleunit = Xbo::getIssueUnit($itemnum);
							$retvalObj->msg = "cannotSellMoreThanLineQty";
							$retvalObj->sellqty = $restQty;
							$retvalObj->saleunit = $saleunit;
							return $retvalObj;
						}else{
							if ($soldQty <= $restQty && $soldQty != 0 && $soldQty > 0) {
							$condval = "and conditiontype ='$conditiontype'";
							if ($conditiontype == null) {
								$condval = "and (conditiontype ='' or conditiontype is null)";
							}
							$whval = "and warehouse ='$warehouse'";
							if ($warehouse == null) {
								$whval = "";
							}
							$rackval = "and racknum ='$racknum'";
							if ($racknum == null) {
								$rackval = "and (racknum ='' or racknum is null)";
							}
							if($costtype == "AVG"){
								$lineprofit = $soldQty * $unitprofit;
							}
							$lineprice = $soldQty * $unitprice;
							if (empty($costtype)) {
								$costtype = Xbo::getCostType($orgcode);
							}
							/*$oldcurbal = $set->useTransactionManager()->MboSetRemote("invbalance")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' $rackval $condval")->MboRemote("curbal")
							->getMboSet()->getMbo()->getString("curbal");
							*/
							$set = new Set();
							$invfifolifocostSet = $set->useTransactionManager()->MboSetRemote("invfifolifocost")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' $condval")->MboRemote("sum(quantity) as curbal")
							->getMboSet();
							
							$invfifolifocost = $invfifolifocostSet->getMbo();
							$oldcurbal = $invfifolifocost->getString("curbal");
							
							
							if (empty($oldcurbal)) {
								$retvalObj->msg  = "cannotSendZeroQuantity";
								return $retvalObj;
							}
							else {
								if($oldcurbal < $soldQty) {
									$saleunit = Xbo::getIssueUnit($itemnum);
									$retvalObj->msg = "notenoughstock";
									$retvalObj->sellqty = $oldcurbal;
									$retvalObj->saleunit = $saleunit;
									$retvalObj->warehouse = $warehouse;
									$retvalObj->racknum = $racknum;
									$retvalObj->itemnum = $itemnum;
									return $retvalObj;
								}else{
									$transtype = "SALE";
									$soldQty = $soldQty * -1;
									$financialperiod = Xbo::getFinancialPeriod();
									
									$bp = new Xbo();
									$baseprice = $bp->getUnitPriceDiscount($orgcode, $itemnum, $warehouse, $conditiontype);
									$primarydiscount = $baseprice->primarydiscount;
									$secondarydiscount = $baseprice->secondarydiscount;
									$discountprice = $baseprice->discountprice;
									
									
									if($costtype=="AVG"){
										$linecostmin = $linecost*-1;
										$taxprice = $soldQty*$unittaxprice;
										$linecommission = $unitcommission*$soldQty2;
										
										$matsale = $set->useTransactionManager()->MboSetRemote("matsaletrans")->setValue("sonum", $sonum)->setValue("solinenum", $solinenum)->setValue("linetype", $linetype)
										->setValue("itemnum", $itemnum)->setValue("conditiontype", $conditiontype)->setValue("description", $description)
										->setValue("quantity", $soldQty)->setValue("unitprice", $unitprice)->setValue("lineprice", $lineprice)
										->setValue("unitpricediscount", $unitpricediscount)->setValue("linepricediscount", $linepricediscount)
										->setValue("warehouse", $warehouse)->setValue("gldebitacct", $gldebitacct)->setValue("salesman", $salesman)
										->setValue("glcreditacct", $glcreditacct)->setValue("transdate", $enterdate)->setValue("enterdate", $enterdate)
										->setValue("enterby", $enterby)->setValue("transtype", $transtype)
										->setValue("customercode", $customercode)->setValue("pricecode", $pricecode)->setValue("costtype", $costtype)
										->setValue("unitprofit", $unitprofit)->setValue("lineprofit", $lineprofit)									
										->setValue("unitprofitdiscount", $unitprofitdiscount)->setValue("lineprofitdiscount", $lineprofitdiscount)
										->setValue("orgcode", $orgcode)->setValue("unitcost", $unitcost)
										->setValue("linecost", $linecostmin)->setValue("sitecode", $sitecode)->setValue("financialperiod", $financialperiod)
										->setValue("primarydiscount", $primarydiscount)->setValue("secondarydiscount", $secondarydiscount)->setValue("discountprice", $discountprice)
										->setValue("paymenttype", $paymenttype)->setValue("taxcode", $taxcode)->setValue("actualdate", $actualdate)->setValue("remarks", $remarks)
										->setValue("unitcommission", $unitcommission)->setValue("linecommission", $linecommission);
										
									}
									
									$setf = new Set();
									$fifotrans = $setf->useTransactionManager()->MboSetRemote("matsaletrans")->setValue("sonum", $sonum)->setValue("solinenum", $solinenum)->setValue("linetype", $linetype)
									->setValue("itemnum", $itemnum)->setValue("conditiontype", $conditiontype)->setValue("description", $description)->setValue("salesman", $salesman)
									->setValue("unitprice", $unitprice)->setValue("unitpricediscount", $unitpricediscount)->setValue("warehouse", $warehouse)->setValue("gldebitacct", $gldebitacct)
									->setValue("glcreditacct", $glcreditacct)->setValue("transdate", $enterdate)->setValue("enterdate", $enterdate)
									->setValue("enterby", $enterby)->setValue("transtype", $transtype)
									->setValue("customercode", $customercode)->setValue("pricecode", $pricecode)
									->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)->setValue("financialperiod", $financialperiod)
									->setValue("primarydiscount", $primarydiscount)->setValue("secondarydiscount", $secondarydiscount)->setValue("discountprice", $discountprice)
									->setValue("paymenttype", $paymenttype)->setValue("taxcode", $taxcode)->setValue("actualdate", $actualdate)->setValue("remarks", $remarks)
									->setValue("unitcommission", $unitcommission);
								
									$fl = new stdClass();
									$fl->unittaxprice = $unittaxprice;
									$fl->tablename = "matsaletrans";
									$fl->transtype = $transtype;
									$fl->itemnum = $itemnum;
									$fl->warehouse = $warehouse;
									$fl->conditiontype = $conditiontype;
									$fl->racknum = $racknum;
									$fl->issuedqty = $soldQty;
									$fl->condval = $condval;
									$fl->rackval = $rackval;
									$fl->fifotrans = $fifotrans;
									$fl->pricecode = $pricecode;
									$fl->unitprice = $unitprice;
									$fl->unitpricediscount = $unitpricediscount;
									$fl->costtype = $costtype;
									$fl->sonum = $sonum;
									$fl->unitcommission = $unitcommission;
									if($costtype == "AVG"){
										$fl->avgtrans = $matsale;
										$fl->unitcost = $unitcost;
									}
									
									$execfifo = XboSet::IssuedFIFOLIFO($fl);
									
									if($execfifo == "notenoughfifoqty"){
										$retvalObj->msg = $execfifo;
										$retvalObj->itemnum = $itemnum;
										$retvalObj->warehouse = $warehouse;
										$retvalObj->racknum = $racknum;
										
										$set = new Set();
										$set->useTransactionManager()->MboSetRemote("soline")->setWhere("sonum='$sonum' and solinenum='$solinenum'")->setValue("costtype", 'AVG')->change();
									}else{
										$set = new Set();
										$retvalObj->msg = "sent";
										if($soldQty<0) $soldQty=$soldQty*-1;
										$newCurbal = $oldcurbal - $soldQty;
										
										$set = new Set();
										if ($newCurbal < 0) $oldcurbal * -1;
										$set->useTransactionManager()->MboSetRemote("invbalance")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' $rackval $condval")
										->setValue("curbal", $newCurbal)->setValue("oldcurbal", $oldcurbal)->change();
										
										$set = new Set();
										$totalLineQty = $set->useTransactionManager()->MboSetRemote("soline")->setWhere("sonum='$sonum' and solinenum='$solinenum'")->MboRemote("sellqty")->getMboSet()->getMbo()->getString("sellqty");
										
										$set = new Set();
										$qtyLeftSet = $set->useTransactionManager()->MboSetRemote("matsaletrans")->setWhere("linetype='item' and sonum='$sonum' and solinenum='" . $solinenum . "'")
										->MboRemote("sum(quantity) as qtyLeft")->getMboSet();
										
										$qtyLeft = $qtyLeftSet->getMbo()->getString("qtyLeft");
										
										if($qtyLeft == null) $qtyLeft =0;
										$qtyLeft =  $qtyLeft * -1;
										if ($qtyLeft === null) $qtyLeft = 0;
										$x = $totalLineQty - $qtyLeft;
										if ($x == $totalLineQty) $sentstatus = 'NONE';
										if ($x < $totalLineQty && $x > 0) $sentstatus = 'PARTIAL';
										if ($x == 0) $sentstatus = 'COMPLETED';
										
										$set = new Set();
										$set->useTransactionManager()->MboSetRemote("soline")->setWhere("sonum='$sonum' and solinenum='$solinenum'")->setValue("sentstatus", $sentstatus)->change();
										ActionSet::setSOSentStatus($sonum, $solinenum, $linetype);
										XboSet::AdjustConversionStock($itemnum, $warehouse);									
									}
									return $retvalObj;
								}
							}
						}
					}
				}
			}
		}
		
		
		
	}
	public static function SORectrans(){
		$transtype = $_POST["transtype"];
		$msg = null;
		$sellqty = null;
		$saleunit = null;
		$warehouse = null;
		$racknum = null;
		$itemnum = null;
		$postvalue = $_POST["sorectrans"];
		
		
		$countrows = count($postvalue);
		if($transtype == "soline"){
			
			$paymenttype = $_POST["paymenttype"];
			$salesman = $_POST["salesman"];
			
			$other = new stdClass();
			$other->paymenttype = $paymenttype;
			$other->salesman = $salesman;
			
			for($i=0;$i<$countrows; $i++){
				$value = $postvalue[$i];
				
				$get = ActionSet::ReceiptTransaction($value, $other);
				$msg = $get->msg;
				if($msg != "sent"){
					
					if(isset($get->sellqty)){
						$sellqty = $get->sellqty;
						$saleunit = $get->saleunit;
						if(isset($get->warehouse)){
							$warehouse = $get->warehouse;
							$racknum = $get->racknum;
							$itemnum = $get->itemnum;
						}
					}else{
						$sellqty = null;
						$saleunit = null;
						if(isset($get->warehouse)){
							$warehouse = $get->warehouse;
							$racknum = $get->racknum;
							$itemnum = $get->itemnum;
						}
					}
					break;
				}else{
					if(isset($get->sellqty)){
						$sellqty = $get->sellqty;
						$saleunit = $get->saleunit;
					}else{
						$sellqty = null;
						$saleunit = null;
					}
				}
				
			}
			
			if($msg!="sent"){
				if($msg == "notenoughstock"){
					Set::throws(["msg"=>$msg, "sellqty"=>$sellqty, "saleunit" => $saleunit, "warehouse" =>$warehouse, "racknum"=>$racknum,"itemnum"=>$itemnum]);
				}else{
					if($msg == "notenoughfifoqty"){
						Set::throws(["msg"=>$msg, "itemnum"=>$itemnum, "warehouse" => $warehouse, "racknum" => $racknum]);
					}else{
						Set::throws(["msg"=>$msg, "sellqty"=>$sellqty, "saleunit" => $saleunit]);
					}
					
				}
				
			}else{
				Set::throws(["msg"=>$msg]);
			}
		}else{
			if($transtype == "rectrans"){
				for($i=0;$i<count($_POST['sorectrans']); $i++){
					$value = $_POST['sorectrans'][$i];
					$process = ActionSet::ReturnTransaction($value);
					$msg = $process->msg;
					if($msg !="returned"){
						break;
					}
				}
				if($msg == "returned"){
					Set::throws(["msg"=>$msg]);
				}else{
					Set::throws(["msg"=>$msg]); 
				}
				
				
			}
		}
	}

	public static function getTaxRate($value){
		$tax_value =  (new Set())->useTransactionManager()->MboSetRemote("tax")->where("taxcode = ? ", $value)->MboRemote("value")->getMboSet()->getMbo()->getString("value");
		return $tax_value;
	}
	public static function getTaxValue()
	{
		$value = trim($_GET['getTaxValue'], " ");
		$set = new Set();
		echo $set->useTransactionManager()->MboSetRemote("tax")->setWhere("taxcode = '$value'")->MboRemote("value")->getMboSet()->getMbo()->getString("value");
	}
	public static function IsSOLineQtyExist()
	{
		try {
			$sonum = Super::getParam("issolineqtyexist");
			if (empty($sonum)) {
				throw new InvalidArgumentException("SO Number is not given in Payload");
			}

			// Bangun satu kueri cerdas untuk menemukan SEMUA baris yang bermasalah sekaligus.
			$problematicLinesSet = (new Set())->useTransactionManager()
				->MboSetRemote("soline sl")
				->setRelationship("v_invbalance vib", 
					"sl.itemnum = vib.itemnum AND sl.warehouse = vib.warehouse AND COALESCE(sl.conditiontype, '') = COALESCE(vib.conditiontype, '') AND COALESCE(sl.racknum, '') = COALESCE(vib.racknum, '') ")
				->where("sl.sonum = ?", $sonum)
				->MboRemote("sl.solinenum, sl.linetype , sl.itemnum, sl.warehouse, sl.costtype, sl.conditiontype, sl.sellqty, sl.lineprice, sl.wonum, sl.gldebitacct, sl.glcreditacct, sl.serviceitemnum, sl.description, vib.racknum")
				->MboRemote("COALESCE(vib.curbal, 0) as curbal") 
				->getMboSet();
			
			$problematicLines = $problematicLinesSet->fetchData();

			// Langkah 2: Proses hasilnya
			if (empty($problematicLines)) {
				Set::throws(["msg" => "true", "content" => null]);
			} else {
				
				$errorMessages = [];
				foreach ($problematicLines as $line) {
					$warehouse = $line['warehouse'];
					$solinenum = $line['solinenum'];
					$linetype = $line['linetype'];
					$wonum = $line['wonum'];
					$gldebitacct = $line['gldebitacct'];
					$glcreditacct = $line['glcreditacct'];
					$serviceitemnum = $line['serviceitemnum'];
					$description = $line['description'];
					$itemnum = $line['itemnum'];
					$costtype = $line['costtype'];
					
					if ($linetype == "item") {
						if (empty($itemnum)) {
							$errorMessages[] = "<li>itemnum is not defined on Line='{$solinenum}', Item='{$itemnum}'</li>";
							continue;
						}
						
						if (empty($warehouse)) {
							$errorMessages[] = "<li>Warehouse is not defined on Line='{$solinenum}', Item='{$itemnum}'</li>";
							continue;
						}
						
						// Build base error message
						$errorMessage = "<li>Line='{$solinenum}', Item='{$itemnum}', Wh='{$warehouse}'";
						if (!empty($line['conditiontype'])) {
							$errorMessage .= ", Cond='{$line['conditiontype']}'";
						}
						$errorMessage .= "<br /><b>Sell Qty:</b> {$line['sellqty']}. <b>Current Qty:</b> {$line['curbal']}.";
						
						// Check validation rules
						if ($line['lineprice'] == 0) {
							$errorMessages[] = $errorMessage . " Reason: Selling Price is 0.</li>";
							continue;
						}
						
						if ($line['sellqty'] == 0) {
							$errorMessages[] = $errorMessage . " Reason: Selling Qty is 0.</li>";
							continue;
						}
						
						// Get total stock across all racks
						$total_stock = (new Set())->MboSetRemote("v_invbalance")
							->where("itemnum=? and warehouse=?", $itemnum, $warehouse)
							->group("racknum")
							->MboRemote("sum(curbal) as total_stock")
							->getMboSet()
							->getMbo()
							->getDecimal("total_stock");
						
						if ($total_stock >= $line['sellqty']) {
							// Stock is sufficient across multiple locations
							if ($costtype == "AVG") {
								$errorMessages[] = $errorMessage . "<br>Reason: You are using Cost Type: AVG and have selected a specific warehouse and rack, but the stock at that location is insufficient.<br><br>The total stock is actually sufficient, but it is spread across multiple locations. If you use FIFO/LIFO, the system will pull stock from various locations based on the receiving date, allowing the SO to be processed.</li>";
							}
							// If FIFO/LIFO, no error needed - continue to next line
						} else {
							// Not enough total stock
							$errorMessages[] = $errorMessage . " Reason: Not enough stock.</li>";
						}
						
					} else {
						// Service line validation
						if (empty($wonum)) {
							$errorMessages[] = "<li>Work Order is not defined on Line='{$solinenum}'</li>";
						} elseif (empty($gldebitacct) || empty($glcreditacct)) {
							$errorMessages[] = "<li>GL Debit Account OR GL Credit Account is not defined on Line='{$solinenum}'</li>";
						} elseif (empty($description)) {
							$errorMessages[] = "<li>Description is not defined on Line='{$solinenum}'</li>";
						}
					}
				}

				$count_errMsg = count($errorMessages);
				if ($count_errMsg == 0) {
					Set::throws(["msg" => "true", "content" => $fullContent]);
					exit();
				} else {
					$fullContent = "<ul>" . implode("", $errorMessages) . "</ul>";
					Set::throws(["msg" => "false", "content" => $fullContent]);
					exit();
				}
				
				
			}

		} catch (Exception $e) {
			// Kirim response error yang terstruktur
			Set::throws(["msg" => "false", "content" => "An error occurred: " . $e->getMessage()]);
		}
	}
	public static function validateSellItem()

	{
		$x = $_GET['validateSellItem'];
		$set = new Set();
		$invbalanceSet = $set->useTransactionManager()->MboSetRemote("v_invbalance")->setRelationship("item", "item.itemnum=v_invbalance.itemnum ")->setWhere("v_invbalance.v_invbalanceid='$x'")
		->MboRemote("v_invbalance.itemnum")->MboRemote("v_invbalance.warehouse")->MboRemote("v_invbalance.racknum")->MboRemote("v_invbalance.conditiontype")->MboRemote("v_invbalance.curbal")
		->MboRemote("v_invbalance.orgcode")->MboRemote("v_invbalance.sitecode")->MboRemote("item.stocktype")->getMboSet("remove_duplicate")->getMbo();
		$itemnum = $invbalanceSet->getString("itemnum");
		$warehouse = $invbalanceSet->getString("warehouse");
		$racknum = $invbalanceSet->getString("racknum");
		$conditiontype = $invbalanceSet->getString("conditiontype");
		$customercode = "";
		$condval = "";
		if (empty($conditiontype)) {
			$condval = "(conditiontype='' or conditiontype is null)";
		}
		else {
			$condval = "conditiontype='$conditiontype'";
		}
		$curbal = $invbalanceSet->getString("curbal");
		$stocktype = $invbalanceSet->getString("stocktype");
		$orgcode = $invbalanceSet->getString("orgcode");
		$sitecode = $invbalanceSet->getString("sitecode");
		
		$enterby = Xbo::getLoginUser();
		$enterdate = Xbo::setEnterDate();
		$costtype = Xbo::InventoryCostType($itemnum, $warehouse, $orgcode);
		
		
		if($costtype == "AVG"){
			$set = new Set();
			$invcostSet =  $set->useTransactionManager()->MboSetRemote("invcost")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' and $condval and orgcode='$orgcode' and sitecode='$sitecode'")->MboRemote("avgcost")->getMboSet();
			$unitcost = $invcostSet->getMbo()->getString("avgcost");
		}else{
			$unitcost = 0;
			$fifowhereclause = "itemnum='$itemnum' and warehouse='$warehouse' and $condval and quantity>0";
			 if($costtype == "FIFO"){
				$orderby = "asc";
			}else{
				if($costtype == "LIFO") {
					$orderby = "desc";
				}else{
					$orderby = "asc";
				}
			}
			$ifSet = $set->useTransactionManager()->MboSetRemote("invfifolifocost")->order("costdate $orderby")->setWhere($fifowhereclause);
			$iflset = $ifSet->MboRemote("unitcost")->getMboSet();
			$cnt = $iflset->counts();
			if($cnt == 0){
				$unitcost=0;
			}else{
				$ifl = $iflset->getMbo();
				$unitcost = $ifl->getString("unitcost");
			}
		}
		$set = new Set();
		$invprice = $set->useTransactionManager()->MboSetRemote("invprice")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' and $condval and defaultprice='Y'")->MboRemote("pricecode")->MboRemote("unitprice")->getMboSet()->getMbo();
		$pricecode = $invprice->getString("pricecode");
		
		
		
		$u = new Xbo();
		$up = $u->getUnitPriceDiscount($orgcode, $itemnum, $warehouse, $conditiontype);
		$unitprice = $u->unitpricediscount;
		
		
		if ($unitcost == null) $unitcost = 0;
		$glcreditacct = Xbo::getCustomerRBNIAcct($customercode, $orgcode, $sitecode); 
		$gldebitacct = Xbo::getCashOnBankAcct($orgcode, $sitecode);
		
		if ($stocktype == "UNSELLABLE") {
			Set::throws(["msg" => "unsellable"]);
			return null;
		}
		
		if ($curbal >= -1) {	
			Set::throws(["warehouse" => $warehouse, "racknum" => $racknum, "conditiontype" => $conditiontype, "curbal" => $curbal, 
						"costtype" => $costtype, "unitcost" => $unitcost, "gldebitacct" => $gldebitacct, "glcreditacct" => $glcreditacct, 
						"unitprice" => $unitprice, "pricecode" => $pricecode, "enterby" => $enterby, "enterdate" => $enterdate]);
		}
	}



	public static function ValidateDiscountPrice()
	{
		$pts = explode("|", $_GET["validatediscountprice"]);
		$set = new Set();
		$sonum = $pts[1];
		$discount = $pts[0];
		$isrecordExist = $set->useTransactionManager()->MboSetRemote("invoiceline")->setWhere("sonum='$sonum'")->MboRemote("invoicenum")->getMboSet()->counts();
		if ($isrecordExist != 0) {
			echo "404";
		}
		else {
			if ($discount > 0) {
				echo "200";
			}
			if ($discount < 0) {
				echo "500";
			}
		}
	}
	
	public static function SumSOCostBackend($sonum){
		
		$set = new Set();
		$soSet = $set->useTransactionManager()->MboSetRemote("so")->setWhere("sonum='$sonum'")->MboRemote("discountpercentage")->getMboSet();
		$so = $soSet->getMbo();
		
		$discountpercentage = $so->getString("discountpercentage");
		
		$whereclause = "sonum='$sonum'";
		$set = new Set();
		$st = new Set();
		
		$solineSet = $st->useTransactionManager()
                ->MboSetRemote("soline")
                ->setWhere($whereclause)
                ->getMboSet();

		$cnt = $solineSet->counts(); 

		for ($i = 0; $i < $cnt; $i++) {

			$soline = $solineSet->getMbo(); 

			$solineid = $soline->getString("solineid");
			$unitprice = (float) $soline->getString("unitprice");
			$lineprice = (float) $soline->getString("lineprice");
			$taxprice  = (float) $soline->getString("taxprice");

			$discountvalue = $discountpercentage / 100;
			$unitpricediscount = $unitprice * $discountvalue;
			$linepricediscount = $lineprice * $discountvalue;
			$taxpricediscount  = $taxprice * $discountvalue;

			// Update field langsung
			(new Set())->useTransactionManager()->MboSetRemote("soline")->setWhere("solineid='$solineid'")->setValue("unitpricediscount", $unitpricediscount)
			->setValue("linepricediscount", $linepricediscount)->setValue("taxpricediscount", $taxpricediscount)->change();

		}
		
		$totaltax = $set->useTransactionManager()->MboSetRemote("soline")->setWhere($whereclause)->MboRemote("sum(taxprice) as totaltax")->getMboSet()->getMbo()->getString("totaltax");
		if($totaltax == null )$totaltax=0.00;
		$pretaxtotal = $set->useTransactionManager()->MboSetRemote("soline")->setWhere($whereclause)->MboRemote("sum(lineprice) as pretaxtotal")->getMboSet()->getMbo()->getString("pretaxtotal");
		if($pretaxtotal == null )$pretaxtotal=0.00;
		
		$pretaxtotaldiscount = $set->useTransactionManager()->MboSetRemote("soline")->setWhere($whereclause)->MboRemote("sum(linepricediscount) as pretaxtotaldiscount")->getMboSet()->getMbo()->getString("pretaxtotaldiscount");
		if($pretaxtotaldiscount == null )$pretaxtotaldiscount=0.00;
		
		$paidprice = $set->useTransactionManager()->MboSetRemote("sopayment")->setWhere($whereclause)->MboRemote("sum(paidprice) as paidprice")->getMboSet()->getMbo()->getString("paidprice");
		if ($paidprice == null) $paidprice = 0.00;
		
		$set = new Set();
		$invoicediscount = $set->useTransactionManager()->MboSetRemote("sopayment")->setWhere($whereclause)->MboRemote("sum(invoicediscount) as invoicediscount")->getMboSet()->getMbo()->getString("invoicediscount");
		if ($invoicediscount == null) $invoicediscount = 0.00;
		
		$set = new Set();
		
		
		$totalprice = $totaltax + $pretaxtotal;
		
		
		$unpaidprice = $paidprice - $totalprice;
		$discountedtotalprice = $totalprice - ($totalprice * ($discountpercentage / 100)); 
		//add totalpriceafterdiscount + othercost + shipping cost
		$discountedtotalprice = $discountedtotalprice;
		$unpaidprice2 = $paidprice - $discountedtotalprice;
		$unpaidprice2 = $unpaidprice2;
		
		
		$discountedtotaltax = $set->useTransactionManager()->MboSetRemote("soline")->setWhere($whereclause)->MboRemote("sum(taxpricediscount) as discountedtotaltax")->getMboSet()->getMbo()->getString("discountedtotaltax");
		if($discountedtotaltax == null )$discountedtotaltax=0.00;
		
		if($invoicediscount>0){
			$taxpercentage = round($invoicediscount)/round($discountedtotaltax)/10;
			$disctax = $discountedtotaltax*$taxpercentage;
			$discountedtotaltax = $discountedtotaltax-$disctax;
			//$discountedtotaltax = 46904.175;
		}
		
		$discountedtotalprofit = $pretaxtotaldiscount-$invoicediscount;
		$discountedtotalprice = $discountedtotaltax+$discountedtotalprofit;
		
		$unpaidprice2 = $discountedtotalprice-$paidprice;
		if($unpaidprice2>0) $unpaidprice2 = $unpaidprice2*-1;
		
		
		$set = new Set();
		$set->useTransactionManager()->MboSetRemote("so")->setWhere($whereclause)
		->setValue("discountedtotalprice", $discountedtotalprice)
		->setValue("discountedtotalprofit", $discountedtotalprofit)
		->setValue("discountedtotaltax", $discountedtotaltax)
		->setValue("totalprice", $totalprice)
		->setValue("totaltax", $totaltax)
		->setValue("pretaxtotal", $pretaxtotal)
		->setValue("paidprice", $paidprice)
		->setValue("unpaidprice", round($unpaidprice2))
		->setValue("invoicediscount", $invoicediscount)
		->change();
					
		
	}
	public static function SumSOCost()
	{
		$payload = Super::getParam("sumsocost");
		$sonum = $payload["sonum"];
		$discountprice = $payload["discountprice"];
		$discountpercentage = $payload["discountpercentage"];
		$whereClause = "sonum='$sonum'";
		$st = new Set();
		$solineSet = (new Set())->useTransactionManager()->MboSetRemote("soline")->setWhere($whereClause)->MboRemote("solineid")->MboRemote("unitprice")->MboRemote("lineprice")->MboRemote("taxprice")->getMboSet();
		$cnt = $solineSet->counts();
		for($i=0; $i<$cnt; $i++){
			$soline = $solineSet->getMbo();
			$solineid = $soline->getString("solineid");
			$unitprice = $soline->getString("unitprice");
			$lineprice = $soline->getString("lineprice");
			$taxprice = $soline->getString("taxprice");
			$discountvalue = $discountpercentage / 100;
			
			$unitpricediscount = $unitprice-($unitprice*$discountvalue);
			$linepricediscount = $lineprice-($lineprice*$discountvalue);
			$taxpricediscount = $taxprice-($taxprice*$discountvalue);
			$ss = new Set();
			$ss->MboSetRemote("soline")->setWhere("solineid='$solineid'")->setValue("unitpricediscount", $unitpricediscount)
			->setValue("linepricediscount", $linepricediscount)->setValue("taxpricediscount", $taxpricediscount)->change();
		}
		
		
		$totaltax = (new Set())->useTransactionManager()->MboSetRemote("soline")->setWhere($whereClause)->MboRemote("sum(taxprice) as totaltax")->getMboValue("totaltax");
		if($totaltax == null )$totaltax=0.00;
		$pretaxtotal = (new Set())->useTransactionManager()->MboSetRemote("soline")->setWhere($whereClause)->MboRemote("sum(lineprice) as pretaxtotal")->getMboValue("pretaxtotal");
		if($pretaxtotal == null )$pretaxtotal=0.00;
		
		$pretaxtotaldiscount = (new Set())->useTransactionManager()->MboSetRemote("soline")->setWhere($whereClause)->MboRemote("sum(linepricediscount) as pretaxtotaldiscount")->getMboValue("pretaxtotaldiscount");
		if($pretaxtotaldiscount == null )$pretaxtotaldiscount=0.00;
		
		$paidprice = (new Set())->useTransactionManager()->MboSetRemote("sopayment")->setWhere($whereClause)->MboRemote("sum(paidprice) as paidprice")->getMboValue("paidprice");
		if ($paidprice == null) $paidprice = 0.00;
		
		$set = new Set();
		$invoicediscount = (new Set())->useTransactionManager()->MboSetRemote("sopayment")->setWhere($whereClause)->MboRemote("sum(invoicediscount) as invoicediscount")->getMboValue("invoicediscount");
		if ($invoicediscount == null) $invoicediscount = 0.00;
		
		$totalprice = $totaltax + $pretaxtotal;
		$unpaidprice = $paidprice - $totalprice;
		$discountedtotalprice = $totalprice - ($totalprice * ($discountpercentage / 100)); 
		$discountedtotalprice = $discountedtotalprice;
		$unpaidprice2 = $paidprice - $discountedtotalprice;
		$unpaidprice2 = $unpaidprice2;
		
		
		$discountedtotaltax = (new Set())->useTransactionManager()->MboSetRemote("soline")->setWhere($whereClause)->MboRemote("sum(taxpricediscount) as discountedtotaltax")->getMboValue("discountedtotaltax");
		if($discountedtotaltax == null )$discountedtotaltax=0.00;
		
		if($invoicediscount>0){
			$taxpercentage = round($invoicediscount)/round($discountedtotaltax)/10;
			$disctax = $discountedtotaltax*$taxpercentage;
			$discountedtotaltax = $discountedtotaltax-$disctax;
		}
		
		$discountedtotalprofit = $pretaxtotaldiscount-$invoicediscount;
		$discountedtotalprice = $discountedtotaltax+$discountedtotalprofit;
		
		$unpaidprice2 = $discountedtotalprice-$paidprice;
		if($unpaidprice2>0) $unpaidprice2 = $unpaidprice2*-1;
		
		Set::throws([
					"discountedtotalprice" => $discountedtotalprice,  //GRAND TOTAL
					"discountedtotalprofit" => $discountedtotalprofit, //Sub Total Setelah diskon
					"discountedtotaltax" => $discountedtotaltax, //PAJAK SETELAH DISKON
					"totalprice" => $totalprice, // TOTAL (TOTAL SEBELUM PAJAK + PAJAK) 
					"totaltax" => $totaltax, // PAJAK 
					"pretaxtotal" => $pretaxtotal, // Total Sebelum pajak
					"paidprice" => $paidprice, 
					"unpaidprice" => round($unpaidprice2), 
					"invoicediscount"=>$invoicediscount
		]);
		
	}
	
	public static function SOChangeOrderService()
	{
		
		$sonum = $_POST["sochangeorderserv"];
		$solinenum = $_POST["solinenum"];
		$sellqty = $_POST["sellqty"];
		$unitprice = $_POST["unitprice"];
		$taxcode = $_POST["taxcode"];
		
		$set = new Set();
		$quantity = $set->useTransactionManager()->MboSetRemote("servsaletrans")->setWhere("sonum='$sonum' and solinenum='$solinenum'")->MboRemote(" sum(quantity) as quantity ")->getMboSet()->getMbo()->getString("quantity");
		if ($quantity > 0) {
			if ($sellqty < $quantity) {
				Set::throws(["msg" => "socannotchangeqty", "sellqty" => $quantity, "sellqty2"=>$sellqty, "sonum"=>$sonum, "unitprice"=>$unitprice, "lineprice"=>0, "taxcode"=>$taxcode, "taxprice" =>0]);
			}
		}else{
			$s = new Set();
			if(!empty($taxcode)){
				$taxSet = $s->useTransactionManager()->MboSetRemote("tax")->setWhere("taxcode='$taxcode'")->MboRemote("value")->getMboSet();
				$tax = $taxSet->getMbo();
				$value = $tax->getString("value");
			}else{
				$value = 0;
			}
			$lineprice = $sellqty*$unitprice;
			$taxprice = ($lineprice*$value)/100;
			Set::throws(["msg" => "socanchangeqty", "sellqty" => null, "sellqty2"=>$sellqty, "sonum"=>$sonum, "unitprice"=>$unitprice, "lineprice"=>$lineprice, "taxcode"=>$taxcode, "taxprice" =>$taxprice]);
		}
	}
	
	public static function SOChangeOrderQty()
	{
		$sonum = $_POST["sochangeorderqty"];
		$solinenum = $_POST["solinenum"];
		$itemnum = $_POST["itemnum"];
		$sellqty = $_POST["sellqty"];
		$set = new Set();
		
		if (empty($itemnum)) {
			$itemval = " and (itemnum='' or itemnum is null)";
		}
		else {
			$itemval = " and itemnum='$itemnum'";
		}
		$matsaletransSet = $set->useTransactionManager()->MboSetRemote("matsaletrans")->setWhere("sonum='$sonum' and solinenum='$solinenum' $itemval")->MboRemote(" sum(quantity)*-1 as quantity ")->getMboSet();
		
		$matsaletrans = $matsaletransSet->getMbo();
		$quantity= $matsaletrans->getString("quantity");
		if($quantity == null){
			Set::throws(["msg" => "socanchangeqty"]);
		}else{
			if ($quantity > 0) {
				if ($sellqty >= $quantity) {
					Set::throws(["msg" => "socanchangeqty"]);
				}
			}
			if ($sellqty < $quantity || $sellqty <= 0) {
				Set::throws(["msg" => "socannotchangeqty", "sellqty" => $quantity]);
			}
		}
		
	}
	
	public static function VRI()
	{
		$vri = urldecode($_POST['vri']);
		$g = explode("^", $vri);
		$table = $g[0];
		$recid = $g[1];
		$set = new Set();
		if ($table == "so") {
			$sonum = $set->useTransactionManager()->MboSetRemote("so")->setWhere("soid='$recid'")->MboRemote("sonum")->getMboSet()->getMbo()->getString("sonum");
			$countmatsale = $set->useTransactionManager()->MboSetRemote("matsaletrans")->setWhere("sonum='$sonum'")->MboRemote("sonum")->getMboSet()->counts();
			if ($countmatsale > 0) {
				Set::throws(["msg" => "198", "tbl" => "matsaletrans"]);
			}
			else {
				$countservsale = $set->useTransactionManager()->MboSetRemote("servsaletrans")->setWhere("sonum='$sonum'")->MboRemote("sonum")->getMboSet()->counts();
				if ($countservsale > 0) {
					Set::throws(["msg" => "198", "tbl" => "servsaletrans"]);
				}
				else {
					Set::throws(["msg" => "200"]);
				}
			}
		}
	}
	public static function SoldItems(){
		$pts = explode("^", $_POST["solditems"]);
		$id = $pts[1];
		$set = new Set();
		
		$soSet = $set->useTransactionManager()->MboSetRemote("so")->setWhere("soid='$id'")->MboRemote("sonum")->getMboSet();
		$so = $soSet->getMbo();
		$sonum = $so->getString("sonum");
		
		$solineSet = $set->useTransactionManager()->MboSetRemote("soline")->setWhere("sonum='$sonum'")->MboRemote("wonum")->getMboSet();
		$solineCounts = $solineSet->counts();
		
		for($i=0; $i<$solineCounts; $i++){
			$set2 = new Set();
			$soline = $solineSet->getMbo();
			$wonum = $soline->getString("wonum");
			$woSet = $set2->useTransactionManager()->MboSetRemote("workorder")->setRelationship("customer", "workorder.customercode = customer.customercode")
					->setWhere("workorder.wonum='$wonum'")->MboRemote("workorder.customercode")->MboRemote("customer.distributor")->MboRemote("customer.reseller")
					->getMboSet();
			$wo = $woSet->getMbo();
			
			$distributor = $wo->getString("distributor");
			$reseller = $wo->getString("reseller");
			$customercode = $wo->getString("customercode");
			
			if($distributor == "Y" || $reseller == "Y"){
				$set3 = new Set();
				$set3->useTransactionManager()->MboSetRemote("custbalance")->setWhere("wonum='$wonum' and customercode='$customercode'")->remove();
			}
		}
	}
	public static function DefaultSalesRepresentative()
	{
		$custcode = $_GET["defaultsalesrep"];
		$set = new Set();
		$salesrep = $set->useTransactionManager()->MboSetRemote("customer")->setWhere("customercode='$custcode'")->MboRemote("salesrep")->getMboSet()->getMbo()->getString("salesrep");
		Set::throws(["employeesid" => $salesrep]);
	}
	public static function ValidateShowChildren()
	{
		$pts = explode("|", $_GET["validateshowchildren"]);
		$set = new Set();
		if ($pts[3] == "soline") {
			$solineSet = $set->useTransactionManager()->MboSetRemote("soline")->setWhere("sonum='" . $pts[0] . "' and solineid = '" . $pts[2] . "'")->MboRemote("solinenum")->getMboSet();
			$solinenum = $solineSet->getMbo()->getString("solinenum");
			$newset = new Set();
			$invoiceLineSet = $newset->useTransactionManager()->MboSetRemote("invoiceline")->setWhere("sonum='" . $pts[0] . "' and invoicelinenum='$solinenum'")->MboRemote("sonum")->getMboSet();
			$isExist = $invoiceLineSet->counts();
			if ($isExist > 0) {
				Set::throws(["msg" => "relateddata", "rltbl" => "invoiceline"]);
			}
		}
	}
	

} 

Class ValidateTables {	
	public static function tbce(){
		$table = $_GET['tbce'];
		if ($table == "mxtable_vendorpolines_child" || $table == "mxtable_vendormatrectrans_child" || $table == "mxtable_vendorservrectrans_child" || $table == "mxtable_receipt_child" || $table == "mxtable_matsaletrans_child" || $table == "mxtable_servsaletrans_child" || $table == "mxtable_soshipment_child" || $table == "mxtable_sotracking_child") 
			echo "histable";
		if ($table == "mxtable_inventory_child" || $table == "mxtable_invvendor_child") echo "relatedtable";
	}
	public static function tbcx(){ 
	
		/*

		{"tbcx":{"ownership":{
			"xboset":"so",
			"xbosetid":"21",
			"owner":[{"sonum":"SO-003-25"}],
			"userid":"maxesadmin"
			},
			"datasetChild":"soline",
			"selectedRows":[{"solineid":"43"}]}}

		*/

		$payload = Super::getParam("tbcx") ?? [];
		$ownership = $payload["ownership"] ?? null;
		$owner = $ownership["owner"] ?? null;
		$childrentable = $payload["datasetChild"] ?? null;
		$selected_rows = $payload["selectedRows"] ?? null;

		if (!isset($payload["ownership"], $payload["datasetChild"], $payload["selectedRows"])) {
			return; // atau return null;
		}

		if ($childrentable == "matsaletrans" || $childrentable == "servsaletrans") {
			Super::sendJsonResponse("error", "Transaction Table cannot be deleted.");
			exit();
		}
		elseif($childrentable == "soline"){
			foreach($selected_rows as $row){
				foreach($row as $key=>$value){
					$sonum = (new Set())->MboSetRemote($childrentable)->setWhere("$key='$value'")->MboRemote("sonum")->getMboValue("sonum");
					$hasInvoiceNum = (new Set())->MboSetRemote("invoiceline")->where("sonum=?", $sonum)->getMboSet();
					if($hasInvoiceNum->isEmpty()){
						$hasQuotation =  (new Set())->MboSetRemote("salesquotationline")->where("sonum=?", $sonum)->getMboSet();
						if($hasQuotation->isEmpty()){
							$hasWO =  (new Set())->MboSetRemote("workorder")->where("sonum=?", $sonum)->getMboSet();
							if($hasWO->isEmpty()){
								$_POST["delChildPackage"] = $payload;
								Super::deleteChildren();		
							}else{
								$wonum = $hasQuotation->getMbo()->getString("wonum");
								Super::sendJsonResponse("error", "The SOLINE has Work Order Related Record: $wonum");	
							}
						}else{
							$salesquotenum = $hasQuotation->getMbo()->getString("salesquotenum");
							Super::sendJsonResponse("error", "The SOLINE has Sales Quotation Line Related Record: $salesquotenum");
						}
					}else{
						$invoicenum = $hasQuotation->getMbo()->getString("invoicenum");
						Super::sendJsonResponse("error", "The SOLINE has INVOICE Related Record: $invoicenum");
					}
				}
			}
		}

		elseif ($childrentable == "solinewo") {
			foreach($selected_rows as $row){
				$solinewoid = $row["solinewoid"];
				$solinewo = (new Set())->useTransactionManager()->MboSetRemote("solinewo")->setWhere("solinewoid='$solinewoid'")
				->MboRemote("sonum, solinenum, quantity, wonum")->getMboSet()->getMbo();;
				$sonum = $solinewo->getString("sonum");
				$solinenum = $solinewo->getString("solinenum");
				$quantity = $solinewo->getString("quantity");
				$wonum =  $solinewo->getString("wonum");
				
				if(empty($wonum)){
					$soline = (new Set())->useTransactionManager()->MboSetRemote("soline")->where("sonum=? AND solinenum=?", $sonum, $solinenum)
					->MboRemote("sonum, solinenum, remainingqty")->getMboSet()->getMbo();
					$remainingqty = $soline->getString("remainingqty");
					$addedqty = $quantity+$remainingqty;
					(new Set())->useTransactionManager()->MboSetRemote("soline")->where("sonum=? AND solinenum=?", $sonum, $solinenum)->setValue("remainingqty", $addedqty)->change();
					$_POST["delChildPackage"] = $payload;
					Super::deleteChildren();	
				}else{
					$isWOExist = (new Set())->useTransactionManager()->MboSetRemote("workorder")->where("sonum=? and solinenum=? and sodistlinenum=?", $sonum, $solinenum, $sodistlinenum)->getMboSet();
					if($isWOExist->isEmpty()){
						$_POST["delChildPackage"] = $payload;
						Super::deleteChildren();
					}else{
						Super::sendJsonResponse("error", "This job cannot be deleted because it is linked to Work Order: $wonum.");
					}
				}
			}
		}
		if ($childrentable == "soline") {
			//check matsaletrans & servsaletrans & solinewo
			foreach($selected_rows as $row){
				$solineid = $row["solineid"];
				$soline = (new Set())->useTransactionManager()->MboSetRemote("soline")->setWhere("solineid='$solineid'")
				->MboRemote("sonum, solinenum")->getMboSet()->getMbo();

				$solinenum = $soline->getString("solinenum");
				$sonum = $soline->getString("sonum");

				//Check Matsaletrans
				$matsaletransSet = (new Set())->useTransactionManager()->MboSetRemote("matsaletrans")->where("sonum=? AND solinenum=?", $sonum, $solinenum)->getMboSet();
				if($matsaletransSet->isEmpty()){
					//check servsaletrans 
					$servsaletransSet =  (new Set())->useTransactionManager()->MboSetRemote("servsaletrans")->where("sonum=? AND solinenum=?", $sonum, $solinenum)->getMboSet();
					if($servsaletransSet->isEmpty()){
						//check solinewo
						$solinewoSet =  (new Set())->useTransactionManager()->MboSetRemote("solinewo")->where("sonum=? AND solinenum=?", $sonum, $solinenum)->getMboSet();
						if($solinewoSet->isEmpty()){
							//check sopayment
							$sopaymentSet =  (new Set())->useTransactionManager()->MboSetRemote("sopayment")->where("sonum=? AND solinenum=?", $sonum, $solinenum)->getMboSet();
							if($sopaymentSet->isEmpty()){
								$_POST["delChildPackage"] = $payload;
								Super::deleteChildren();
							}else{
								$sopayment = $sopaymentSet->getMbo();
								$invoicenum = $sopayment->getString("invoicenum");
								Super::sendJsonResponse("error", "This Line: $solinenum cannot be deleted because it is linked to the payment: $invoicenum.");
							}
						}else{
							$solinewo = $solinewoSet->getMbo();
							$sodistlinenum = $solinewo->getString("sodistlinenum");
							$wonum = $solinewo->getString("wonum");
							$ctx_wo = ": $wonum";
							if(empty($wonum)){
								$ctx_wo="";
							}
							Super::sendJsonResponse("error", "This Line: $solinenum cannot be deleted because it has been followed up to the Work Order Distribution $ctx_wo on line: $sodistlinenum.");
						}
					}else{
						$servsaletrans = $servsaletransSet->getMbo();
						$enterdate = $servsaletrans->getString("enterdate");
						Super::sendJsonResponse("error", "This Line: $solinenum cannot be deleted because it is linked to the Service Receipt Transaction on date: $enterdate");
					}
				}else{
					$matsaletrans = $matsaletransSet->getMbo();
					$enterdate = $servsaletrans->getString("enterdate");
					Super::sendJsonResponse("error", "This Line: $solinenum cannot be deleted because it is linked to the Naterial Receipt Transaction on date: $enterdate");
				}
			}
		}
		elseif ($childrentable == "sopayment") {
			foreach($selected_rows as $row){
				$sopaymentid = $row["sopaymentid"];
				$sopayment = (new Set())->useTransactionManager()->MboSetRemote("sopayment")->where("sopaymentid=?", $sopaymentid)
				->MboRemote("sonum, solinenum")->getMboSet()->getMbo();

				$solinenum = $sopayment->getString("solinenum");
				$sonum = $sopayment->getString("sonum");
				$invoicenum = $sopayment->getString("invoicenum");
				Super::sendJsonResponse("error", "This Line: $solinenum cannot be deleted because it is linked to the payment: $invoicenum.");
			}
		}
		elseif ($childrentable == "relatedworkorder") {
			Super::sendJsonResponse("error", "Related Work Order Records cannot be deleted.");	
		}
		elseif ($childrentable == "soshipment") {
			Super::sendJsonResponse("error", "Shipment Records cannot be deleted.");	
		}
		elseif ($childrentable == "sotracking") {
			Super::sendJsonResponse("error", "Tracking Records cannot be deleted.");	
		}
	}
}
	

?>