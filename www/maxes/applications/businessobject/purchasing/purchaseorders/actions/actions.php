<?php
require_once ("../../../eat.php");

Class POActionSet {
	public static function changePONumber()
	{
		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();

		try {
			$payload = Super::getParam("changePONumber");
			$oldPonum = $payload["ponum"];
			$newPonum = $payload["newponum"];

			if (empty($oldPonum) || empty($newPonum)) {
				throw new Exception("Old PO Number and New PO Number are required.");
			}

			$db = new dbo();
			$dbname = $db->dbname();
			$dbtype = $db->dbtype();
			
			// Kueri yang sudah difilter agar tidak menyentuh VIEW
			if ($dbtype === 'mysql') {
				$query = "SELECT c.TABLE_NAME 
						FROM INFORMATION_SCHEMA.COLUMNS c
						JOIN INFORMATION_SCHEMA.TABLES t ON c.TABLE_NAME = t.TABLE_NAME AND c.TABLE_SCHEMA = t.TABLE_SCHEMA
						WHERE c.TABLE_SCHEMA = '$dbname' AND c.COLUMN_NAME = 'ponum' AND t.TABLE_TYPE = 'BASE TABLE'";
			} elseif ($dbtype === 'sqlserver') {
				$query = "SELECT c.TABLE_NAME 
						FROM INFORMATION_SCHEMA.COLUMNS c
						JOIN INFORMATION_SCHEMA.TABLES t ON c.TABLE_NAME = t.TABLE_NAME
						WHERE c.TABLE_CATALOG = '$dbname' AND c.COLUMN_NAME = 'ponum' AND t.TABLE_TYPE = 'BASE TABLE'";
			}

			$res = Set::fetchAll($query);
			$tablesWithPoNum = array_column($res, 'TABLE_NAME');

			// Tambahkan Blacklist untuk jaga-jaga
			$blacklist = ['po_audit_log', 'system_logs']; 
			
			foreach ($tablesWithPoNum as $table) {
				if (in_array($table, $blacklist)) continue;

				(new Set())->useTransactionManager()
					->MboSetRemote($table)
					->where("ponum = ?", $oldPonum)
					->setValue("ponum", $newPonum)
					->change();
			}

			$changedate = Xbo::setEnterDate();
			$changeby = Xbo::getLoginUser();

			(new Set())->useTransactionManager()->MboSetRemote("po")->where("ponum=?", $newPonum)
			->setValue("changedate", $changedate)
			->setValue("changeby", $changeby)
			->change();
			
			$tm->commit();
			Super::sendJsonResponse("success", "PO Number changed successfully.");

		} catch (Exception $e) {
			$tm->rollback();
			Super::sendJsonResponse("error", "Failed: " . $e->getMessage());
		}
	}
	public static function getVendorPhone(){
		$payload = Super::getParam("getVendorPhone");
		$ponum = $payload["ponum"];

		$poSet = (new Set())->useTransactionManager()->MboSetRemote("po")->where("ponum=?", $ponum)->MboRemote("vendor, status")->getMboSet();

		$po = $poSet->getMbo();
		$vendor = $po->getString("vendor");
		$status = $po->getString("status");

		if($poSet->isEmpty()){
			Super::sendJsonResponse("error", "PO is not found. Please select PO first");
		}

		if(empty($vendor)){
			Super::sendJsonResponse("error", "Vendor has not been set!");
		}

		if($status != "sent"){
			Super::sendJsonResponse("error", "PO Status must be changed to SENT.");
		}
		
		
		

		//vendorPhone
		$vendorSet = (new Set())->useTransactionManager()->MboSetRemote("vendor")->where("vendorcode=?", $vendor)->getMboSet();

		if($vendorSet->isEmpty()){
			Super::sendJsonResponse("error", "Vendor is not found!");
		}

		$vend = $vendorSet->getMbo();
		
		$vendorPhone = $vend->getString("phone");
		$vendorName = $vend->getString("name");
		$contactPerson = $vend->getString("contact");
		
		if(empty($vendorPhone)){
			Super::sendJsonResponse("error", "Vendor Phone has not been set!");
		}
		
		$orgDescription = (new Set())->MboSetRemote("organization")->getMboValue("description");

		Super::sendJsonResponse("success", "Vendor Phone retrieved", [
			"vendorPhone"=>$vendorPhone, 
			"vendorName" => $vendorName,
			"contactPerson" => $contactPerson,
			"orgDescription" => $orgDescription
		]);
	}
	public static function GetServiceItemInfo(){
		$payload = Super::getParam("getServiceItemInfo");
		$serviceitemnum = $payload["serviceitemnum"];
		
		$set = new Set();

		$serviceitemSet = $set->MboSetRemote("serviceitems")->setWhere("serviceitemnum='$serviceitemnum'")->MboRemote("description, costvolatilityrisk, leadtimerisk, complexityrisk, serviceunit")->MboRemote("stdcost")->getMboSet();
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


	private static function validateStatusChange(string $ponum, string $newStatus)
	{
		
		$poData = (new Set())->useTransactionManager()
			->MboSetRemote("po")
			->where("ponum = ?", $ponum)
			->MboRemote("status, vendordate, orderdate, exchangerate, totalbasecost, vendor, buyer, currencycode, priority, contractnum")
			->getMboSet()
			->fetch();

		if (!$poData) {
			throw new Exception("Purchase Order '$ponum' not found.");
		}

		$currentStatus = $poData['status'];
		$contractnum =  $poData['contractnum'];
		$errors = []; 

		// ===============
		// ATURAN #1: Validasi Status Saat Ini
		// ===============
		if ($currentStatus === 'cancel') {
			throw new Exception("Cannot change status. PO '$ponum' has already been canceled.");
		}
		if ($currentStatus === 'sent') {
			throw new Exception("Cannot change status. PO '$ponum' has already been sent to the vendor.");
		}

		// ===============
		// ATURAN #2: Validasi Field Penting (jika mau di-approve)
		// ===============
	
		if (in_array($newStatus, ['approved', 'sent', 'wappr'])) {
			if (empty($poData['vendor'])) $errors[] = "Vendor must be filled.";
			if (empty($poData['buyer'])) $errors[] = "Buyer must be filled.";
			if (empty($poData['currencycode'])) $errors[] = "Currency Code must be filled.";
			if (empty($poData['priority'])) $errors[] = "Priority must be filled.";
			
			// Validasi angka
			if (!is_numeric($poData['exchangerate']) || (float)$poData['exchangerate'] <= 0) {
				$errors[] = "Exchange Rate must be a number greater than 0.";
			}
			if (!is_numeric($poData['totalbasecost']) || (float)$poData['totalbasecost'] <= 0) {
				$errors[] = "Total Cost must be greater than 0.";
			}
			
			// Validasi tanggal
			$today = (new DateTime())->setTime(0, 0);
			$orderDate = $poData['orderdate'] ? (new DateTime($poData['orderdate']))->setTime(0,0) : null;
			$vendorDate = $poData['vendordate'] ? (new DateTime($poData['vendordate']))->setTime(0,0) : null;
			
			if (!$orderDate) {
				$errors[] = "Order Date must be filled.";
			} elseif ($orderDate > $today) {
				$errors[] = "Order Date cannot be in the future.";
			}

			if (!$vendorDate) {
				$errors[] = "Vendor Date must be filled.";
			} elseif ($orderDate && $vendorDate < $orderDate) {
				$errors[] = "Vendor Date cannot be earlier than Order Date.";
			}

			// Check PO LINES 
			$polineSet = (new Set())->MboSetRemote("poline")
			->where("ponum=?", $ponum)
			->getMboSet(); // Ambil semua baris sebagai array
			
			$polines = [];
			while($poline = $polineSet->fetch()){
				$linetype = $poline["linetype"];
				$polinenum = $poline["polinenum"];
				if(empty($linetype)){
					$polines[] = "Line Type is required on PO Line Number $polinenum";
				}

				$gldebitacct = $poline["gldebitacct"];
				$glcreditacct = $poline["glcreditacct"];
				$description = $poline["description"];

				if($linetype == "item"){
					$warehouse = $poline["warehouse"];
					$itemnum = $poline["itemnum"];
					$conversionfactor = $poline["conversionfactor"];
				
					if(empty($warehouse)){
						$polines[] = "Warehouse is required on PO Line Number $polinenum";
					}
					if(empty($itemnum)){
						$polines[] = "Item Number is required on PO Line Number $polinenum";
					}
					if(empty($conversionfactor)){
						$polines[] = "Conversion Factor is required on PO Line Number $polinenum";
					}
					
					if($conversionfactor <= 0){

						$polines[] = "Conversion Factor must higher than 0 on PO Line Number $polinenum";
					}

				}else{
					//linetype = "services"
					$wonum = $poline["wonum"];
					if(empty($wonum)){
						$polines[] = "Work Order is required on PO Line Number $polinenum";
					}
				}

				if(empty($glcreditacct)){
					$polines[] = "GL Credit Account is required on PO Line Number $polinenum";
				}
				if(empty($gldebitacct)){
					$polines[] = "GL Debit Account is required on PO Line Number $polinenum";
				}
				if(empty($description)){
					$polines[] = "Description is required on PO Line Number $polinenum";
				}
			}

			if(!empty($polines)){
				$errorMessage = implode("<br>", $polines);
				Super::sendJsonResponse("error", $errorMessage);
			}
			// ---------------------------------
			//  BUDGET CHECK / PLAFON VALIDATION
			// ---------------------------------

			if (!empty($contractnum)) {
				// Panggil helper yang sudah kita buat.
				// Pastikan fungsi helper getContractBudgetStatus bisa diakses (public static atau self::)
				$budgetStatus = self::getContractBudgetStatus($contractnum, $ponum);
				
				$limit = (float)$budgetStatus['limit'];
				$spentPrevious = (float)$budgetStatus['spent']; // Spending dari PO lain yang sudah Approved
				$currentPOCost = (float)$poData['totalbasecost'];
				
				// Hitung Proyeksi Spending (Yang sudah ada + PO yang mau diapprove ini)
				$projectedSpending = $spentPrevious + $currentPOCost;

				// Cek Overbudget (Hanya jika limit > 0)
				if ($limit > 0 && $projectedSpending > $limit) {
					$limitStr = number_format($limit, 2);
					$usedStr = number_format($spentPrevious, 2);
					$currStr = number_format($currentPOCost, 2);
					$excess = number_format($projectedSpending - $limit, 2);

					$errors[] = "<strong>Overbudget Warning:</strong> Approval denied.<br>" .
								"Contract Limit: $limitStr<br>" .
								"Previously Used: $usedStr<br>" .
								"This PO Cost: $currStr<br>" .
								"Total Projected: " . number_format($projectedSpending, 2) . "<br>" .
								"Exceeds limit by: $excess";
				}
			}
		}

		
		if (!empty($errors)) {
			Super::sendJsonResponse("error", "Cannot approve PO '$ponum' due to missing or invalid data: <ul><li>" . implode('</li><li>', $errors) . '</li></ul>');
		}

		// Validasi Berdasarkan Transaksi Terkait
		
		if (in_array($newStatus, ['cancel', 'new',  'approved'])) {
			// Cek apakah sudah ada invoice terkait
			$invoiceCount = (new Set())->useTransactionManager()
				->MboSetRemote("invoicetrans")
				->where("ponum = ?", $ponum)
				->countRecords(); 

			if ($invoiceCount > 0) {
				Super::sendJsonResponse("error", "Cannot change status to '$newStatus'. PO '$ponum' already has related invoice transactions.");
			}
			
		}

		return true;
	}
	public static function createAnnualBudgetAlloc() {
		$ponum = $_POST["ponum"];
		$budgetnum = $_POST["budgetnum"];
		$budgetspan = (int)$_POST["createAnnualBudgetAlloc"];  // Pastikan integer
		$new_status = $_POST["new_status"];
		
		//VALIDATE STATUS CHANGE();

		self::validateStatusChange($ponum, $new_status);


		if(!empty($budgetnum)){
			$polines = (new Set())->MboSetRemote("poline")
			->where("ponum=?", $ponum)
			->MboRemote("polinenum, budgetlinenum") // Minta kolom yang kita butuhkan
			->getMboSet()
			->fetchData(); // Ambil semua baris sebagai array

			if (empty($polines)) {
				Super::sendJsonResponse("error", "This PO has no lines.");
				return;
			}

			$nullBudgetLines = [];
			$budgetChecks = []; 

			foreach ($polines as $line) {
				// Cek budgetlinenum yang kosong
				if (empty($line["budgetlinenum"])) {
					$context = "<li>Budget Line Number on PO Line {$line['polinenum']} is not set.</li>";
					$nullBudgetLines[] = $context;
				} else {
					// Kumpulkan budgetnum dan budgetlinenum yang untuk divalidasi nanti
					$unique_key = $budgetnum . "-" . $line["budgetlinenum"];
					if (!isset($budgetChecks[$unique_key])) {
						$budgetChecks[$unique_key] = [
							"budgetnum" => $budgetnum,
							"budgetlinenum" => $line["budgetlinenum"]
						];
					}
				}
			}

			// Jika ada budgetlinenum yang kosong, langsung hentikan proses.
			if (!empty($nullBudgetLines)) {
				$errorMessage = "<ul>".implode("", $nullBudgetLines). "</ul>";
				Super::sendJsonResponse("error", $errorMessage);
				return;
			}

			// Cek apakah budget ini berhubungan dengan kontrak vendor.
			$budgetData = (new Set())->useTransactionManager()->MboSetRemote("budget")
				->where("budgetnum=?", $budgetnum)
				->MboRemote("vendorcode") 
				->getMboSet()->getMbo();

			$budget_vendorcode = $budgetData ? $budgetData->getString("vendorcode") : null;

			// Hanya lanjutkan jika budget ini memang spesifik untuk satu vendor
			if(!empty($budget_vendorcode)) {
				
				// Ambil PR Vendor untuk dibandingkan.
				$po_vendorcode = (new Set())->useTransactionManager()->MboSetRemote("po")
					->where("ponum=?", $ponum)
					->getMboValue("vendor");

				// Normalisasi huruf
				$clean_budget_vendor = strtolower(trim($budget_vendorcode));
				$clean_pr_vendor = strtolower(trim($po_vendorcode));

				if($clean_budget_vendor !== $clean_pr_vendor) {
					
					$errorMessage = "This PO cannot be approved. The selected budget ($budgetnum) is specifically allocated for vendor '{$budget_vendorcode}', 
					but this PO is for vendor '{$po_vendorcode}'. Please use a different budget or change the vendor on the PR.";
					
					Super::sendJsonResponse("error", $errorMessage);
				}
			}


			// Validasi untuk setiap budget line
			$budgetExceededErrors = [];
			foreach ($budgetChecks as $check) {
				$budgetnum = $check["budgetnum"];
				$budgetlinenum = $check["budgetlinenum"];

				$isExceeds = (new Set())->useTransactionManager()->MboSetRemote("budget")->where("budgetnum=?", $budgetnum)->getMboValue("isexceeds");
				
				if ($isExceeds == "Y") {
					// Ambil sisa budget SAAT INI dari view
					$current_remaining = (new Set())->useTransactionManager()->MboSetRemote("budgetline_summary")
						->where("budgetnum=?", $budgetnum)
						->where("budgetlinenum=?", $budgetlinenum) 
						->getMboValue("remaining_amount", 0);

					// Ambil BIAYA dari PO yang sedang divalidasi
					$cost_of_this_po_line = (new Set())->useTransactionManager()->MboSetRemote("poline")
						->where("ponum=?", $ponum)
						->where("budgetlinenum=?", $budgetlinenum)
						->MboRemote("SUM(linecost) as totalcost")
						->getMboValue("totalcost", 0);

					// Hitung sisa budget HIPOTETIS
					$hypothetical_remaining = $current_remaining - $cost_of_this_po_line;

					// Lakukan pengecekan pada nilai HIPOTETIS
					if ($hypothetical_remaining < 0) { 
						$remaining_amount_formatted = number_format($hypothetical_remaining, 2, '.', ',');
						$budgetExceededErrors[] = "<li>Budget line {$budgetnum}-{$budgetlinenum} would be exceeded. Remaining balance would be {$remaining_amount_formatted}.</li>";
					}
				}
			}

			if (!empty($budgetExceededErrors)) {
				(new Set())->useTransactionManager()->MboSetRemote("po")->where("ponum=?", $ponum)
				->setValue("isbudgetexceeds", "Y")
				 ->setValue("status", "hold") 
				->change();
				$errorMessage = "Cannot Approve PO. The PO has been placed on HOLD due to insufficient budget. It will need to be re-validated after the budget is adjusted. Issues found: <ul>" . implode("", $budgetExceededErrors) . "</ul>";
				Super::sendJsonResponse("notice", $errorMessage);
			}

		}
		
		//Jika semua validasi budget berhasil Lolos...

		(new Set())->useTransactionManager()->MboSetRemote("po")->where("ponum=?", $ponum)->setValue("isbudgetexceeds", "N")->change();
		$budgetSet = (new Set())->MboSetRemote("budget")->setWhere("budgetnum='$budgetnum'")->getMboSet();
		
		$budget = $budgetSet->getMbo();
		$budgetperiod = $budget->getString("budgetperiod"); //Asumsi format tahun adalah 'YYYY'
		$budgetyear = (int)$budgetperiod; //Konversi budgetperiod ke integer
		$enterdate = Xbo::setEnterDate();
		$enterby = Xbo::getLoginUser();

		$set = new Set();
		$polineSet = $set->MboSetRemote("poline")->setWhere("ponum='$ponum'")->getMboSet();

		if ($polineSet->isEmpty()) {
			Super::sendJsonResponse("error", "No POLINE found for PONUM '$ponum'.");
			return;
		}

		$counts = $polineSet->counts();
		//budget alloc hanya yang change status ke approve atau sent
		
		if($new_status == "approved" || $new_status == "sent"){
			for ($i = 0; $i < $counts; $i++) {
				$poline = $polineSet->getMbo(); 
				$budgetlinenum = $poline->getString("budgetlinenum");
				$linecost = $poline->getString("linecost");
				$taxcost = $poline->getString("taxcost");
				$polinenum = $poline->getInt("polinenum"); 
				$allocatedamount = floatval($linecost);		
				$spanAllocatedAmount = round($allocatedamount / $budgetspan, 2); // Pembagian merata, 2 desimal

				// Buat annualbudgetalloc untuk setiap tahun dalam budgetspan
				for ($j = 0; $j < $budgetspan; $j++) {
					$currentBudgetYear = $budgetyear + $j; // Tahun anggaran saat ini
					$status = ($j == 0) ? "active" : "planned"; // Tahun pertama active, sisanya planned

					$annualbudgetallocnum = "$ponum-$polinenum-$budgetspan-$currentBudgetYear"; // Tambah tahun
					//Atur budgetnum dan budgetlineNum ke NULL untuk tahun-tahun mendatang
					$currentBudgetNum = ($j == 0) ? $budgetnum : null;
					$currentBudgetlineNum = ($j == 0) ? $budgetlinenum : null;
					//Check Annnual Budget Allocation before insert..

					$annualBudgetAllocSet = (new Set())->useTransactionManager()->MboSetRemote("annualbudgetalloc")->where("annualbudgetallocnum=? and ponum=?", $annualbudgetallocnum, $ponum)->getMboSet();
					if(!$annualBudgetAllocSet->isEmpty()){
						continue;
					}
					$setInsert = new Set();
					$insertMboSet = $setInsert->MboSetRemote("annualbudgetalloc");
						$insertMboSet->setValue("annualbudgetallocnum", $annualbudgetallocnum);
						$insertMboSet->setValue("ponum", $ponum);
						$insertMboSet->setValue("polinenum", $polinenum);
						$insertMboSet->setValue("budgetyear", $currentBudgetYear);
						$insertMboSet->setValue("allocatedamount", $spanAllocatedAmount);
						$insertMboSet->setValue("actualexpense", 0);
						$insertMboSet->setValue("remainingallocation", $spanAllocatedAmount); // Awal = allocatedamount
						$insertMboSet->setValue("budgetnum", $currentBudgetNum);
						$insertMboSet->setValue("budgetlineNum", $currentBudgetlineNum);
						$insertMboSet->setValue("status", $status);
						$insertMboSet->setValue("statusdate", $enterdate);
						$insertMboSet->setValue("enterdate", $enterdate);
						$insertMboSet->setValue("enterby", $enterby);
						$insertMboSet->add();
				}
			}
			Super::sendJsonResponse("success", "Annual Budget Allocation created successfully for $counts POLINE(s) * $budgetspan year(s).");
		}else{
			Super::sendJsonResponse("success", "PO :$ponum has been changed to status : $new_status");
		}
		
		
	}
	public static function addadditionalPORequest(){
		$invoicenum = $_POST["addadditionalPORequest"];
		$ponum = $_POST["ponum"];
		$linetype = $_POST["po_linetype"];
		$itemnum = $_POST["po_itemnum"];
		$description = $_POST["description"];
		$unitcost = $_POST["unitcost"];
		$linecost = $_POST["linecost"];
		$taxcost = $_POST["taxcost"];
		$tax = $_POST["tax"];
		$orderqty = $_POST["orderqty"];
		$orderunit = $_POST["orderunit"];
		$conversionfactor = $_POST["conversionfactor"];
		$partnum = $_POST["partnum"];
		$category = $_POST["category"];
		$catalog = $_POST["catalog"];
		$warehouse = $_POST["warehouse"];
		$wonum = $_POST["wonum"];
		$requireddate = $_POST["requireddate"];
		$glcreditacct = $_POST["glcreditacct"];
		$gldebitacct = $_POST["gldebitacct"];
		$receiptstatus = "NONE";
		
		$polinenum = Xbo::getLineNumber("poline", "polinenum", "ponum='$ponum'");
		$set = new Set();
		$POSet = $set->MboSetRemote("po")->setwhere("ponum='$ponum'")->getMboSet();
		$poCount = $POSet->counts();
		$enterdate = Xbo::setEnterDate();
		$enterby = Xbo::getLoginUser();
		$changeby = $enterby;
		$changedate = $enterdate;
		if($poCount != 0){
			$set = new Set();
			$set->MboSetRemote("poline")->setValue("ponum", $ponum)->setValue("polinenum", $polinenum)->setValue("linetype", $linetype)
			->setValue("itemnum", $itemnum)->setValue("description", $description)->setValue("unitcost", $unitcost)
			->setValue("linecost", $linecost)->setValue("taxcost", $taxcost)->setValue("tax", $tax)
			->setValue("orderqty", $orderqty)->setValue("orderunit", $orderunit)->setValue("conversionfactor", $conversionfactor)
			->setValue("warehouse", $warehouse)->setValue("wonum", $wonum)->setValue("glcreditacct", $glcreditacct)->setValue("gldebitacct", $gldebitacct)
			->setValue("receiptstatus", $receiptstatus)->setValue("enterdate", $enterdate)->setValue("enterby", $enterby)
			->setValue("changeby", $changeby)->setValue("changedate", $changedate)->add();
			
			$msg = "added";
			POActionSet::SumPOCostBackend($ponum);
		}else{
			$msg = "ponumnotfound";
		}
		
		$ss= new stdClass;
		$ss->msg = $msg;
		Set::throws($ss);
	}
	public static function GetGLCreditAcct(){
		$vendor = $_POST["getglcreditacct"];
		$set = new Set();
		$POSet = $set->MboSetRemote("vendor")->setWhere("vendorcode='$vendor'")->MboRemote("rbniacct")->getMboSet();
		$PO = $POSet->getMbo();
		
		$rbniacct = $PO->getString("rbniacct");
		Set::throws(["msg"=>$rbniacct]);
	}

	
	// Helper untuk validasi Budget Kontrak
	private static function getContractBudgetStatus($contractnum, $excludePonum = null) {
		
		// 1. Ambil Data Kontrak (Limit)
		$currContract = (new Set())->useTransactionManager()
			->MboSetRemote("purchcontract")
			->where("contractnum=?", $contractnum)
			->getMboSet()
			->fetch();

		if (!$currContract) {
			throw new Exception("Contract $contractnum not found.");
		}

		$limit = (float)$currContract['totalcost'];
		
		if ($limit <= 0) {
			return ['limit' => 0, 'spent' => 0, 'is_exhausted' => false];
		}

		// 2. Trace Back Lineage
		$relatedContracts = [$contractnum];
		$currentSearch = $contractnum;
		$loopGuard = 0;

		while ($loopGuard < 20) { 
			$prevData = (new Set())->useTransactionManager()
				->MboSetRemote("purchcontract")
				->where("contractnum = ?", $currentSearch)
				->MboRemote("prevcontractnum")
				->getMboSet()
				->fetch();
			
			if (!empty($prevData['prevcontractnum'])) {
				$prev = $prevData['prevcontractnum'];
				if (in_array($prev, $relatedContracts)) break; 
				$relatedContracts[] = $prev;
				$currentSearch = $prev;
			} else {
				break;
			}
			$loopGuard++;
		}

		// 3. Hitung Total PO (Real Spending)
		
		$poSet = (new Set())->useTransactionManager()
			->MboSetRemote("po")
			->whereIn("contractnum", $relatedContracts) 
			->where("status NOT IN ('cancel')");

		// [FIX] EXCLUDE CURRENT PO
		// Agar tidak double counting saat validasi diri sendiri
		if (!empty($excludePonum)) {
			$poSet->where("ponum != ?", $excludePonum);
		}
			
		$poSet->MboRemote("SUM(totalbasecost) as total_spent"); 
		$resultSet = $poSet->getMboSet(); // Execute

		$result = $resultSet->fetch();
		$spent = isset($result['total_spent']) ? (float)$result['total_spent'] : 0.00;

		return [
			'limit' => $limit,
			'spent' => $spent,
			'is_exhausted' => ($spent >= $limit)
		];
	}
	public static function CopyContractLines() {
		
		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();

		try {
			
			$payload = Super::getParam("CopyContractLines");
			$ponum = $payload["ponum"];
			$contractlines_input = $payload["contractlines"]; 
            // Format Payload: [{"id":"7","rowindex":"7"},{"id":"8","rowindex":"8"}]

			if (empty($ponum)) { throw new Exception("Target PO Number is required"); }
			if (empty($contractlines_input)) { throw new Exception("Selected Contract Lines are required"); }

			//Ambil Nomor Contract.

			$po = (new Set())->useTransactionManager()->MboSetRemote("po")->where("ponum=?", $ponum)->getMboSet()->getMbo();
			$contractnum = $po->getString("contractnum");

			$budgetStatus = self::getContractBudgetStatus($contractnum);

			
		
			if ($budgetStatus['is_exhausted']) {
				$limitStr = number_format($budgetStatus['limit'], 2);
				$spentStr = number_format($budgetStatus['spent'], 2);
				throw new Exception("Action Denied: Contract Budget is exhausted. Limit: $limitStr, Used: $spentStr. Cannot add more lines.");
			}

			// --- Ambil value dari key 'id' ---
			$lineIds = array_column($contractlines_input, 'id');
          
			// 1. Ambil Data Source (Contract Line)
			$contractLinesData = (new Set())->useTransactionManager()
									->MboSetRemote("contractline")
									->whereIn("contractlineid", $lineIds)
									->getMboSet()->fetchData();

			if (count($contractLinesData) !== count($lineIds)) {
				throw new Exception("Some of the selected Contract Lines could not be found.");
			}

			// 2. Siapkan Variabel Pendukung
			$poLinesToInsert = [];
			$next_polinenum = Xbo::getLineNumber("poline", "polinenum", "ponum='$ponum'");
			$currentUser = Xbo::getLoginUser();
			$currentDate = Xbo::setEnterDate();

			// 3. Loop & Mapping Data
			foreach ($contractLinesData as $cline) {
				
				$poLine = [];
				
				// A. Kunci Utama
				$poLine['ponum']       = $ponum;
				$poLine['polinenum']   = $next_polinenum;
				
				// B. Data Item & Deskripsi
				$poLine['linetype']       = $cline['linetype'];
				$poLine['itemnum']        = $cline['itemnum'];
				$poLine['serviceitemnum'] = $cline['serviceitemnum'];
				$poLine['description']    = $cline['description'];
				$poLine['orderunit']      = $cline['orderunit'];
				$poLine['conversionfactor'] = 1; 
				
				// C. Data Harga & Pajak
				$poLine['unitcost']    = $cline['unitcost']; 
				$poLine['tax']         = $cline['taxcode']; 
				
				// D. Reset Value
				$poLine['orderqty']    = 1; 
				$poLine['linecost']    = $poLine['orderqty']*$poLine['unitcost'];
				$poLine['taxcost']     = 0;
				
				// E. Link Kembali ke Kontrak
				$poLine['contractnum']     = $cline['contractnum'];
				$poLine['contractlinenum'] = $cline['contractlinenum']; 
				
				// F. Audit Trail & Defaults
				$poLine['receiptstatus'] = 'NONE';
				$poLine['enterby']       = $currentUser;
				$poLine['enterdate']     = $currentDate;
				$poLine['changeby']      = $currentUser;
				$poLine['changedate']    = $currentDate;
				
				$poLinesToInsert[] = $poLine;
				$next_polinenum += 10;
			}

			// 4. Eksekusi Insert Massal
			if (!empty($poLinesToInsert)) {
				(new Set())->useTransactionManager()
						->MboSetRemote("poline")
						->addStream($poLinesToInsert);
			}

			$tm->commit();
			Super::sendJsonResponse("success", count($poLinesToInsert) . " Contract Line(s) copied to PO {$ponum}. Please update the Quantity.");

		} catch (Exception $e) {
			$tm->rollback();
			Super::sendJsonResponse("error", $e->getMessage());
		}
	}
	public static function CopyPRLines()
	{
	
		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();

		try {
			
			$payload = Super::getParam("CopyPRLines");
			$ponum = $payload["ponum"];
			$prlines_input = $payload["prlines"]; 

			if (empty($ponum)) { throw new Exception("Selected PO Number is required"); }
			if (empty($prlines_input)) { throw new Exception("Selected PR Lines are required"); }

		
			$prlineIds = array_column($prlines_input, 'prlineid');
			$prLinesData = (new Set())->useTransactionManager()
									->MboSetRemote("prline")
									->whereIn("prlineid", $prlineIds)
									->getMboSet()->fetchData();

			if (count($prLinesData) !== count($prlineIds)) {
				throw new Exception("Some of the selected PR Lines could not be found.");
			}

			// 4. Siapkan Data untuk INSERT ke POLINE
			$poLinesToInsert = [];
			$next_polinenum = Xbo::getLineNumber("poline", "polinenum", "ponum='$ponum'");
			$keysToUnset = ['prlineid', 'ponum', 'polinenum', 'prnum', 'prlinenum']; // Kolom yang tidak relevan/berbeda di poline

			foreach ($prLinesData as $prLine) {
				$poLine = $prLine; 

				// Hapus kunci yang tidak relevan
				foreach ($keysToUnset as $key) {
					unset($poLine[$key]);
				}
				
				// Tambahkan/Setel kolom spesifik untuk POLINE
				$poLine['ponum'] = $ponum;
				$poLine['polinenum'] = $next_polinenum;
				$poLine['prnum'] = $prLine['prnum'];       // Simpan referensi ke PR asli
				$poLine['prlinenum'] = $prLine['prlinenum']; // Simpan referensi ke PR Line asli
				
				$poLinesToInsert[] = $poLine;
				$next_polinenum += 10;
			}

			if (!empty($poLinesToInsert)) {
				(new Set())->useTransactionManager()
						->MboSetRemote("poline")
						->addStream($poLinesToInsert);
			}
			

			// Update PRLINE Asli (di dalam transaksi)
			$current_polinenum = Xbo::getLineNumber("poline", "polinenum", "ponum='$ponum'") - (count($prLinesData) * 10); // Reset ke nomor awal
			foreach ($prLinesData as $prLine) {
				(new Set())->useTransactionManager()
						->MboSetRemote("prline")
						->where("prlineid = ?", $prLine['prlineid'])
						->setValue("ponum", $ponum)
						->setValue("polinenum", $current_polinenum)
						->change();
				$current_polinenum += 10;
			}
			
			$tm->commit();
			Super::sendJsonResponse("success", "PR Line(s) have been successfully copied to PO {$ponum}.");

		} catch (Exception $e) {
			// Jika ada error di manapun, batalkan semua
			$tm->rollback();
			Super::sendJsonResponse("error", $e->getMessage());
		}
	}
	public static function ValidateShowChildren()

	{
		$pts = explode("|", $_GET["validateshowchildren"]);
		$set = new Set();
		
		if ($pts[3] == "poline") {
			$polineSet = $set->MboSetRemote("poline")->setWhere("ponum='" . $pts[0] . "' and polineid = '" . $pts[2] . "'")->MboRemote("polinenum")->getMboSet();
			$polinenum = $polineSet->getMbo()->getString("polinenum");
			$newset = new Set();
			$invoiceLineSet = $newset->MboSetRemote("invoiceline")->setWhere("ponum='" . $pts[0] . "' and invoicelinenum='$polinenum'")->MboRemote("ponum")->getMboSet();
			$isExist = $invoiceLineSet->counts();
			if ($isExist > 0) {
				Set::throws(["msg" => "relateddata", "rltbl" => "invoiceline"]);
			}
		}
	}
	public static function PRPOCan()
	{
		$ponum = $_GET["prpocan"];
		$set = new Set();
		$polineSet = $set->MboSetRemote("poline")->setWhere("ponum='$ponum' and prnum is not null")->MboRemote("prnum")->MboRemote("prlinenum")->MboRemote("polinenum")->getMboSet();
		$countpoline = $polineSet->counts();
		$statusdate = Xbo::setEnterDate();
		for ($i = 0; $i < $countpoline; $i++) {
			$set2 = new Set();
			$polinenum = $poline->getString("polinenum");
			$set2->MboSetRemote("poline")->setWhere("ponum='$ponum'")->setValue("prnum", null)->setValue("prlinenum", null)->change();
			$set2->MboSetRemote("pr")->setWhere("prnum='$prnum'")->setValue("status", "approved")->setValue("statusdate", $statusdate)->change();
			$poline = $polineSet->getMbo();
			$prnum = $poline->getString("prnum");
			$prlinenum = $poline->getString("prlinenum");
			$set2->MboSetRemote("prline")->setWhere("prnum='$prnum' and ponum='$ponum' and polinenum='$polinenum'")->setValue("ponum", null)->setValue("polinenum", null)->change();
		}
	}
	public static function POChangeOrderServ()
	{
		$ponum = Super::getParam("pochangeorderserv");
		$polinenum = Super::getParam("polinenum");
		$orderqty = (float)Super::getParam("orderqty"); // Pastikan float

		// 1. VALIDASI RECEIVING (Service Receipt)
		// -------------------------------------
		$servrectransSet = (new Set())->useTransactionManager()
			->MboSetRemote("servrectrans")
			->setWhere("ponum='$ponum' and polinenum='$polinenum'")
			->getMboSet();
		
		if (!$servrectransSet->isEmpty()) {
			Set::throws(["msg" => "pocannotchangeqty", "recqty" => $orderqty]);
			return;
		}

		// 2. TIER PRICING LOGIC
		// -------------------------------------
		$newUnitPrice = null;

		// Ambil Info Kontrak dari Baris PO
		$poLineData = (new Set())->useTransactionManager()
			->MboSetRemote("poline")
			->where("ponum=? AND polinenum=?", $ponum, $polinenum)
			->MboRemote("contractnum, contractlinenum, unitcost")
			->getMboSet()
			->fetch();

		if ($poLineData && !empty($poLineData['contractnum'])) {
			$contractnum = $poLineData['contractnum'];
			$contractlinenum = $poLineData['contractlinenum'];
			
			// Cek Tabel Tier Price (Service juga bisa punya tier, misal: sewa > 100 jam lebih murah)
			$tierPrice = (new Set())->useTransactionManager()
				->MboSetRemote("contractprice")
				->where("contractnum=? AND contractlinenum=? AND minqty <= ?", $contractnum, $contractlinenum, $orderqty)
				->order("minqty DESC")
				->limit(1)
				->getMboSet()
				->fetch();

			if ($tierPrice) {
				// FOUND TIER!
				$newUnitPrice = (float)$tierPrice['unitcost'];
			} else {
				// NO TIER FOUND, Fallback ke Contract Line Base Price
				$baseLine = (new Set())->useTransactionManager()
					->MboSetRemote("contractline")
					->where("contractnum=? AND contractlinenum=?", $contractnum, $contractlinenum)
					->getMboSet()
					->fetch();
				
				if ($baseLine) {
					$newUnitPrice = (float)$baseLine['unitcost'];
				}
			}
		}

		// Return JSON dengan status "Success" + Harga Baru (jika ada)
		$response = ["msg" => "pocanchangeqty"];
		
		if ($newUnitPrice !== null) {
			$response["new_unit_cost"] = $newUnitPrice;
			$response["new_line_cost"] = $newUnitPrice * $orderqty;
		}

		Set::throws($response);
	}
	
	public static function POChangeOrderQty()
	{
		$tm = TransactionManager::getInstance();
		$ponum = Super::getParam("pochangeorderqty");
		$polinenum = Super::getParam("polinenum");
		$itemnum = Super::getParam("itemnum");
		$orderqty = (float)Super::getParam("orderqty"); // Pastikan float
		
		// 1. VALIDASI RECEIVING (Existing Logic)
		// -------------------------------------
		if (empty($itemnum)) {
			$itemval = " and (itemnum='' or itemnum is null)";
		} else {
			$itemval = " and itemnum='$itemnum'";
		}

		$matrectransSet = (new Set())->useTransactionManager()->MboSetRemote("matrectrans")
			->setWhere("ponum='$ponum' and polinenum='$polinenum' $itemval")
			->getMboSet();

		if (!$matrectransSet->isEmpty()) {
			Set::throws(["msg" => "pocannotchangeqty"]);
			return; 
		}

		// 2. TIER PRICING LOGIC (New Logic)
		$newUnitPrice = null;

		// Ambil Info Kontrak dari Baris PO Tersebut
		$poLineData = (new Set())->useTransactionManager()
			->MboSetRemote("poline")
			->where("ponum=? AND polinenum=?", $ponum, $polinenum)
			->MboRemote("contractnum, contractlinenum, unitcost")
			->getMboSet()
			->fetch();

		if ($poLineData && !empty($poLineData['contractnum'])) {
			$contractnum = $poLineData['contractnum'];
			$contractlinenum = $poLineData['contractlinenum'];
			
			// Cek Tabel Tier Price
			// Logic: Cari tier dengan minqty terbesar yang masih <= orderqty
			// Contoh: Qty 500. Tier: 100(10rb), 1000(9rb). 
			// 100 <= 500 (TRUE). 1000 <= 500 (FALSE). Ambil yang 100.
			$tierPrice = (new Set())->useTransactionManager()
				->MboSetRemote("contractprice")
				->where("contractnum=? AND contractlinenum=? AND minqty <= ?", $contractnum, $contractlinenum, $orderqty)
				->order("minqty DESC") // Urut dari yang terbesar supaya dapat tier paling tinggi
				->limit(1)
				->getMboSet()
				->fetch();

			if ($tierPrice) {
				// FOUND TIER!
				$newUnitPrice = (float)$tierPrice['unitcost'];
			} else {
				// NO TIER FOUND.
				// Opsi A: Ambil harga dasar dari Contract Line (jika belum ada tier)
				// Opsi B: Biarkan harga lama yang ada di PO Line (mungkin manual edit)
				
				$baseLine = (new Set())->useTransactionManager()
					->MboSetRemote("contractline")
					->where("contractnum=? AND contractlinenum=?", $contractnum, $contractlinenum)
					->getMboSet()
					->fetch();
				
				if ($baseLine) {
					$newUnitPrice = (float)$baseLine['unitcost'];
				}
			}
		}

		// Return JSON dengan status "Success" + Harga Baru (jika ada)
		$response = ["msg" => "pocanchangeqty"];
		
		if ($newUnitPrice !== null) {
			$response["new_unit_cost"] = $newUnitPrice;
			$response["new_line_cost"] = $newUnitPrice * $orderqty;
		}

		Set::throws($response);
	}
	
	
	public static function SumPOCostBackend($ponum)

	{
		
		$whereclause = "ponum='$ponum'";
		$set = new Set();
		$exchangerate = $set->MboSetRemote("po")->setWhere($whereclause)->MboRemote("exchangerate")->getMboSet()->getMbo()->getString("exchangerate");
		if ($exchangerate == null) $exchangerate = 0;
		$totaltax = $set->MboSetRemote("poline")->setWhere($whereclause)->MboRemote(" sum(taxcost) as totaltax ")->getMboSet()->getMbo()->getString("totaltax");
		if ($totaltax == null) $totaltax = 0;
		$pretaxtotal = $set->MboSetRemote("poline")->setWhere($whereclause)->MboRemote(" sum(linecost) as pretaxtotal ")->getMboSet()->getMbo()->getString("pretaxtotal");
		if ($pretaxtotal == null) $pretaxtotal = 0;
		$totalcost = $totaltax + $pretaxtotal;
		$totalbasecost = $totalcost * $exchangerate;
		$paidcost = $set->MboSetRemote("popayment")->setWhere($whereclause)->MboRemote("  sum(paidcost) as paidcost ")->getMboSet()->getMbo()->getString("paidcost");
		if ($paidcost == null) $paidcost = 0;
		else $paidcost = $paidcost * $exchangerate;
		$unpaidcost = (($totalbasecost - $paidcost) * -1);
		
		$set = new Set();
		$discSet = $set->MboSetRemote("po")->setWhere($whereclause)->MboRemote("discountprice")->MboRemote("discountpercentage")
		->MboRemote("exchangerate")->MboRemote("paidcost")
		->getMboSet();
		$disc = $discSet->getMbo();
		
		$discountprice = $disc->getString("discountprice");
		$discountpercentage = $disc->getString("discountpercentage");
		$exchangerate = $disc->getString("exchangerate");
		
		$pretaxtotaldiscount = $pretaxtotal-$discountprice;
		$totaltax2 = $totaltax*($discountpercentage/100);
		$totaltax2 = $totaltax-$totaltax2;
		$totalcost = $totaltax2+$pretaxtotaldiscount;
		$totalbasecost = $totalcost*$exchangerate;
		$unpaidcost = $paidcost-$totalbasecost;
		
		
		$set = new Set();
		$set->MboSetRemote("po")->setWhere($whereclause)
		->setValue("totalcost", $totalcost)
		->setValue("totaltax", $totaltax)
		->setValue("totalbasecost", $totalbasecost)
		->setValue("pretaxtotal", $pretaxtotal)
		->setValue("paidcost", $paidcost)
		->setValue("pretaxtotaldiscount", $pretaxtotaldiscount)
		->setValue("unpaidcost", round($unpaidcost))
		->setValue("totaltax2", $totaltax2)
		->change();
		
		
	}
	
	

public static function SumPOCost()
{
    if (!isset($_GET['sumpocost'])) {
        Set::throws(["msg" => "Error: sumpocost parameter is missing."]);
        return;
    }

    $value = $_GET['sumpocost'];
    $parts = explode("|", $value);

    if (count($parts) !== 2) {
        Set::throws(["msg" => "Error: Invalid sumpocost parameter format."]);
        return;
    }

    $table = $parts[0];
    $ownerParts = explode("`", $parts[1]);

    if (count($ownerParts) !== 2) {
        Set::throws(["msg" => "Error: Invalid owner format in sumpocost parameter."]);
        return;
    }

    $col = $ownerParts[0];
    $val = $ownerParts[1]; // data dari parameter GET sangat berbahaya

    $set = new Set();
    $safe_col = $set->conn->real_escape_string($col);
    $safe_val = $set->conn->real_escape_string($val);

    $whereClause = "`$safe_col` = '$safe_val'"; //untuk keamanan

    try {
        // Ambil data dari tabel PO
		$set = new Set();
        $poSet = $set->MboSetRemote("po")
            ->setWhere($whereClause)
            ->MboRemote("exchangerate, discountprice, discountpercentage,contractnum")
            ->getMboSet();

        $po = $poSet->getMbo();

        if (!$po) {
            Set::throws(["msg" => "Error: PO not found with specified criteria."]);
            return;
        }

        $exchangerate = (float) $po->getString("exchangerate");
        $discountprice = (float) $po->getString("discountprice");
        $discountpercentage = (float) $po->getString("discountpercentage");
		$contractnum = $po->getString("contractnum");

        // Default values
        $totaltax = 0;
        $pretaxtotal = 0;
        $paidcost = 0;

        // Ambil totaltax
		
		$set = new Set();
        $polineSet = $set->MboSetRemote("poline")
            ->setWhere($whereClause)
            ->MboRemote("SUM(taxcost) as totaltax")
            ->getMboSet();

        $poline = $polineSet->getMbo();
        if ($poline) {
            $totaltax = (float) $poline->getString("totaltax");
        }
		
        // Ambil pretaxtotal
		$set = new Set();
        $polineSet = $set->MboSetRemote("poline")
            ->setWhere($whereClause)
            ->MboRemote("SUM(linecost) as pretaxtotal")
            ->getMboSet();

        $poline = $polineSet->getMbo();
		$pretaxtotal = $poline->getString("pretaxtotal");
		
        if ($poline) {
            $pretaxtotal = (float) $poline->getString("pretaxtotal");
        }

        // Ambil paidcost dari popayment
		$set = new Set();
        $popaymentSet = $set->MboSetRemote("popayment")
            ->setWhere($whereClause)
            ->MboRemote("SUM(paidcost) as paidcost")
            ->getMboSet();

        $popayment = $popaymentSet->getMbo();
        if ($popayment) {
            $paidcost = (float) $popayment->getString("paidcost");
        }


		
		// Ambil Line Type dulu
		$poLineSet = (new Set())
			->useTransactionManager()
			->MboSetRemote("poline")
			->where("ponum=?", $safe_val)
			->getMboSet();

		$hasItem = false;
		$hasService = false;
		$linetypeCounts = 0;

		while ($line = $poLineSet->fetch()) {

			$linetype = strtolower(trim($line["linetype"]));
			$linetypeCounts++;

			if ($linetype === "item") {
				$hasItem = true;
			}

			if ($linetype === "service") {
				$hasService = true;
			}
		}



        // Hitung
        $pretaxtotaldiscount = $pretaxtotal - $discountprice;
        $totaltax2 = $totaltax * ($discountpercentage / 100);
        $totaltax2 = $totaltax - $totaltax2;
        $totalcost = $totaltax2 + $pretaxtotaldiscount;
        $totalbasecost = $totalcost * $exchangerate;
        $unpaidcost = $paidcost - $totalbasecost;
		
		// Penentuan PO Type
		

		$potype = "";

		$totalbasecost = (float) $totalbasecost;
		$contractnum = trim((string) $contractnum);

		// 1. KHS
		if (!empty($contractnum)) {
			$potype = "KHS";
		}

		// 2. Overhead (0 - <10jt) // ini kamu cek dulu dek berapa range receh receh
		else if ($totalbasecost >= 0 && $totalbasecost < 10000000) {
			$potype = "OVERHEAD";
		}

		// 3. 10jt - <100jt
		else if ($totalbasecost >= 10000000 && $totalbasecost < 100000000) {

			if ($hasItem && !$hasService) {
				// Barang saja
				$potype = "SPBL";
			} else {
				// Service saja ATAU campuran, ada service doang apa nggak?
				$potype = "SPBJ";
			}
		}

		// 4. SPK (100jt - <300jt)
		else if ($totalbasecost >= 100000000 && $totalbasecost < 300000000) {
			$potype = "SPK";
		}

		// 5. PJ (>=300jt)
		else if ($totalbasecost >= 300000000) {
			$potype = "PJ";
		}


		// ==============================
		// DEBUG OUTPUT
		// ==============================

		Set::throws([
			"potype" => $potype,
			"linetypeCounts" => $linetypeCounts,
			"hasItem" => $hasItem,
			"hasService" => $hasService,
			"totalcost" => $totalcost,
			"totaltax" => $totaltax,
			"totalbasecost" => $totalbasecost,
			"pretaxtotal" => $pretaxtotal,
			"paidcost" => $paidcost,
			"unpaidcost" => $unpaidcost,
			"pretaxtotaldiscount" => $pretaxtotaldiscount,
			"totaltax2" => $totaltax2
		]);


    } catch (Exception $e) {
        Set::throws(["msg" => "Error: " . $e->getMessage()]);
    }
}

} 

Class POValidateTables {	
	public static function tbce(){
		$table = $_GET['tbce'];
		if ($table == "mxtable_vendorpolines_child" || $table == "mxtable_vendormatrectrans_child" || $table == "mxtable_vendorservrectrans_child" || $table == "mxtable_receipt_child" || $table == "mxtable_matsaletrans_child" || $table == "mxtable_servsaletrans_child" || $table == "mxtable_soshipment_child" || $table == "mxtable_sotracking_child") 
			echo "histable";
		if ($table == "mxtable_inventory_child" || $table == "mxtable_invvendor_child") echo "relatedtable";
	}

	public static function tbcx(){
		
		$payload = Super::getParam("tbcx");
		$data = $payload["dataPackage"];
		$childrentable = $data["datasetChild"];
		$selected_rows = $data["selectedRows"];
		$parent_id = $data["ownership"]["xbosetid"];
		$can_delete = false;

		if($childrentable == "poline"){
			$poSet = (new Set())->MboSetRemote("po")->where("poid=?", $parent_id)->getMboSet();
			$po = $poSet->getMbo();
			$status = $po->getString("status");
			$vendor = $po->getString("vendor");
			if($status == "approved" || $status == "cancel" || $status == "sent"){
				Super::sendJsonResponse("error", "Cannot delete record when PO status: $status");
			}else{
				foreach($selected_rows as $row){
					foreach($row as $key=>$value){
						$poline = (new Set())->useTransactionManager()->MboSetRemote("poline")->setWhere("$key='$value'")->MboRemote("ponum, polinenum, rfqnum, rfqlinenum, prnum, prlinenum")->getMboSet()->getMbo();
						$ponum = $poline->getString("ponum");
						$polinenum = $poline->getString("polinenum");
						$rfqnum = $poline->getString("rfqnum");
						$rfqlinenum = $poline->getString("rfqlinenum");
						$prnum = $poline->getString("prnum");
						$prlinenum = $poline->getString("prlinenum");
						$Matrectrans = (new Set())->useTransactionManager()->MboSetRemote("matrectrans")->setWhere("ponum='$ponum' and polinenum='$polinenum'")->MboRemote("polinenum")->getMboSet();
						if (!$Matrectrans->isEmpty()) {
							Super::sendJsonResponse("error", "The POLINE has Material Received Related Record");
						}else{
							//check servrectrans
							$Servrectrans = (new Set())->useTransactionManager()->MboSetRemote("servrectrans")->setWhere("ponum='$ponum' and polinenum='$polinenum'")->MboRemote("polinenum")->getMboSet();
							if (!$Servrectrans->isEmpty()) {
								Super::sendJsonResponse("error", "The POLINE has Service Received Related Record");
							}else{
								$Invoiceline = (new Set())->useTransactionManager()->MboSetRemote("invoiceline")->setWhere("ponum='$ponum' and invoicelinenum='$polinenum'")->MboRemote("invoicelinenum")->getMboSet();
								if (!$Invoiceline->isEmpty()) {
									Super::sendJsonResponse("error", "The POLINE has Invoice Line Related Record");
								}else{
									$poline_rfq =  (new Set())->useTransactionManager()->MboSetRemote("rfqquotation")->setWhere("rfqnum='$rfqnum' and rfqlinenum='$rfqlinenum' and vendor='$vendor'")->getMboSet();
									if (!$poline_rfq->isEmpty()) {
										Super::sendJsonResponse("error", "The POLINE has RFQ Quotation Line Related Record");
									}else{
										(new Set())->MboSetRemote("prline")->where("prnum = ? AND prlinenum=?", $prnum, $prlinenum)
										->setValue("ponum", null)
										->setValue("polinenum", null)
										->change();
										//Jangan di delete disini, karena informasi ponum dan polinenum bisa hilang.
										//setiap polinenum membawa informasi prnum dan prlinenum
										//fungsi delete akan menghapus semua record dalam sekali jalan.
										//ubah flag can_delete = true
										$can_delete = true;
									}
								}
							}
							
						}
						
					}
				}
			}
			
			if($can_delete == true){
				$_POST["delChildPackage"] = $data;
				Super::deleteChildren();
			}
		}else if($childrentable == "popayment"){
			foreach($selected_rows as $row){
				foreach($row as $key=>$value){
					$popayment = (new Set())->useTransactionManager()->MboSetRemote("popayment")->setWhere("$key='$value'")->MboRemote("ponum")->MboRemote("invoicelineid")->getMboSet()->getMbo();
					$ponum = $popayment->getString("ponum");
					$invoicelineid = $popayment->getString("invoicelineid");
					$invoicelineSet = (new Set())->useTransactionManager()->MboSetRemote("invoiceline")->setWhere("invoicelineid='$invoicelineid' and ponum='$ponum'")->MboRemote("invoicelineid")->getMboSet();
					if ($invoicelineSet->counts() > 0) {
						Super::sendJsonResponse("error", "The POPAYMENT has Invoice Line Related Record");
					}else{
						$_POST["delChildPackage"] = $data;
						Super::deleteChildren();
					}
				}
			}
		}	
	}
}
	

?>