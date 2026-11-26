<?php
require_once ("../../../eat.php");

Class ActionSet {
	public static function getContractChartData() {
		try {
			$payload = Super::getParam("getContractChartData");
			$contractnum = $payload["contractnum"];

			// 1. CARI ROOT CONTRACT (Leluhur Paling Awal)
			// Kita mundur dulu sampai ketemu Rev 0
			$rootContract = $contractnum;
			$loop = 0;
			while($loop < 20) {
				$prev = (new Set())->useTransactionManager()
					->MboSetRemote("purchcontract")
					->where("contractnum=?", $rootContract)
					->MboRemote("prevcontractnum")
					->getMboSet()->fetch();
				
				if(!empty($prev['prevcontractnum'])) {
					$rootContract = $prev['prevcontractnum'];
				} else {
					break;
				}
				$loop++;
			}

			// 2. CARI SEMUA KETURUNAN DARI ROOT (Family Tree)
			$familyTree = [];
			// Ambil Root Data
			$rootData = (new Set())->useTransactionManager()->MboSetRemote("purchcontract")->where("contractnum=?", $rootContract)->getMboSet()->fetch();
			$familyTree[] = $rootData;
			
			// Cari anak-anaknya secara berurutan
			$currentParent = $rootContract;
			while(true) {
				$child = (new Set())->useTransactionManager()
					->MboSetRemote("purchcontract")
					->where("prevcontractnum=?", $currentParent)
					->getMboSet()->fetch();
				
				if($child) {
					$familyTree[] = $child;
					$currentParent = $child['contractnum'];
				} else {
					break;
				}
			}

			// 3. BUILD CHART DATA
			$categories = []; // Sumbu X (Nomor Kontrak / Rev)
			$limits = [];     // Data Plafon
			$spents = [];     // Data Terpakai
			$poDetails = [];  // Data Tooltip

			foreach ($familyTree as $c) {
				$cNum = $c['contractnum'];
				$rev  = $c['revisionnum'];
				$limit = (float)$c['totalcost'];
				
				// Hitung PO khusus untuk contractnum ini
				// Pastikan kolom 'status' ikut diambil di SELECT (atau gunakan MboRemote * jika default)
				$poList = (new Set())->useTransactionManager()
					->MboSetRemote("po")
					->where("contractnum=? AND status NOT IN ('cancel','draft')", $cNum)
					->getMboSet()->fetchData();

				$totalPO = 0;
				$poListFormatted = [];
				
				foreach($poList as $po) {
					$cost = (float)$po['totalbasecost'];
					$status = strtoupper($po['status']); // Ambil Status
					$totalPO += $cost;
					// Format: PO-XXX [STATUS]: 1,000,000
					// Saya tambahkan styling span kecil agar status terlihat rapi
					$poListFormatted[] = $po['ponum'] . " <span style='font-size:10px; color:blue;'>[" . $status . "]</span>: " . number_format($cost);
				}

				// Label X-Axis
				$label = $cNum . "\n(Rev $rev)";
				
				$categories[] = $label;
				$limits[] = $limit;
				$spents[] = [
					'value' => $totalPO,
					'po_list' => implode('<br/>', $poListFormatted) // Simpan buat tooltip
				];
				
				// Logic Visual: Warna Bar
				// Jika spent > limit -> Merah, Jika aman -> Hijau
				// (Ditangani di JS formatter)
			}

			Super::sendJsonResponse("success", "Chart Data Loaded", [
				"categories" => $categories,
				"limits" => $limits,
				"spents" => $spents
			]);

		} catch (Exception $e) {
			Super::sendJsonResponse("error", $e->getMessage());
		}
	}
	public static function checkAmountSpt() {
		try {
			$payload = Super::getParam("checkAmountSpt");
			$contractnum = $payload["contractnum"];

			if (empty($contractnum)) {
				throw new Exception("Contract Number is required.");
			}

			// =================
			// 1. TRACE BACK LINEAGE (Cari Silsilah Kontrak)
			// =================
			
			$relatedContracts = [];
			$relatedContracts[] = $contractnum; // Masukkan kontrak saat ini
			
			$currentSearch = $contractnum;
			$loopGuard = 0;

			// Cek apakah kontrak ini punya bapak/kakek (prevcontractnum)
			while (true) {
				// Safety guard biar gak infinite loop
				if ($loopGuard > 20) break; 

				$prevData = (new Set())->useTransactionManager()
					->MboSetRemote("purchcontract")
					->where("contractnum = ?", $currentSearch)
					->MboRemote("prevcontractnum") // Hanya ambil kolom ini
					->getMboSet()
					->fetch();
				
				if (!empty($prevData['prevcontractnum'])) {
					$prev = $prevData['prevcontractnum'];
					
					// Cek duplicate biar gak muter
					if (in_array($prev, $relatedContracts)) break;

					$relatedContracts[] = $prev;
					$currentSearch = $prev; // Set pointer untuk loop berikutnya
				} else {
					break; // Tidak punya ancestor lagi, stop.
				}
				$loopGuard++;
			}

			// $relatedContracts sekarang berisi: ['CON-001-REV-1', 'CON-001']

			// =================
			// 2. HITUNG TOTAL PO DARI SELURUH KONTRAK
			// =================
			
			$placeholders = implode(',', array_fill(0, count($relatedContracts), '?'));
			
			
			$poSet = (new Set())->useTransactionManager()
				->MboSetRemote("po")
				->whereIn("contractnum", $relatedContracts) 
				->where("status NOT IN ('cancel')") 
				->MboRemote("SUM(totalbasecost) as total_spent") 
				->getMboSet();

			$result = $poSet->fetch();
			$amountSpent = isset($result['total_spent']) ? (float)$result['total_spent'] : 0.00;

			// =================
			// 3. UPDATE NILAI (Hanya ke Kontrak yang sedang dibuka)
			// =================
			(new Set())->useTransactionManager()
				->MboSetRemote("purchcontract")
				->where("contractnum = ?", $contractnum)
				->setValue("amountspent", $amountSpent)
				->change();

			Super::sendJsonResponse("success", "Calculated successfully including history", [
				"amountspent" => $amountSpent,
				"contractnum" => $contractnum,
				"history_trace" => $relatedContracts // Debugging info
			]);

		} catch (Exception $e) {
			Super::sendJsonResponse("error", $e->getMessage());
		}
	}
	public static function releasePO() {
		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();

		try {
			// 1. Ambil Payload
			$payload = Super::getParam("releasePO");
			$new_ponum   = $payload["new_ponum"];
			$contractnum = $payload["contractnum"];
			$user        = Xbo::getLoginUser();
			$currentDate = Xbo::setEnterDate();
			$orgInfo     = Xbo::getOrgcode($user);

			if($new_ponum== "" || empty($new_ponum)){
				throw new Exception("New PO Number is required.");
			}

			// 2. Validasi & Ambil Data Contract Header
			$contractSet = (new Set())->useTransactionManager()
				->MboSetRemote("purchcontract")
				->where("contractnum=?", $contractnum)
				->getMboSet()
				->fetch();

			if (!$contractSet) {
				throw new Exception("Contract $contractnum not found.");
			}

			// Validasi Status: Hanya APPR yang bisa Release PO
			if (strtolower($contractSet['status']) != 'appr') {
				throw new Exception("Cannot release PO. Contract status must be APPR (Current: " . $contractSet['status'] . ")");
			}

			// Validasi Expired
			if (strtotime($contractSet['enddate']) < time()) {
				throw new Exception("Cannot release PO. Contract has expired on " . $contractSet['enddate']);
			}

			// -------------------------------------------------------------
			// [NEW] VALIDASI PLAFON / BUDGET
			// -------------------------------------------------------------
			$limit = (float)$contractSet['totalcost'];
			$used  = (float)$contractSet['amountspent'];

			// Cek hanya jika limit diset (> 0).
			// Toleransi 0.00: Jika Used >= Limit, stop.
			if ($limit > 0 && $used >= $limit) {
				// Format angka agar pesan error enak dibaca
				$limitStr = number_format($limit, 2);
				$usedStr  = number_format($used, 2);
				
				throw new Exception("Cannot release PO. Contract Budget is fully utilized (Exhausted). Limit: $limitStr, Used: $usedStr. Please create a Revision to increase the budget.");
			}
			// -------------------------------------------------------------


			// 3. PREPARE DATA HEADER PO
			$vendorInfo = (new Set())->useTransactionManager()
				->MboSetRemote("vendor") // Pastikan nama tabel vendor benar (companies/vendor)
				->where("vendorcode=?", $contractSet['vendorcode'])
				->getMboSet()
				->fetch();
				
			$poHeader = [
				"ponum"         => $new_ponum,
				"description"   => "Release PO for Contract " . $contractnum,
				"contractnum"   => $contractnum,
				"vendor"        => $contractSet['vendorcode'],
				"status"        => "new", 
				"statusdate"    => $currentDate,
				"orderdate"     => $currentDate,
				"priority"      => "normal",
				"receipt"       => "NONE",
				"currencycode"  => $contractSet['currencycode'],
				"exchangerate"  => 1,
				"buyer"         => $contractSet['buyer'],
				"enterby"       => $user,
				"enterdate"     => $currentDate,
				"changeby"      => $user,
				"changedate"    => $currentDate,
				"orgcode"       => $contractSet['orgcode'] ?? $orgInfo->orgcode,
				"sitecode"      => $contractSet['sitecode'] ?? $orgInfo->sitecode,
				"totalcost"     => 0,
				"unpaidcost"    => 0,
				"budgetspan"    => 1
			];

			// Insert Header PO
			(new Set())->useTransactionManager()->MboSetRemote("po")->addStream([$poHeader]);


			// 4. PREPARE DATA LINES (Hanya jika PRICE Contract)
			if ($contractSet['contracttype'] == 'PRICE') {
				
				$contractLines = (new Set())->useTransactionManager()
					->MboSetRemote("contractline")
					->where("contractnum=?", $contractnum)
					->getMboSet()
					->fetchData();

				$poLines = [];
				if (!empty($contractLines)) {
					foreach ($contractLines as $cline) {
						
						$gldebit = null;
						$glcredit = null;
						
						// Placeholder Logic GL Account
						if (strtolower($cline['linetype']) == 'item' && !empty($cline['itemnum'])) {
							// $gldebit = ...
						}

						$poLines[] = [
							"ponum"          => $new_ponum,
							"polinenum"      => $cline['contractlinenum'],
							"linetype"       => $cline['linetype'],
							"itemnum"        => $cline['itemnum'],
							"serviceitemnum" => $cline['serviceitemnum'],
							"description"    => $cline['description'],
							"orderunit"      => $cline['orderunit'],
							"unitcost"       => $cline['unitcost'], 
							"tax"            => $cline['taxcode'],
							"orderqty"       => 0, 
							"linecost"       => 0,
							"taxcost"        => 0,
							"conversionfactor" => 1,
							"conditiontype"  => "NEW",
							"receiptstatus"  => "NONE",
							"gldebitacct"    => $gldebit,
							"glcreditacct"   => $glcredit,
							"enterby"        => $user,
							"enterdate"      => $currentDate,
							"changeby"       => $user,
							"changedate"     => $currentDate
						];
					}
					// Insert Lines
					(new Set())->useTransactionManager()->MboSetRemote("poline")->addStream($poLines);
				}
			}

			// 5. COPY TERMS (ToC & ToP)
			
			// A. Terms & Conditions
			$cTerms = (new Set())->useTransactionManager()
				->MboSetRemote("contractterm")
				->where("contractnum=?", $contractnum)
				->getMboSet()
				->fetchData();
			
			$poTerms = [];
			if (!empty($cTerms)) {
				foreach ($cTerms as $ct) {
					$poTerms[] = [
						"ponum"       => $new_ponum,
						"termlinenum" => $ct['termlinenum'],
						"termcode"    => $ct['termcode'],
						"description" => $ct['description'],
						"enterby"     => $user,
						"enterdate"   => $currentDate,
						"changeby"    => $user,
						"changedate"  => $currentDate
					];
				}
				(new Set())->useTransactionManager()->MboSetRemote("poterm")->addStream($poTerms);
			}

			// B. Payment Terms
			$cPayTerms = (new Set())->useTransactionManager()
				->MboSetRemote("contractterm2")
				->where("contractnum=?", $contractnum)
				->getMboSet()
				->fetchData();

			$poPayTerms = [];
			if (!empty($cPayTerms)) {
				foreach ($cPayTerms as $cpt) {
					$poPayTerms[] = [
						"ponum"       => $new_ponum,
						"termlinenum" => $cpt['termlinenum'],
						"termcode"    => $cpt['termcode'],
						"remarks"     => $cpt['remarks'],
						"description" => $cpt['description'],
						"enterby"     => $user,
						"enterdate"   => $currentDate,
						"changeby"    => $user,
						"changedate"  => $currentDate
					];
				}
				(new Set())->useTransactionManager()->MboSetRemote("poterm2")->addStream($poPayTerms);
			}

			// COMMIT ALL
			$tm->commit();
			Super::sendJsonResponse("success", "PO: $new_ponum created successfully from Contract $contractnum.", [
				"ponum" => $new_ponum
			]);

		} catch (Exception $e) {
			$tm->rollback();
			Super::sendJsonResponse("error", "Failed to release PO: " . $e->getMessage());
		}
	}
	public static function getPONumber(){
		$getAutoNum = new Super();
		$new_ponum = $getAutoNum->getAutoNumber("po", "update_next_value");
		Super::sendJsonResponse("success", "PO Number retrieved", ["new_ponum"=>$new_ponum]);
	}
	public static function createRevision() {
		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();

		try {
			// 1. Ambil Parameter
			$payload = Super::getParam("createRevision");
			$oldContractNum = $payload["contractnum"];
			$newContractNum = $payload["new_contract_number"];
			$user = Xbo::getLoginUser();
			$currentDate = Xbo::setEnterDate();

			// 2. Validasi Input Dasar
			if ($oldContractNum == $newContractNum) {
				throw new Exception("New Contract Number cannot be the same as the Old Contract Number.");
			}

			// 3. Validasi Kontrak Lama (Source)
			$oldContractSet = (new Set())->useTransactionManager()
				->MboSetRemote("purchcontract")
				->where("contractnum=?", $oldContractNum)
				->getMboSet();

			if ($oldContractSet->isEmpty()) {
				throw new Exception("Original contract not found.");
			}

			$oldHeader = $oldContractSet->fetch();
			$currentStatus = strtolower($oldHeader["status"]);

			// Hanya boleh revisi jika status APPR atau SUSPEND
			if ($currentStatus != "appr" && $currentStatus != "suspend") {
				throw new Exception("Only Active (APPR) or Suspended contracts can be revised. Current status: " . strtoupper($currentStatus));
			}

			// 4. Validasi Kontrak Baru (Target) - Harus belum ada
			$checkNew = (new Set())->useTransactionManager()
				->MboSetRemote("purchcontract")
				->where("contractnum=?", $newContractNum)
				->countRecords();

			if ($checkNew > 0) {
				throw new Exception("Contract Number $newContractNum already exists. Please use a unique number.");
			}

			// -------------------------------------------------
			// PROSES COPY DATA
			// -------------------------------------------------

			// A. COPY HEADER
			// Siapkan array data baru dari data lama
			$newHeaderData = $oldHeader;
			
			// Override field penting
			unset($newHeaderData["purchcontractid"]); // Hapus Auto Increment ID lama
			$newHeaderData["contractnum"]     = $newContractNum;
			$newHeaderData["prevcontractnum"] = $oldContractNum; // Link ke masa lalu
			$newHeaderData["status"]          = "DRAFT";
			$newHeaderData["revisionnum"]     = 0; // Mulai dari 0 lagi karena nomor baru
			$newHeaderData["amountspent"]     = $oldHeader["amountspent"]; // Bawa saldo terpakai
			$newHeaderData["enterby"]         = $user;
			$newHeaderData["enterdate"]       = $currentDate;
			$newHeaderData["changeby"]        = $user;
			$newHeaderData["changedate"]      = $currentDate;
			$newHeaderData["description"]     = $oldHeader["description"] . " (Rev)"; // Optional marker

			// Insert Header Baru
			(new Set())->useTransactionManager()->MboSetRemote("purchcontract")->addStream([$newHeaderData]);

			// Update Header Lama -> REVISED
			(new Set())->useTransactionManager()
				->MboSetRemote("purchcontract")
				->where("contractnum=?", $oldContractNum)
				->setValue("status", "REVISED")
				->setValue("changeby", $user)
				->setValue("changedate", $currentDate)
				->change();

			// B. COPY CONTRACT LINES
			$oldLines = (new Set())->useTransactionManager()
				->MboSetRemote("contractline")
				->where("contractnum=?", $oldContractNum)
				->getMboSet()
				->fetchData();

			$newLines = [];
			if (!empty($oldLines)) {
				foreach ($oldLines as $line) {
					unset($line["contractlineid"]); // Hapus PK
					$line["contractnum"] = $newContractNum; // Ganti FK
					$line["enterby"]     = $user;
					$line["enterdate"]   = $currentDate;
					$line["changeby"]    = $user;
					$line["changedate"]  = $currentDate;
					$newLines[] = $line;
				}
				(new Set())->useTransactionManager()->MboSetRemote("contractline")->addStream($newLines);
			}

			// C. COPY PRICING (Strict Logic: contractnum + contractlinenum)
			// Asumsi: tabel contractprice memiliki kolom 'contractnum' dan 'contractlinenum' sesuai instruksi relasi.
			$oldPrices = (new Set())->useTransactionManager()
				->MboSetRemote("contractprice")
				->where("contractnum=?", $oldContractNum)
				->getMboSet()
				->fetchData();

			$newPrices = [];
			if (!empty($oldPrices)) {
				foreach ($oldPrices as $price) {
					unset($price["contractpriceid"]); // Hapus PK
					// Perhatikan: Kita TIDAK pakai contractlineid.
					// Kita update contractnum-nya saja. contractlinenum tetap sama.
					$price["contractnum"] = $newContractNum; 
					$price["enterby"]     = $user;
					$price["enterdate"]   = $currentDate;
					
					$newPrices[] = $price;
				}
				(new Set())->useTransactionManager()->MboSetRemote("contractprice")->addStream($newPrices);
			}

			// D. COPY CONTRACT TERMS (ToC)
			$oldTerms = (new Set())->useTransactionManager()
				->MboSetRemote("contractterm")
				->where("contractnum=?", $oldContractNum)
				->getMboSet()
				->fetchData();

			$newTerms = [];
			if (!empty($oldTerms)) {
				foreach ($oldTerms as $term) {
					unset($term["contracttermid"]); // Hapus PK
					$term["contractnum"] = $newContractNum;
					$term["enterby"]     = $user;
					$term["enterdate"]   = $currentDate;
					$term["changeby"]    = $user;
					$term["changedate"]  = $currentDate;
					$newTerms[] = $term;
				}
				(new Set())->useTransactionManager()->MboSetRemote("contractterm")->addStream($newTerms);
			}

			// E. COPY PAYMENT TERMS (ToP)
			$oldPayTerms = (new Set())->useTransactionManager()
				->MboSetRemote("contractterm2")
				->where("contractnum=?", $oldContractNum)
				->getMboSet()
				->fetchData();

			$newPayTerms = [];
			if (!empty($oldPayTerms)) {
				foreach ($oldPayTerms as $pTerm) {
					unset($pTerm["contractterm2id"]); // Hapus PK
					$pTerm["contractnum"] = $newContractNum;
					$pTerm["enterby"]     = $user;
					$pTerm["enterdate"]   = $currentDate;
					$pTerm["changeby"]    = $user;
					$pTerm["changedate"]  = $currentDate;
					$newPayTerms[] = $pTerm;
				}
				(new Set())->useTransactionManager()->MboSetRemote("contractterm2")->addStream($newPayTerms);
			}

			// COMMIT
			$tm->commit();
			Super::sendJsonResponse("success", "Revision Created Successfully. Old Contract set to REVISED.", [
				"new_contractnum" => $newContractNum
			]);

		} catch (Exception $e) {
			$tm->rollback();
			Super::sendJsonResponse("error", "Failed to create revision: " . $e->getMessage());
		}
	}
	public static function validateStatusChange() {
		$payload = Super::getParam("validateStatusChange");
		$contractnum = $payload["contractnum"];
		$new_status = strtolower($payload["new_status"]);
		
		// 1. AMBIL HEADER & FETCH DATA PENTING
		$headerSet = (new Set())->useTransactionManager()
			->MboSetRemote("purchcontract")
			->where("contractnum = ?", $contractnum)
			->getMboSet();
		
		if ($headerSet->isEmpty()) {
			Super::sendJsonResponse("error", "Contract not found.");
		}

		$headerData = $headerSet->fetch();
		$current_status = strtolower($headerData['status']);
		$contractType = $headerData['contracttype'];
		$totalCost = (float)$headerData['totalcost'];
		
		// Field untuk Validasi Tanggal
		$startDate = $headerData['startdate'];
		$endDate = $headerData['enddate'];
		$prevContractNum = $headerData['prevcontractnum']; // Cek apakah ini revisi

		// 2. VALIDASI MATRIX TRANSISI (HARAM ROLLBACK) -- (Kode sama seperti sebelumnya) --
		if ($current_status == 'appr' && ($new_status == 'draft' || $new_status == 'wappr')) {
			Super::sendJsonResponse("error", "Action Denied: Cannot revert Approved contract to Draft/Waiting Approval. Please use 'Create Revision' action.");
		}
		if ($current_status == 'revised' || $current_status == 'close' || $current_status == 'canceled') {
			Super::sendJsonResponse("error", "Action Denied: Status is final ($current_status). No changes allowed.");
		}

		// 3. CEK ACTIVE PO -- (Kode sama seperti sebelumnya) --
		$hasPonum = (new Set())->useTransactionManager()
			->MboSetRemote("po")
			->where("contractnum = ? AND status NOT IN ('canceled', 'close')", $contractnum)
			->getMboSet();

		if (!$hasPonum->isEmpty()) {
			if ($new_status == "canceled") Super::sendJsonResponse("error", "Cannot Cancel: Active POs exist.");
			if ($new_status == "draft") Super::sendJsonResponse("error", "Cannot revert to Draft: POs exist.");
		}

		// -------------------------------------------------
		// 4. VALIDASI KELENGKAPAN DATA (INTEGRITY CHECK)
		// -------------------------------------------------

		if ($new_status == "appr" || $new_status == "wappr") {
			if (strtotime($endDate) < time()) {
				Super::sendJsonResponse("error", "Validity Error: Cannot set status to " . strtoupper($new_status) . ". The Contract End Date ($endDate) is in the past (Expired). Please update the End Date first.");
			}
			// A. VALIDASI TANGGAL (BASIC)
			if (strtotime($endDate) < strtotime($startDate)) {
				Super::sendJsonResponse("error", "Invalid Dates: End Date cannot be earlier than Start Date.");
			}

			// B. VALIDASI TANGGAL REVISI (CRONOLOGY CHECK)
			// Jika ini adalah kontrak revisi (punya prevcontractnum)
			if (!empty($prevContractNum)) {
				$prevSet = (new Set())->useTransactionManager()
					->MboSetRemote("purchcontract")
					->where("contractnum = ?", $prevContractNum)
					->getMboSet();
				
				if (!$prevSet->isEmpty()) {
					$prevData = $prevSet->fetch();
					$prevStartDate = $prevData['startdate'];
					// Rule: Start Date Revisi TIDAK BOLEH lebih lampau dari Start Date Asli
					if (strtotime($startDate) < strtotime($prevStartDate)) {
						Super::sendJsonResponse("error", "Chronology Error: Revision Start Date ($startDate) cannot be earlier than Original Contract Start Date ($prevStartDate).");
					}
				}
			}

			// C. VALIDASI BLANKET
			if ($contractType == "BLANKET" && $totalCost <= 0) {
				Super::sendJsonResponse("error", "Cannot Approve: Blanket Contract must have a Total Cost limit greater than 0.");
			}

			// D. VALIDASI PRICE CONTRACT (LINES)
			if ($contractType == "PRICE") {
				$contractlineSet = (new Set())->useTransactionManager()
					->MboSetRemote("contractline")
					->where("contractnum = ?", $contractnum)
					->getMboSet();

				if ($contractlineSet->isEmpty()) {
					Super::sendJsonResponse("error", "Cannot Approve: Price Contract must have at least one Contract Line.");
				}

				$lines = $contractlineSet->fetchData(); 
				
				foreach ($lines as $line) {
					$linenum = $line['contractlinenum'];
					$linetype = strtolower($line['linetype']);
					$itemnum = $line['itemnum'];
					$serviceitemnum = $line['serviceitemnum'];
					
					if (($linetype == "item") && empty($itemnum)) {
						Super::sendJsonResponse("error", "Line $linenum: Item Number is mandatory for Item Line Type.");
					}
					if (($linetype == "services") && empty($serviceitemnum)) {
						Super::sendJsonResponse("error", "Line $linenum: Service Item is mandatory for Services Line Type.");
					}
				}
			}
		}

		Super::sendJsonResponse("success", "Safe to change Status");
	}	
	public static function GetServiceItemInfo(){
		$payload = Super::getParam("getServiceItemInfo");
		$serviceitemnum = $payload["serviceitemnum"];
		
		$set = new Set();

		$serviceitemSet = $set->useTransactionManager()->MboSetRemote("serviceitems")
		->where("serviceitemnum=?", $serviceitemnum)->MboRemote("description, costvolatilityrisk, leadtimerisk, complexityrisk, serviceunit, stdcost")->getMboSet();
		$serviceitem = $serviceitemSet->getMbo();
		$description = $serviceitem->getString("description");
		$stdcost = $serviceitem->getString("stdcost");
		$costvaolatilityrisk = $serviceitem->getString("costvolatilityrisk");
		$leadtimerisk = $serviceitem->getString("leadtimerisk");
		$complexityrisk = $serviceitem->getString("complexityrisk");
		$serviceunit = $serviceitem->getString("serviceunit");


		//calculate unitcost with risk factor
		$riskfactor = (1 + ($costvaolatilityrisk + $leadtimerisk + $complexityrisk) / 100);
		$unitcost = $stdcost * $riskfactor;
		//convert 2 decimal place
		$unitcost = number_format((float)$unitcost, 2, '.', '');
		Set::throws(["description"=>$description, "unitcost"=>$unitcost, "serviceunit"=>$serviceunit]);
	}
} 

Class ValidateTables {	
	
	public static function tbcx(){
		$payload = Super::getParam("tbcx");
		$data = $payload["dataPackage"];
		$childrentable = $data["datasetChild"];
		$selected_rows = $data["selectedRows"];
		
		foreach($selected_rows as $row){
			foreach($row as $key=>$value){
				
				if($childrentable == "contractline"){
					$contractline = (new Set())->MboSetRemote($childrentable)->setWhere("$key='$value'")->getMboSet()->getMbo();
					$contractnum = $contractline->getString("contractnum");
					$contractlinenum = $contractline->getString("contractlinenum");
					$hasPonum = (new Set())->MboSetRemote("po")->where("contractnum=?", $contractnum)->getMboSet();
					if($hasPonum->isEmpty()){
						$status = (new Set())->MboSetRemote("purchcontract")->setWhere("contractnum='$contractnum'")->getMboValue("status");
						$status = strtolower($status);
						if( $status == "draft" || $status == "revised" ){
							(new Set())->useTransactionManager()->MboSetRemote("contractprice")->where("contractnum=? AND contractlinenum=?", $contractnum, $contractlinenum)->remove();
							$_POST["delChildPackage"] = $data;
							Super::deleteChildren();
						}else{
							Super::sendJsonResponse("error", "Cannot delete Contract $contractnum because it has status: $status.");
						}
					}else{
						$ponum = $hasPonum->getMbo()->getString("ponum");
						Super::sendJsonResponse("error", "The CONTRACTLINE has PO Related Record: $ponum");
					}
				}
				
			}
		}
	}
}
	

?>