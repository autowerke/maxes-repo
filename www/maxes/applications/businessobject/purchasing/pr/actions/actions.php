<?php
require_once ("../../../eat.php");

Class ActionSet {
	public static function createcontracttransaction() {
		
		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();

		try {
			$payload = Super::getParam("createcontracttransaction");
			$prnum   = $payload["prnum"];
			$data    = $payload["data"]; //[{vendor: 'A', contractnum: 'CON-01'}]

			$prSet = (new Set())->useTransactionManager()
				->MboSetRemote("pr")
				->where("prnum=?", $prnum)
				->getMboSet()
				->fetch();

			$enterby = Xbo::getLoginUser();
			$currentDate = Xbo::setEnterDate();
			$orgInfo = Xbo::getOrgcode($enterby);

			// Loop per Vendor (Biasanya cuma 1)
			foreach ($data as $row) {
				$vendor = $row["vendor"];
				$contractnum = $row["contractnum"];

				// Cek Duplikat
				$exist = (new Set())->useTransactionManager()->MboSetRemote("purchcontract")->where("contractnum=?", $contractnum)->countRecords();
				if ($exist > 0) throw new Exception("Contract $contractnum already exists.");

				// 1. Siapkan Header Contract
				$totalCost = 0;
				
				// 2. Ambil Lines PR (Mapping ke Contract Lines)
				$prLines = (new Set())->useTransactionManager()
					->MboSetRemote("prline")
					->where("prnum=?", $prnum)
					->getMboSet()
					->fetchData();

				$cLines = [];
				$lineSeq = 10;
				
				foreach ($prLines as $pl) {
					$lineTotal = (float)$pl['linecost'];
					$totalCost += $lineTotal;

					$cLines[] = [
						"contractnum"     => $contractnum,
						"contractlinenum" => $lineSeq,
						"linetype"        => $pl['linetype'], // Item/Service
						"itemnum"         => $pl['itemnum'],
						"serviceitemnum"  => $pl['serviceitemnum'],
						"description"     => $pl['description'],
						"orderunit"       => $pl['orderunit'],
						"minqty"          => 1, // Default
						"taxcode"         => $pl['tax'], // Mapping: PR(tax) -> Contract(taxcode)
						"unitcost"        => $pl['unitcost'],
						"remark"          => "From PR " . $prnum,
						"enterby"         => $enterby,
						"enterdate"       => $currentDate,
						"changeby"        => $enterby,
						"changedate"      => $currentDate
					];
					$lineSeq += 10;
				}

				// Insert Header
				(new Set())->useTransactionManager()->MboSetRemote("purchcontract")->addStream([[
					"contractnum"  => $contractnum,
					"description"  => "Contract from PR " . $prnum . " - " . $prSet['description'],
					"contracttype" => "PRICE",
					"vendorcode"   => $vendor,
					"status"       => "DRAFT",
					"statusdate"   => $currentDate,
					"startdate"    => $currentDate,
					"enddate"      => date('Y-m-d H:i:s', strtotime($currentDate . ' + 1 year')),
					"totalcost"    => $totalCost,
					"amountspent"  => 0,
					"prnum"        => $prnum, 
					"buyer"        => $enterby,
					"currencycode" => $prSet['currencycode'],
					"enterby"      => $enterby,
					"enterdate"    => $currentDate,
					"orgcode"      => $orgInfo->orgcode,
					"sitecode"     => $orgInfo->sitecode
				]]);

				// Insert Lines
				if (!empty($cLines)) {
					(new Set())->useTransactionManager()->MboSetRemote("contractline")->addStream($cLines);
				}
			}

			// Update PR (Optional: Link ContractNum back to PR Header)
			// (new Set())->useTransactionManager()->MboSetRemote("pr")->where("prnum=?", $prnum)->setValue("contractnum", $contractnum)->change();

			$tm->commit();
			Super::sendJsonResponse("success", "Contract created successfully from PR.", $data);

		} catch (Exception $e) {
			$tm->rollback();
			Super::sendJsonResponse("error", $e->getMessage());
		}
	}
	public static function createcontract() {
		$prnum = Super::getParam("createcontract");

		// 1. Cek Header PR
		$prHeader = (new Set())->useTransactionManager()
			->MboSetRemote("pr")
			->where("prnum=?", $prnum)
			->getMboSet()
			->fetch();

		if (!$prHeader) { Super::sendJsonResponse("error", "PR not found."); }
		$prStatus = strtolower($prHeader['status']);
		
		// Validasi Status PR (Hanya Approved yang boleh jadi Kontrak)
		if ( $prStatus != 'approved') {
			Super::sendJsonResponse("error", "PR must be APPROVED to create a contract.");
		}

		// 2. Ambil PR Lines
		$prLines = (new Set())->useTransactionManager()
			->MboSetRemote("prline")
			->where("prnum=?", $prnum)
			->getMboSet()
			->fetchData();

		if (empty($prLines)) { Super::sendJsonResponse("error", "PR Lines not found."); }

		// 3. GROUP BY VENDOR (Logic Baru)
		$vendorsFound = [];
		$vendorMap = []; // [VendorCode => [LineData...]]

		foreach ($prLines as $line) {
			// Vendor diambil dari PR Header (biasanya PR itu 1 vendor)
			// Tapi kalau sistem Anda support PR Line beda vendor, ambil dari Line.
			// Asumsi: Vendor ada di Header PR.
			
			$v = $prHeader['vendor']; 
			if (empty($v)) {
				Super::sendJsonResponse("error", "PR Header must have a Vendor specified.");
			}
			
			if (!in_array($v, $vendorsFound)) {
				$vendorsFound[] = $v;
				
				// Generate Auto Number Contract
				$contractnum = (new Super())->getAutoNumber("purchcontract", "update_next_value")->autonumber;
				
				$vendorMap[] = [
					"vendor" => $v,
					"new_contractnum" => $contractnum,
					"pr_description" => $prHeader['description']
				];
			}
		}

		// Kirim data yang sudah di-grouping (Harusnya cuma 1 item kalau PR single vendor)
		Super::sendJsonResponse("success", "Vendor Selected", $vendorMap); 
	}
	public static function validateApproval(){ //validateStatusChange
		$payload = Super::getParam("validateApproval");
		$prnum = $payload["prnum"];
		$new_status = $payload["new_status"];
		$budgetnum = $payload["budgetnum"];

		//Check PR
		$prSet = (new Set())->useTransactionManager()->MboSetRemote("pr")->where("prnum=?", $prnum)->getMboSet();

		$pr = $prSet->getMbo();
		$description = $pr->getString("description");
		$requestedby = $pr->getString("requestedby");
		$current_status = $pr->getString("status");
		$requesteddate = $pr->getString("requesteddate");
		$currencycode = $pr->getString("currencycode");
		$exchangerate = $pr->getString("exchangerate");

		if ($current_status == "cancel") {
			Super::sendJsonResponse("error", "Cannot change status for a PR that has been canceled.");
		}

		if (empty($description)) {
			Super::sendJsonResponse("error", "Description is required before changing the status.");
		}

		if (empty($requestedby)) {
			Super::sendJsonResponse("error", "Requested By is required before changing the status.");
		}

		if (empty($requesteddate)) {
			Super::sendJsonResponse("error", "Requested Date is required before changing the status.");
		}

		if (empty($currencycode)) {
			Super::sendJsonResponse("error", "Currency Code is required before changing the status.");
		}

		if (empty($exchangerate)) {
			Super::sendJsonResponse("error", "Exchange Rate is required before changing the status.");
		} elseif ($exchangerate <= 0) {
			Super::sendJsonResponse("error", "Exchange Rate cannot be zero or below.");
		}

		//Check PRLINES
		$prlines = [];
		$prlineSet = (new Set())->useTransactionManager()->MboSetRemote("prline")->where("prnum = ? ", $prnum)->getMboSet();
		while($prline = $prlineSet->fetch()){
			$linetype = $prline["linetype"];
			$prlinenum = $prline["prlinenum"];
			if(empty($linetype)){
				$prlines[] = "Line Type is required on PR Line Number $prlinenum";
			}

			$gldebitacct = $prline["gldebitacct"];
			$glcreditacct = $prline["glcreditacct"];
			$description = $prline["description"];
				
			if($linetype == "item"){
				$warehouse = $prline["warehouse"];
				$itemnum = $prline["itemnum"];
				$conversionfactor = $prline["conversionfactor"];
			
				if(empty($warehouse)){
					$prlines[] = "Warehouse is required on PR Line Number $prlinenum";
				}
				if(empty($itemnum)){
					$prlines[] = "Item Number is required on PR Line Number $prlinenum";
				}
				if(empty($conversionfactor)){
					$prlines[] = "Conversion Factor is required on PR Line Number $prlinenum";
				}
				
				if($conversionfactor <= 0){

					$prlines[] = "Conversion Factor must higher than 0 on PR Line Number $prlinenum";
				}

			}else{
				//linetype = "services"
				$wonum = $prline["wonum"];
				if(empty($wonum)){
					$prlines[] = "Work Order is required on PR Line Number $prlinenum";
				}
			}

			if(empty($glcreditacct)){
				$prlines[] = "GL Credit Account is required on PR Line Number $prlinenum";
			}
			if(empty($gldebitacct)){
				$prlines[] = "GL Debit Account is required on PR Line Number $prlinenum";
			}
			if(empty($description)){
				$prlines[] = "Description is required on PR Line Number $prlinenum";
			}
		}

		if(!empty($prlines)){
			$errorMessage = implode("<br>", $prlines);
			Super::sendJsonResponse("error", $errorMessage);
		}

		if(!empty($budgetnum)){
			$prlines = (new Set())->MboSetRemote("prline")
			->where("prnum=?", $prnum)
			->MboRemote("prlinenum, budgetlinenum") // Minta kolom yang kita butuhkan
			->getMboSet()
			->fetchData(); // Ambil semua baris sebagai array

			if (empty($prlines)) {
				Super::sendJsonResponse("error", "This PR has no lines.");
				return;
			}

			$nullBudgetLines = [];
			$budgetChecks = []; // buat array untuk mengumpulkan budget number.

			foreach ($prlines as $line) {
				// Cek budgetlinenum yang kosong
				if (empty($line["budgetlinenum"])) {
					$context = "<li>Budget Line Number on PR Line {$line['prlinenum']} is not set.</li>";
					$nullBudgetLines[] = $context;
				} else {
					// Kumpulin budgetnum dan budgetlinenum yang unik untuk divalidasi nanti
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
				$pr_vendorcode = (new Set())->useTransactionManager()->MboSetRemote("pr")
					->where("prnum=?", $prnum)
					->getMboValue("vendor");

				// Normalisasi huruf
				$clean_budget_vendor = strtolower(trim($budget_vendorcode));
				$clean_pr_vendor = strtolower(trim($pr_vendorcode));

				if($clean_budget_vendor !== $clean_pr_vendor) {
					
					$errorMessage = "This PR cannot be approved. The selected budget ($budgetnum) is specifically allocated for vendor '{$budget_vendorcode}', but this PR is for vendor '{$pr_vendorcode}'. Please use a different budget or change the vendor on the PR.";
					
					Super::sendJsonResponse("error", $errorMessage);
				}
			}

			// Validasi untuk setiap budget line
			$budgetExceededErrors = [];
			foreach ($budgetChecks as $check) {
				$budgetnum = $check["budgetnum"];
				$budgetlinenum = $check["budgetlinenum"];

				// Cek dulu apakah budget ini perlu divalidasi
				$isExceeds = (new Set())->useTransactionManager()->MboSetRemote("budget")->where("budgetnum=?", $budgetnum)->getMboValue("isexceeds");
				
				if ($isExceeds == "Y") {
					// 1. Ambil sisa budget SAAT INI dari view (yang mungkin tidak termasuk PR ini jika statusnya 'hold')
					$current_remaining = (new Set())->useTransactionManager()->MboSetRemote("budgetline_summary")
						->where("budgetnum=?", $budgetnum)
						->where("budgetlinenum=?", $budgetlinenum) 
						->getMboValue("remaining_amount", 0); // Default ke 0 jika null

					// 2. Ambil BIAYA dari PR yang sedang divalidasi untuk budget line ini
					$cost_of_this_pr_line = (new Set())->useTransactionManager()->MboSetRemote("prline")
						->where("prnum=?", $prnum)
						->where("budgetlinenum=?", $budgetlinenum)
						->MboRemote("SUM(linecost) as totalcost")
						->getMboValue("totalcost", 0); // Default ke 0 jika null

					// 3. sisa budget SEBENARNYA jika PR ini lolos
					$hypothetical_remaining = $current_remaining - $cost_of_this_pr_line;

					// 4. Lakukan pengecekan pada nilai HIPOTETIS, BUKAN nilai dari view
					if ($hypothetical_remaining < 0) { 
						$remaining_amount_formatted = number_format($hypothetical_remaining, 2, '.', ',');
						$budgetExceededErrors[] = "<li>Budget line {$budgetnum}-{$budgetlinenum} would be exceeded. Remaining balance would be {$remaining_amount_formatted}.</li>";
					}
				}
			}

			if (!empty($budgetExceededErrors)) {
				(new Set())->useTransactionManager()->MboSetRemote("pr")->where("prnum=?", $prnum)
				->setValue("isbudgetexceeds", "Y")
				->setValue("status", "hold") 
				->change();
				$errorMessage = "Cannot Approve PR. The PR has been placed on HOLD due to insufficient budget. It will need to be re-validated after the budget is adjusted. Issues found: <ul>" . implode("", $budgetExceededErrors) . "</ul>";
				Super::sendJsonResponse("notice", $errorMessage);
			}

		}
		
		// Jika semua validasi lolos
		(new Set())->useTransactionManager()->MboSetRemote("pr")->where("prnum=?", $prnum)->setValue("isbudgetexceeds", "N")->change();
		Super::sendJsonResponse("success", "PR has been changed status to $new_status");
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
		$unitcost = number_format((float)$unitcost, 2, '.', '');
		Set::throws(["description"=>$description, "unitcost"=>$unitcost, "serviceunit"=>$serviceunit]);
	}

	public static function GetGLCreditAccount(){
		$vendorname = $_POST["getglcreditacct"];
		$orgcode = $_POST["orgcode"];
		
		$set = new Set();
		$vendorSet = $set->MboSetRemote("vendor")->setWhere("vendorcode='$vendorname'")->MboRemote("rbniacct")->getMboSet();
		$vendor = $vendorSet->getMbo();
		$rbniacct = $vendor->getString("rbniacct");
		Set::throws(["glcreditacct"=>$rbniacct]);
	}
	public static function ValidateShowChildren()
	{
		$pts = explode("|", $_GET["validateshowchildren"]);
		$set = new Set();
		if ($pts[3] == "prline") {
			$prlineSet = $set->MboSetRemote("prline")->setWhere("prnum='" . $pts[0] . "' and prlineid = '" . $pts[2] . "'")->MboRemote("prlinenum")->getMboSet();
			$prlinenum = $prlineSet->getMbo()->getString("prlinenum");
			$newset = new Set();
			$prlineSet = $newset->MboSetRemote("poline")->setWhere("prnum='" . $pts[0] . "' and prlinenum='$prlinenum'")->MboRemote("prnum")->getMboSet();
			$isExist = $prlineSet->counts();
			if ($isExist > 0) {
				echo json_encode(["msg" => "relateddata", "rltbl" => "poline"]);
			}
		}
	}
	
	public static function POChangeOrderService()
	{
		$poparts = explode("|", $_POST["pochangeorderserv"]);
		$ponum = $poparts[0];
		$polinenum = $poparts[1];
		$orderqty = $poparts[2];
		$set = new Set();
		$recQty = $set->MboSetRemote("servrectrans")->setWhere("ponum='$ponum' and polinenum='$polinenum'")->MboRemote(" sum(quantity) as quantity ")->getMboSet()->getMbo()->getString("quantity");
		if ($orderqty > 0) {
			if ($orderqty >= $recQty) {
				Set::throws(["msg" => "pocanchangeqty"]);
			}
		}
		if ($orderqty < $recQty || $orderqty <= 0) {
			Set::throws(["msg" => "pocannotchangeqty", "recqty" => $recQty]);
		}
	}
	
	public static function POChangeOrderQty()
	{
		$poparts = explode("|", $_POST["pochangeorderqty"]);
		$ponum = $poparts[0];
		$polinenum = $poparts[1];
		$itemnum = $poparts[2];
		$orderqty = $poparts[3];
		$set = new Set();
		if (empty($itemnum)) {
			$itemval = " and (itemnum='' or itemnum is null)";
		}
		else {
			$itemval = " and itemnum='$itemnum'";
		}
		$counts = $set->MboSetRemote("matrectrans")->setWhere("ponum='$ponum' and polinenum='$polinenum' $itemval")->MboRemote("ponum")->getMboSet()->counts();
		if ($counts > 0) {
			$recQty = $set->MboSetRemote("matrectrans")->setWhere("ponum='$ponum' and polinenum='$polinenum' $itemval")->MboRemote(" sum(quantity) as quantity ")->getMboSet()->getMbo()->getString("quantity");
			if ($orderqty > 0) {
				if ($orderqty >= $recQty) {
					Set::throws(["msg" => "pocanchangeqty"]);
				}
			}
			if ($orderqty < $recQty || $orderqty <= 0) {
				Set::throws(["msg" => "pocannotchangeqty", "recqty" => $recQty]);
			}
		}
		else {
			Set::throws(["msg" => "pocanchangeqty"]);
		}
	}
	public static function SumPRCost()
	{
		$value = $_GET['sumprcost'];
		$parts = explode("|", $value);
		$table = $parts[0];
		$owner = explode("`", $parts[1]);
		$col = $owner[0];
		$val = "'" . $owner[1] . "'";
		$set = new Set();
		$exchangerate = $set->MboSetRemote("pr")->setWhere("$col=$val")->MboRemote("exchangerate")->getMboSet()->getMbo()->getString("exchangerate");
		if ($exchangerate == null) $exchangerate = 0;
		$set = new Set();
		$totaltax = $set->MboSetRemote("prline")->setWhere("$col=$val")->MboRemote(" sum(taxcost) as totaltax ")->getMboSet()->getMbo()->getString("totaltax");
		if ($totaltax == null) $totaltax = 0;
		$set = new Set();
		$pretaxtotal = $set->MboSetRemote("prline")->setWhere("$col=$val")->MboRemote(" sum(linecost) as pretaxtotal ")->getMboSet()->getMbo()->getString("pretaxtotal");
		if ($pretaxtotal == null) $pretaxtotal = 0;
		$totalcost = $totaltax + $pretaxtotal;
		$totalbasecost = $totalcost * $exchangerate;
		Set::throws(["totalcost" => $totalcost, "totaltax" => $totaltax, "totalbasecost" => $totalbasecost, "pretaxtotal" => $pretaxtotal]);
	}
	
	public static function CreateNewPO()
	{

		$prnum = Super::getParam("prnum");
		$ponum = Super::getParam("ponum");
		$podesc =Super::getParam("podesc");
		
	
		$isPOExist = (new Set())->useTransactionManager()->MboSetRemote("po")->setWhere("ponum='$ponum'")->MboRemote("ponum")->getMboSet();
		
		if ($isPOExist->isEmpty()) {
			$schemaSet = (new Set())->useTransactionManager()->getAllColumns("pr");
			$columnsSet = $schemaSet->result;
			$countResult = $columnsSet->counts();
			$columnr = [];
			for ($i = 0; $i < $countResult; $i++) {
				$columns = $columnsSet->getMbo();
				$column = $columns->getString("columnname");
				$column = strtolower($column);
				array_push($columnr, $column);
			}
			
			$prSet = (new Set())->useTransactionManager()->MboSetRemote("pr")->setWhere("prnum='$prnum'");
			for ($i = 0; $i < count($columnr); $i++) {
				$prSet = $prSet->MboRemote($columnr[$i]);
			}
			//get PR Data
			$prSet = $prSet->getMboSet();
			$pr = $prSet->getMbo();
			$prdesc = $pr->getString("description");
			$requestedby = $pr->getString("requestedby");
			$requesteddate = $pr->getString("requesteddate");
			$priority = $pr->getString("priority");
			$remarks = $pr->getString("remarks");
			$status = "new";
			$vendor = $pr->getString("vendor");
			$requireddate = $pr->getString("requireddate");
			$followupdate = $pr->getString("followupdate");
			$vendordate = $pr->getString("vendordate");
			$freightterms = $pr->getString("freightterms");
			$paymentterms = $pr->getString("paymentterms");
			$estdatearrival = $pr->getString("estdatearrival");
			$fobpoints = $pr->getString("fobpoints");
			$billtoaddress = $pr->getString("billtoaddress");
			$shiptoaddress = $pr->getString("shiptoaddress");
			$currencycode = $pr->getString("currencycode");
			$exchangerate = $pr->getString("exchangerate");
			$pretaxtotal = $pr->getString("pretaxtotal");
			$totaltax = $pr->getString("totaltax");
			$totalcost = $pr->getString("totalcost");
			$totalbasecost = $pr->getString("totalbasecost");
			$orgcode = $pr->getString("orgcode");
			$sitecode = $pr->getString("sitecode");
			$enterdate = Xbo::setEnterDate();
			$enterby = Xbo::getLoginUser();
			$changeby = $enterby;
			$changedate = $enterdate;
			$budgetnum = $pr->getString("budgetnum");
			// insert PR data to PO
			(new Set())->useTransactionManager()->MboSetRemote("po")->setValue("ponum", $ponum)->setValue("description", $podesc)->setValue("buyer", $requestedby)->setValue("priority", $priority)->setValue("remarks", $remarks)->setValue("status", $status)->setValue("vendor", $vendor)
			->setValue("followupdate", $followupdate)->setValue("vendordate", $vendordate)->setValue("freightterms", $freightterms)->setValue("paymentterms", $paymentterms)
			->setValue("estdatearrival", $estdatearrival)->setValue("fobpoints", $fobpoints)->setValue("billtoaddress", $billtoaddress)->setValue("shiptoaddress", $shiptoaddress)
			->setValue("currencycode", $currencycode)->setValue("exchangerate", $exchangerate)->setValue("pretaxtotal", $pretaxtotal)->setValue("totaltax", $totaltax)->setValue("enterby", $enterby)
			->setValue("enterdate", $enterdate)->setValue("totalcost", $totalcost)->setValue("totalbasecost", $totalbasecost)
			->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)->setValue("budgetnum", $budgetnum)->add();
			
			(new Set())->useTransactionManager()->MboSetRemote("postatus")->setValue("ponum", $ponum)->setValue("status", $status)->setValue("changeby", $changeby)
			->setValue("changedate", $changedate)->setValue("remarks", "Created from PR: $prnum")->add();
			
			//get all columname from prline table.
			$ss = new Set();
			$schemaSet = $ss->getAllColumns("prline");
			$columnsSet = $schemaSet->result;
			$countResult = $columnsSet->counts();
			$columnr = [];
			for ($i = 0; $i < $countResult; $i++) {
				$columns = $columnsSet->getMbo();
				$column = $columns->getString("columnname");
				
				$column = strtolower($column);
				array_push($columnr, $column);
			}
			
			//set all column name to sql selection
		
			$prlineSet = (new Set())->useTransactionManager()->MboSetRemote("prline")->setWhere("prnum='$prnum' and (ponum is null or ponum='')");
			for ($i = 0; $i < count($columnr); $i++) {
				$prlineSet = $prlineSet->MboRemote($columnr[$i]);
			}
			
			$prlineSet = $prlineSet->getMboSet();
			
			$countprline = $prlineSet->counts();
		
			//get value of prline column from selection
			for ($i = 0; $i < $countprline; $i++) {
				$prline = $prlineSet->getMbo();
				$prlinenum = $prlineSet->getString("prlinenum");
				$linetype = $prlineSet->getString("linetype");
				$itemnum = $prlineSet->getString("itemnum");
				$partnum = $prlineSet->getString("partnum");
				$description = $prlineSet->getString("description");
				$conditiontype = $prlineSet->getString("conditiontype");
				$unitcost = $prlineSet->getString("unitcost");
				$linecost = $prlineSet->getString("linecost");
				$tax = $prlineSet->getString("tax");
				$taxcost = $prlineSet->getString("taxcost");
				$manufacturer = $prlineSet->getString("manufacturer");
				$category = $prlineSet->getString("category");
				$catalog = $prlineSet->getString("catalog");
				$trackingnum = $prlineSet->getString("trackingnum");
				$requireddate = $prlineSet->getString("requireddate");
				$orderqty = $prlineSet->getString("orderqty");
				$orderunit = $prlineSet->getString("orderunit");
				$wonum = $prlineSet->getString("wonum");
				$conversionfactor = $prlineSet->getString("conversionfactor");
				$warehouse = $prlineSet->getString("warehouse");
				$gldebitacct = $prlineSet->getString("gldebitacct");
				$glcreditacct = $prlineSet->getString("glcreditacct");
				$budgetlinenum = $prlineSet->getString("budgetlinenum");
				$serviceitemnum = $prlineSet->getString("serviceitemnum");
				$polinenum = $i + 1;
				
				(new Set())->useTransactionManager()->MboSetRemote("poline")->setValue("ponum", $ponum)->setValue("polinenum", $polinenum)->setValue("linetype", $linetype)->setValue("itemnum", $itemnum)->setValue("partnum", $partnum)
				->setValue("description", $description)->setValue("conditiontype", $conditiontype)->setValue("unitcost", $unitcost)->setValue("linecost", $linecost)
				->setValue("tax", $tax)->setValue("taxcost", $taxcost)->setValue("manufacturer", $manufacturer)->setValue("category", $category)->setValue("catalog", $catalog)
				->setValue("enterby", $enterby)->setValue("requestedby", $requestedby)->setValue("requesteddate", $requesteddate)->setValue("requireddate", $requireddate)
				->setValue("orderqty", $orderqty)->setValue("orderunit", $orderunit)->setValue("wonum", $wonum)->setValue("conversionfactor", $conversionfactor)
				->setValue("warehouse", $warehouse)->setValue("gldebitacct", $gldebitacct)->setValue("glcreditacct", $glcreditacct)->setValue("receiptstatus", "NONE")
				->setValue("prnum", $prnum)->setValue("prlinenum", $prlinenum)->setValue("budgetlinenum", $budgetlinenum)->setValue("serviceitemnum", $serviceitemnum)->add();
				
				(new Set())->useTransactionManager()->MboSetRemote("prline")->setWhere("prnum='$prnum' and prlinenum='$prlinenum'")->setValue("ponum", $ponum)->setValue("polinenum", $polinenum)->change();
				
			}
			$po_created = "po-created";
			(new Set())->useTransactionManager()->MboSetRemote("pr")->setWhere("prnum='$prnum'")->setValue("status", $po_created)->change();
			(new Set())->useTransactionManager()->MboSetRemote("prstatus")->setValue("prnum", $prnum)->setValue("status", $po_created)->setValue("changeby", $changeby)->setValue("changedate", $changedate)->setValue("remarks", "PO: $ponum was created")->add();
			$getAutoNum = new Super();
			$getAutoNum->getAutoNumber("po", "update_next_value");
			
			$paidcost = (new Set())->useTransactionManager()->MboSetRemote("popayment")->setWhere("ponum='$ponum'")->MboRemote("sum(paidcost) as paidcost ")->getMboSet()->getMbo()->getString("paidcost");
			if ($paidcost == null) $paidcost = 0;
			else $paidcost = $paidcost * $exchangerate;
			$unpaidcost = (($totalbasecost - $paidcost) * -1);
			
			(new Set())->useTransactionManager()->MboSetRemote("po")->setWhere("ponum='$ponum'")->setValue("unpaidcost", $unpaidcost)->change();
			Set::throws(["msg"=>"pocreated"]);
		}
		else {
			Set::throws(["msg"=>"poexist"]);
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
		
		$payload = Super::getParam("tbcx");
		$data = $payload["dataPackage"];
		$childrentable = $data["datasetChild"];
		$selected_rows = $data["selectedRows"];
		
		foreach($selected_rows as $row){
			foreach($row as $key=>$value){
				$prnum = (new Set())->MboSetRemote($childrentable)->setWhere("$key='$value'")->MboRemote("prnum")->getMboSet()->getMbo()->getString("prnum");
				$hasPonum = (new Set())->MboSetRemote("poline")->where("prnum=?", $prnum)->getMboSet();
				if($hasPonum->isEmpty()){
					$pmcampaignnum = (new Set())->MboSetRemote("pr")->setWhere("prnum='$prnum'")->getMboValue("pmcampaignnum");
					if(!empty($pmcampaignnum)){
						Super::sendJsonResponse("error", "The PR has Work Campaign Related Record: $pmcampaignnum");
					}else{
						$status = (new Set())->MboSetRemote("pr")->setWhere("prnum='$prnum'")->getMboValue("status");
						if($status == "new"){
							$_POST["delChildPackage"] = $data;
							Super::deleteChildren();
						}else{
							Super::sendJsonResponse("error", "Cannot delete PR $prnum because it has status: $status.");
						}
					}
				}else{
					$ponum = $hasPonum->getMbo()->getString("ponum");
					Super::sendJsonResponse("error", "The PRLINE has PO Related Record: $ponum");
				}
			}
		}
	}
}
	

?>