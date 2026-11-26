<?php
require_once ("../../../eat.php");

Class ActionSet {
	public static function rfqquotationAI() {
		$payload = Super::getParam("rfqquotationAI");
		$rfqnum = $payload["rfqnum"];
		$quotations = $payload["quotations"]["data"];

		$result = [
			"rfqnum" => $rfqnum,
			"vendors" => []
		];

		// 1. PERSIAPAN DATA (BATCH FETCHING)
		$allItemNums = [];
		$allVendors = [];
		
		foreach ($quotations as $q) {
			if (!empty($q['itemnum'])) $allItemNums[] = $q['itemnum'];
			if (!empty($q['vendor']))  $allVendors[] = $q['vendor'];
		}
		
		$allItemNums = array_unique($allItemNums);
		$allVendors = array_unique($allVendors);
		$leadTimeMap = [];

		// Hanya query jika ada item dan vendor
		if (!empty($allItemNums) && !empty($allVendors)) {
			// Ambil data lead time dari invvendor
			$invVendorSet = (new Set())->useTransactionManager()
				->MboSetRemote("invvendor")
				->whereIn("itemnum", $allItemNums)
				->whereIn("vendorcode", $allVendors)
				->MboRemote("vendorcode, itemnum, extleadtime")
				->getMboSet()
				->fetchData();

			// Buat Peta Pencarian: Kuncinya adalah "VENDOR_ITEM"
			foreach ($invVendorSet as $row) {
				$key = $row['vendorcode'] . '_' . $row['itemnum'];
				$leadTimeMap[$key] = $row['extleadtime'];
			}
		}

		// 2. PROSES DATA UTAMA

		foreach ($quotations as $q) {

			$vendor = $q["vendor"];
			$itemnum = $q["itemnum"];

			// Jika vendor belum dibuat → buat entry
			if (!isset($result["vendors"][$vendor])) {
				$result["vendors"][$vendor] = [
					"vendor_name" => $q["vendor_name"],
					"items" => [],
					"services" => [],
					"totalcost" => 0 
				];
			}

			// qty + unit
			$qtyunit = $q["quantity"] . " " . $q["orderunit"];

			// Hitung totalcost per entry
			$entry_total = floatval($q["unitcost"]) + floatval($q["linecost"]) + floatval($q["taxcost"]);

			// --- LOGIKA LEAD TIME ---
			// Cari di peta yang sudah kita buat di atas
			$lookupKey = $vendor . '_' . $itemnum;
			$leadTimeVal = "N/A"; // Default jika tidak ada

			if (isset($leadTimeMap[$lookupKey])) {
				// Kita bisa tambahkan "Days" agar AI lebih paham konteksnya
				$leadTimeVal = $leadTimeMap[$lookupKey] . " Days"; 
			}
			// ------------------------

			// Struktur entry dengan tambahan leadtime
			$entry = [
				"itemnum"     => $q["itemnum"],
				"description" => $q["description"],
				"qtyunit"     => $qtyunit,
				"totalcost"   => $entry_total,
				"leadtime"    => $leadTimeVal // <--- Data Baru
			];

			// Masukkan ke items atau services
			if ($q["linetype"] === "item") {
				$result["vendors"][$vendor]["items"][] = $entry;
			} else {
				$result["vendors"][$vendor]["services"][] = $entry;
			}

			// Tambahkan ke totalcost per vendor
			$result["vendors"][$vendor]["totalcost"] += $entry_total;
		}

	
		$friendlyPayload = json_encode($result);
		$endpoint = "/generate/vendor-price-comparation";
		$resultAI = Super::sendToML($endpoint, $friendlyPayload, true); 
		$dataAI = json_decode($resultAI, true);

		
		if ($dataAI) {
			$toon = $dataAI['toon_data'] ?? null;
			$rfqnum = $dataAI['rfqnum'] ?? null;
			
			$rfqDescription = "";
			if (!empty($rfqnum)) {
				$rfqDescription = (new Set())->MboSetRemote("rfq")
					->where("rfqnum=?", $rfqnum)
					->getMboValue("description");
			}
			
			$header = "RFQ: $rfqnum - $rfqDescription <hr>";
			$narrative_analysis = $dataAI['narrative_analysis'] ?? "";
			Super::sendJsonResponse("success", $narrative_analysis);

		}
	}
	
	
	public static function createpotransaction() {
		
		$payload = Super::getParam("createpotransaction");
		$rfqnum  = $payload["rfqnum"];
		$data    = $payload["data"]; // array vendor + ponum

		$rfqSet = (new Set())->MboSetRemote("rfq")->where("rfqnum=?", $rfqnum)->getMboSet()->fetch();
		$description = $rfqSet["description"];
		$shiptoaddress = $rfqSet["shiptoaddress"];
		$billtoaddress = $rfqSet["billtoaddress"];
		$exchangerate = $rfqSet["exchangerate"];
		
		if(empty($exchangerate)) {
			Super::sendJsonResponse("error", "Exchange Rate is required");
		}

		if($exchangerate <= 0) {
			Super::sendJsonResponse("error", "Exchange Rate must be more than 0");
		}

		$enterby = Xbo::getLoginUser();
		if(empty($enterby)){
			Super::sendJsonResponse("error", "Login User does not exist. Please Log Out and Login again");
		}
		$orginfo = Xbo::getOrgcode($enterby);
		$orgcode = $orginfo->orgcode;
		$sitecode = $orginfo->sitecode;
		$currencycode = $rfqSet["exchangerate"];
		if(empty($currencycode)){
			Super::sendJsonResponse("error", "Currency Code is required.");
		}

		$getCurrencyCode = Xbo::getBaseCurrency(true);
		$currencycode = $getCurrencyCode["basecurrency"];
		
	
		
		$polines = []; // array final untuk semua rfqquotation
		$po = [];
		$poTotals = [];
		
		foreach ($data as $row) {

			$vendor = $row["vendor"];
			$ponum  = $row["ponum"];
			$poTotals[$ponum] = [
				"totalLineCost" => 0,
				"totalTaxCost"  => 0
			];

			$poBudget[$ponum] = [
				"budgetnum" => null
			];
			
			
			$priority = "normal";
			$receipt = "NONE";
			$status = "new";
			$statusdate = Xbo::setEnterDate();
			$requireddate = null;
			$orderdate = $statusdate;
			//Buat PO Header 
			$po[] = [
				"ponum"=>$ponum, 
				"description"=>$description,
				"shiptoaddress"=>$shiptoaddress,
				"billtoaddress"=>$billtoaddress,
				"enterby"=>$enterby,
				"orgcode"=>$orgcode,
				"sitecode"=>$sitecode,
				"currencycode"=>$currencycode,
				"exchangerate"=>$exchangerate,
				"vendor"=>$vendor,
				"priority"=>$priority,
				"receipt"=>$receipt,
				"status"=>$status,
				"statusdate"=>$statusdate,
				"requireddate"=>$requireddate,
				"orderdate"=>$orderdate
			];

			// Ambil rfqquotation untuk vendor tertentu
			$rfqquotations = (new Set())->useTransactionManager()
				->MboSetRemote("rfqquotation")
				->where("rfqnum=? AND vendor=?", $rfqnum, $vendor)
				->getMboSet()
				->fetchData();
			// Proses tiap rfqquotation
		
			foreach ($rfqquotations as &$q) {
				// 1. Tambahkan key ponum
				$q["ponum"] = $ponum;
				$q["tax"] = $q["taxcode"];
				$q["orderqty"] = $q["quantity"];
				$prnum = $q["prnum"];
				$prlinenum = $q["prlinenum"];

				$rfqlineSet = (new Set())->useTransactionManager()->MboSetRemote("rfqline")->where("rfqnum=? and prnum=? and prlinenum=?", $rfqnum, $prnum, $prlinenum)
				->getMboSet();

				$rfqline = $rfqlineSet->getMbo();
				$gldebitacct = $rfqline->getString("gldebitacct");
				$glcreditacct = $rfqline->getString("glcreditacct");
				$warehouse = $rfqline->getString("warehouse");
				$wonum = $rfqline->getString("wonum");
				$rfqlinenum = $rfqline->getString("rfqlinenum");

				//getBudgetLinenum 
				$budgetlinenum = (new Set())->useTransactionManager()->MboSetRemote("prline")->where("prnum=? and prlinenum=?", $prnum, $prlinenum)->getMboValue("budgetlinenum");
				$budgetnum_value = (new Set())->useTransactionManager()->MboSetRemote("pr")->where("prnum=?", $prnum)->getMboValue("budgetnum");

				
				if(!empty($budgetnum_value)) {
					$poBudget[$ponum]["budgetnum"] = $budgetnum_value;
				}



				$q["gldebitacct"] = $gldebitacct;
				$q["glcreditacct"] = $glcreditacct;
				$q["warehouse"] = $warehouse;
				$q["wonum"] = $wonum;
				$q["polinenum"] = $rfqlinenum;
				$q["budgetlinenum"] = $budgetlinenum;

				// 2. Hapus key yang tidak dibutuhkan
				unset($q["vendor"]);
				unset($q["awarded"]);
				unset($q["remarks2"]);
				unset($q["rfqquotationid"]);
				unset($q["taxcode"]);
				unset($q["quantity"]);

				 $poTotals[$ponum]["pretaxtotal"] += isset($q["linecost"]) ? $q["linecost"] : 0;
       			 $poTotals[$ponum]["totaltax"]  += isset($q["taxcost"]) ? $q["taxcost"] : 0;

				 (new Set)->MboSetRemote("rfqquotation")->where("rfqnum=? AND rfqlinenum=? AND vendor=?", $rfqnum, $rfqlinenum, $vendor)
				->setValue("ponum", $ponum)
				->setValue("polinenum", $rfqlinenum)
				->change();
			}
			$polines = array_merge($polines, $rfqquotations);
		}

		foreach ($po as &$p) {
			$ponum = $p["ponum"];
			$p["pretaxtotal"] = $poTotals[$ponum]["pretaxtotal"];
			$p["totaltax"]  = $poTotals[$ponum]["totaltax"];
			$p["budgetnum"]  = $poBudget[$ponum]["budgetnum"];
			$p["pretaxtotaldiscount"]  = $p["pretaxtotal"];
			$p["totaltax2"]  = $p["totaltax"];

			$p["totalcost"] = $p["pretaxtotaldiscount"]+$p["totaltax2"];
			$p["totalbasecost"] = $p["totalcost"] * $exchangerate;
			$p["paidcost"] = 0;
			$p["unpaidcost"] = $p["paidcost"]-$p["totalbasecost"];

		}

		//Masukkan ke tabel PO 
		(new Set())->useTransactionManager()->MboSetRemote("po")->addStream($po);
		//Masukkan ke tabel POLINE
		(new Set())->useTransactionManager()->MboSetRemote("poline")->addStream($polines);
   		// Kirim response JSON ke frontend
   		Super::sendJsonResponse("success", "PO created successfully.", $po);
	}
	public static function createcontracttransaction() {
		
		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();

		try {
			// 1. Ambil Payload
			$payload = Super::getParam("createcontracttransaction");
			$rfqnum  = $payload["rfqnum"];
			$data    = $payload["data"]; 

			// 2. Ambil Data Header RFQ
			$rfqSet = (new Set())->useTransactionManager()
				->MboSetRemote("rfq")
				->where("rfqnum=?", $rfqnum)
				->getMboSet()
				->fetch();

			if (!$rfqSet) {
				throw new Exception("RFQ $rfqnum not found.");
			}

			$descriptionRaw = $rfqSet["description"];
			$enterby = Xbo::getLoginUser();
			$currentDate = Xbo::setEnterDate();
			$orginfo = Xbo::getOrgcode($enterby);
			
			// --- STEP TAMBAHAN: PRE-FETCH TERMS RFQ ---
			// Ambil Terms & Conditions (ToC)
			$rfqTerms = (new Set())->useTransactionManager()
				->MboSetRemote("rfqterm")
				->where("rfqnum=?", $rfqnum)
				->getMboSet()
				->fetchData();

			// Ambil Terms of Payment (ToP)
			$rfqPayTerms = (new Set())->useTransactionManager()
				->MboSetRemote("rfqterm2")
				->where("rfqnum=?", $rfqnum)
				->getMboSet()
				->fetchData();
			// ------------------------------------------

			// Array Penampung Batch Insert
			$contractsToInsert = [];
			$linesToInsert = [];
			$termsToInsert = [];      // Untuk contractterm
			$payTermsToInsert = [];   // Untuk contractterm2

			// 4. Loop per Vendor
			foreach ($data as $row) {
				$vendor = $row["vendor"];
				$contractnum = $row["contractnum"];

				// Validasi unik
				$exist = (new Set())->useTransactionManager()
					->MboSetRemote("purchcontract")
					->where("contractnum=?", $contractnum)
					->getMboSet();
				
				if (!$exist->isEmpty()) {
					throw new Exception("Contract Number $contractnum already exists.");
				}

				$totalCostHeader = 0; 
				$lineSeq = 10;

				// 4a. Lines Processing
				$quotations = (new Set())->useTransactionManager()
					->MboSetRemote("rfqquotation")
					->where("rfqnum=? AND vendor=? AND awarded='Y'", $rfqnum, $vendor)
					->getMboSet()
					->fetchData();

				if (empty($quotations)) continue; 

				foreach ($quotations as $q) {
					$rfqlinenum = $q["rfqlinenum"];
					$unitcost = (float)$q["unitcost"]; 
					
					$rfqLineData = (new Set())->useTransactionManager()
						->MboSetRemote("rfqline")
						->where("rfqnum=? AND rfqlinenum=?", $rfqnum, $rfqlinenum)
						->getMboSet()
						->fetch();

					if (!$rfqLineData) continue;

					$qty = (float)$rfqLineData["orderqty"];
					$lineTotal = $qty * $unitcost;
					$totalCostHeader += $lineTotal;

					$linetype = $rfqLineData["linetype"];
					$linetype = strtolower($linetype);
					if(empty($linetype)) {
						$linetype = !empty($rfqLineData["itemnum"]) ? "item" : "services";
					}

					$linesToInsert[] = [
						"contractnum"     => $contractnum,
						"contractlinenum" => $lineSeq,
						"linetype"        => strtolower($linetype),
						"itemnum"         => $rfqLineData["itemnum"],
						"serviceitemnum"  => $rfqLineData["serviceitemnum"],
						"description"     => $rfqLineData["description"],
						"orderunit"       => $rfqLineData["orderunit"],
						"minqty"          => 1,
						"taxcode"         => $q["taxcode"],
						"unitcost"        => $unitcost,
						"remark"          => "Generated from RFQ " . $rfqnum,
						"enterby"         => $enterby,
						"enterdate"       => $currentDate,
						"changeby"        => $enterby,
						"changedate"      => $currentDate
					];
					$lineSeq += 10;
				}

				// --- STEP TAMBAHAN: MAPPING TERMS KE CONTRACTNUM BARU ---
				
				// 1. Map Terms & Conditions (ToC)
				if (!empty($rfqTerms)) {
					foreach ($rfqTerms as $rt) {
						$termsToInsert[] = [
							"contractnum" => $contractnum,
							"termlinenum" => $rt["termlinenum"],
							"termcode"    => $rt["termcode"],
							"description" => $rt["description"],
							"ownership"   => $rt["ownership"] ?? null,
							"enterby"     => $enterby,
							"enterdate"   => $currentDate,
							"changeby"    => $enterby,
							"changedate"  => $currentDate
						];
					}
				}

				// 2. Map Terms of Payment (ToP)
				if (!empty($rfqPayTerms)) {
					foreach ($rfqPayTerms as $rpt) {
						$payTermsToInsert[] = [
							"contractnum" => $contractnum,
							"termlinenum" => $rpt["termlinenum"],
							"termcode"    => $rpt["termcode"],
							"remarks"     => $rpt["remarks"] ?? null,
							"description" => $rpt["description"],
							"ownership"   => $rpt["ownership"] ?? null,
							"enterby"     => $enterby,
							"enterdate"   => $currentDate,
							"changeby"    => $enterby,
							"changedate"  => $currentDate
						];
					}
				}
				// ---------------------------------------------------------

				// 4c. Header Processing
				$contractsToInsert[] = [
					"contractnum"  => $contractnum,
					"description"  => "Contract from " . $rfqnum . " - " . $descriptionRaw,
					"contracttype" => "PRICE",
					"vendorcode"   => $vendor,
					"status"       => "DRAFT",
					"statusdate"   => $currentDate,
					"startdate"    => $currentDate,
					"enddate"      => date('Y-m-d H:i:s', strtotime($currentDate . ' + 1 year')),
					"totalcost"    => $totalCostHeader,
					"amountspent"  => 0,
					"rfqnum"       => $rfqnum,
					"buyer"        => $enterby,
					"currencycode" => $rfqSet["currencycode"] ?? 'IDR',
					"enterby"      => $enterby,
					"enterdate"    => $currentDate,
					"orgcode"      => $orginfo->orgcode,
					"sitecode"     => $orginfo->sitecode
				];
			}

			// 5. Eksekusi Batch Insert
			if (!empty($contractsToInsert)) {
				(new Set())->useTransactionManager()->MboSetRemote("purchcontract")->addStream($contractsToInsert);
			}

			if (!empty($linesToInsert)) {
				(new Set())->useTransactionManager()->MboSetRemote("contractline")->addStream($linesToInsert);
			}

			// Insert Terms
			if (!empty($termsToInsert)) {
				(new Set())->useTransactionManager()->MboSetRemote("contractterm")->addStream($termsToInsert);
			}

			// Insert Pay Terms
			if (!empty($payTermsToInsert)) {
				(new Set())->useTransactionManager()->MboSetRemote("contractterm2")->addStream($payTermsToInsert);
			}

			$tm->commit();
			Super::sendJsonResponse("success", "Contract(s) created successfully.", $data);

		} catch (Exception $e) {
			$tm->rollback();
			Super::sendJsonResponse("error", "Failed to create contract: " . $e->getMessage());
		}
	}


	public static function createcontract(){
		$rfqnum = Super::getParam("createcontract");

		//cek di Purch. Contract apakah ada rfq terkait. 
		$purchContractSet = (new Set())->MboSetRemote("purchcontract")->where("rfqnum=?", $rfqnum)->getMboSet("distinct");
		if(!$purchContractSet->isEmpty()){
			$contract = $purchContractSet->getMbo();
			$contractnum = $contract->getString("contractnum");
			Super::sendJsonResponse("error", "Contract : $contractnum has already been exist", $purchContractSet); //auto exit php
		}

		//Get Warded vendor 
		$rfqquotationSet = (new Set())
			->useTransactionManager()
			->MboSetRemote("rfqquotation")
			->where("rfqnum=? AND awarded=?", $rfqnum, 'Y')
			->MboRemote("vendor")
			->getMboSet("distinct")
			->fetchData();
			//select vendor from rfqquotation WHERE(rfqnum='RFQ-018-2023'AND awarded='Y')
    
		if (empty($rfqquotationSet)) {
			Super::sendJsonResponse("error", "Awarded Vendor not found.");
		}

		// Tambah key baru pada setiap data vendor
		foreach ($rfqquotationSet as &$data) {

			$getAutoNum = new Super();
			$contractnum = $getAutoNum->getAutoNumber("purchcontract", "update_next_value")->autonumber;
			// Tambahkan key baru
			$data["new_contractnum"] = $contractnum;
		}
	
		Super::sendJsonResponse("success", "Awarded Vendor Selected", $rfqquotationSet); // kirim awarded vendor data

	}


	public static function createpo(){
		$rfqnum = Super::getParam("createpo");
		

		//cek di poline apakah ada rfq terkait. 

		$polineSet = (new Set())->MboSetRemote("poline")->where("rfqnum=?", $rfqnum)->getMboSet("distinct");
		if(!$polineSet->isEmpty()){

			$ponums = [];
			while($result = $polineSet->fetch()){
				$ponums [] = $result["ponum"];
			}
			$ponums = array_unique($ponums);

			$ponum = implode(",", $ponums);
			Super::sendJsonResponse("error", "PO : $ponum has already been exist", $polineSet);
		}
		//get Awarded Vendor List
	
		$rfqquotationSet = (new Set())
			->useTransactionManager()
			->MboSetRemote("rfqquotation")
			->where("rfqnum=? AND awarded=?", $rfqnum, 'Y')
			->MboRemote("vendor")
			->getMboSet("distinct")
			->fetchData();

		if (empty($rfqquotationSet)) {
			Super::sendJsonResponse("error", "Awarded Vendor not found.");
		}

		// Tambah key baru pada setiap data vendor
		foreach ($rfqquotationSet as &$data) {

			$getAutoNum = new Super();
			$ponum = $getAutoNum->getAutoNumber("po", "update_next_value")->autonumber;
			// Tambahkan key baru
			$data["new_ponum"] = $ponum;
		}
		Super::sendJsonResponse("success", "", $rfqquotationSet);

	}
	public static function selectWinner(){ 
		$rfqvendorid = Super::getParam("selectWinner")[0]["rfqvendorid"];
		$rfqnum = Super::getParam("rfqnum");

		if(!$rfqvendorid) {
			Super::sendJsonResponse("error", "No Vendor has been selected");
		}
		$vendor = (new Set())->useTransactionManager()->MboSetRemote("rfqvendor")->where("rfqnum=? AND rfqvendorid=?", $rfqnum,$rfqvendorid )
		->getMboValue("vendor");
		
		//unselect existing awarded vendor. 
		(new Set())->useTransactionManager()->MboSetRemote("rfqquotation")->where("rfqnum=?", $rfqnum)->setValue("awarded", "N")->change();

		$rfqquotationSet = (new Set())->useTransactionManager()->MboSetRemote("rfqquotation")->where("rfqnum=? AND vendor=?", $rfqnum,$vendor)->getMboSet();
		if($rfqquotationSet->isEmpty()){
			Super::sendJsonResponse("error", "No Quotation Found for Vendor: $vendor ");
		}

		(new Set())->useTransactionManager()->MboSetRemote("rfqquotation")->where("rfqnum=? AND vendor=?", $rfqnum, $vendor )->setValue("awarded", "Y")->change();

		Super::sendJsonResponse("success", "Vendor $vendor has been awarded for RFQ: $rfqnum");
	}
	public static function submitNtp(){
		//create NTP
		$submitNtp = Super::getParam("submitNtp");
		$rfqnum = Super::getParam("rfqnum");
	

		$ntpnumber       = isset($submitNtp["ntp_number"]) ? $submitNtp["ntp_number"] : "";
		$issuedate       = isset($submitNtp["issue_date"]) ? $submitNtp["issue_date"] : "";
		$vendor          = isset($submitNtp["vendor_name"]) ? $submitNtp["vendor_name"] : "";
		$worktitle       = isset($submitNtp["work_title"]) ? $submitNtp["work_title"] : "";
		$contractnumber  = isset($submitNtp["contract_number"]) ? $submitNtp["contract_number"] : "";
		$contractvalue   = isset($submitNtp["contract_value"]) ? str_replace(".", "", $submitNtp["contract_value"]) : 0; // hapus titik
		$startdate       = isset($submitNtp["start_date"]) ? $submitNtp["start_date"] : "";
		$enddate         = isset($submitNtp["end_date"]) ? $submitNtp["end_date"] : "";
		$projectlocation = isset($submitNtp["project_location"]) ? $submitNtp["project_location"] : "";
		$ownerpic        = isset($submitNtp["owner_pic"]) ? $submitNtp["owner_pic"] : "";
		$ownercontact    = isset($submitNtp["owner_contact"]) ? $submitNtp["owner_contact"] : "";
		$notes           = isset($submitNtp["notes"]) ? $submitNtp["notes"] : "";


		 // ===== VALIDASI DUPLIKAT =====
		 //Langsung Hapus!
		(new Set())->MboSetRemote("rfqntp")->where("rfqnum = ? ", $rfqnum)->remove();

		$existing = (new Set())->MboSetRemote("rfqntp")
			->where("rfqnum = ? AND ntpnum=?", $rfqnum, $ntpnumber)
			->getMboSet();
			
		if(!$existing->isEmpty()){
			// jika sudah ada, kirim error
			Super::sendJsonResponse("error", "NTP Number '$ntpnumber' for RFQ '$rfqnum' already exists!", null);
			
		}


    	// Insert ke tabel rfqntp
		(new Set())->MboSetRemote("rfqntp")
			->setValue("rfqnum", $rfqnum)
			->setValue("ntpnum", $ntpnumber)
			->setValue("issuedate", $issuedate)
			->setValue("vendor", $vendor)
			->setValue("worktitle", $worktitle)
			->setValue("contractnumber", $contractnumber)
			->setValue("contractvalue", $contractvalue)
			->setValue("startdate", $startdate)
			->setValue("enddate", $enddate)
			->setValue("projectlocation", $projectlocation)
			->setValue("ownerpic", $ownerpic)
			->setValue("ownercontact", $ownercontact)
			->setValue("notes", $notes)
			->add();
		Super::sendJsonResponse("success", "NTP submitted successfully!", $data);
	}

	public static function getVendorTotalCost(){
		$payload = Super::getParam("getVendorTotalCost");
		$rfqnum = $payload["rfqnum"];
		$vendorcode = $payload["vendorname"];

		$total_cost = (new Set())->MboSetRemote("rfqquotation")->where("rfqnum=? AND vendor=?", $rfqnum, $vendorcode)
		->MboRemote("sum(linecost) as total_cost")->getMboSet()->getMbo()->getString("total_cost");
		$total_cost = floatval($total_cost);
		Super::sendJsonResponse("success", $total_cost);
	
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
    public static function generateVendorStatements($chart_data, $ext_leadtime_data) {
        $vendors = array_keys($ext_leadtime_data); // Get vendors from lead time data
        $numVendors = count($vendors);

        // Determine the most and least expensive vendors
        $vendorPrices = [];
        foreach ($chart_data as $item) {
            if (!isset($vendorPrices[$item['vendor']])) {
                $vendorPrices[$item['vendor']] = 0;
            }
            $vendorPrices[$item['vendor']] += $item['linecost']; //sum total linecost per vendor
        }

       if (!empty($vendorPrices)) {
			$mostExpensiveVendor = array_search(max($vendorPrices), $vendorPrices);
			$leastExpensiveVendor = array_search(min($vendorPrices), $vendorPrices);
		} else {
			$mostExpensiveVendor = null;
			$leastExpensiveVendor = null;
		}

        // Determine lead time range
        $leadTimes = array_values($ext_leadtime_data ?? []);

		if (!empty($leadTimes)) {
			$minLeadTime = min($leadTimes);
			$maxLeadTime = max($leadTimes);
		} else {
			$minLeadTime = null; // atau nilai default lain, misal 0
			$maxLeadTime = null;
		}

        // Statement fokus pada Harga
        $price_statement = "Based on the RFQ data, " . $mostExpensiveVendor . " tends to offer the highest prices, while " . $leastExpensiveVendor . " seems the most economical overall.";

        // Statement fokus pada Lead Time
        $leadtime_statement = "The lead times among all vendors (" . implode(", ", $vendors) . ") are relatively similar, ranging from " . $minLeadTime . " to " . $maxLeadTime . " days.";

        // Statement fokus pada Harga dan Lead Time
        $price_leadtime_statement = "Considering the comparable lead times, the choice of vendor should be based on careful price analysis and other factors beyond leadtime, with a specific focus on the most important items and budget constraints.";
        return [
            "price_statement" => $price_statement,
            "leadtime_statement" => $leadtime_statement,
            "price_leadtime_statement" => $price_leadtime_statement
        ];
    }


	
public static function vendorpriceChart() {
    $rfqnum = Super::getParam("vendorpriceChart");

    $conn = DB::getMXConnection(); // Mendapatkan koneksi database

    $sql = "SELECT rfqnum, rfqquotation.vendor, vendor.name AS vendor_name, linetype, itemnum, description, quantity, orderunit, rfqquotationid, taxcode, taxcost, unitcost, linecost
            FROM rfqquotation
            LEFT JOIN vendor ON vendor.vendorcode = rfqquotation.vendor
            WHERE rfqnum = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $rfqnum);
    $stmt->execute();
    $result = $stmt->get_result();

    $chart_data = array();
    $vendors = array(); // Array untuk menyimpan vendor yang terkait dengan RFQ ini
    while ($row = $result->fetch_assoc()) {
        $chart_data[] = $row;
        $vendors[] = $row['vendor']; // Tambahkan vendor ke array
    }
    $stmt->close();

    $sqlExternal = "SELECT
                        po.VENDOR,
                        DATEDIFF(MAX(matrectrans.ACTUALDATE), po.ORDERDATE) AS EXTERNAL_LEADTIME
                    FROM
                        po
                    JOIN
                        matrectrans ON po.PONUM = matrectrans.PONUM
                    WHERE
                        po.STATUS IN ('sent')
                        AND YEAR(po.ORDERDATE) = '2024'
                        AND po.VENDOR IN ('" . implode("','", $vendors) . "')  
                    GROUP BY
                        po.VENDOR";

    $stmtExternal = $conn->prepare($sqlExternal);
    $stmtExternal->execute();
    $resultExternal = $stmtExternal->get_result();

    $ext_leadtime_data = array();
    while ($rowExternal = $resultExternal->fetch_assoc()) {
        $ext_leadtime_data[$rowExternal['VENDOR']] = $rowExternal['EXTERNAL_LEADTIME'];
    }
    $stmtExternal->close();

    $conn->close();

    // tambahkan 3 jenis statement diatas disini, statement dalam bahasa inggris dengan jenis statement yang bervariasi . generated statement dibuat dalam fungsi baru agar scalable.
    $statements = self::generateVendorStatements($chart_data, $ext_leadtime_data);
    Set::throws(["data" => $chart_data, "data_ext_leadtime" => $ext_leadtime_data, "price_statement"=>$statements["price_statement"], "leadtime_statement"=>$statements["leadtime_statement"], "price_leadtime_statement"=>$statements["price_leadtime_statement"]]);
}


	public static function copypr(){
		//Copy PR LINE TO RFQLINE
		$rfqnum = Super::getParam("rfqnum");
		$copypr = Super::getParam("copypr");
		$enterdate = Xbo::setEnterDate();
		$enterby = Xbo::getLoginUser();

		$prids = [];

		if (isset($_POST['copypr']) && is_array($_POST['copypr'])) {
			foreach ($_POST['copypr'] as $item) {
				if (isset($item['prid'])) {
					$prids[] = $item['prid'];
				}
			}
		}

		$prset = (new Set())->useTransactionManager()->MboSetRemote("prline prl")
		->setRelationship("pr", "pr.prnum = prl.prnum")
		->whereIn("pr.prid", $prids)
		->MboRemote("prl.prnum")->MboRemote("prl.linetype")->MboRemote("prl.itemnum")->MboRemote("prl.prlinenum")->MboRemote("prl.description")->MboRemote("prl.unitcost")
		->MboRemote("prl.linecost")->MboRemote("prl.tax as taxcode")->MboRemote("prl.taxcost")->MboRemote("prl.orderqty as quantity")->MboRemote("prl.orderunit")
		->MboRemote("prl.conversionfactor")->MboRemote("prl.warehouse")->MboRemote("prl.wonum")->MboRemote("prl.glcreditacct")->MboRemote("prl.gldebitacct")
		->MboRemote("prl.manufacturer")->MboRemote("prl.category")->MboRemote("prl.catalog")->MboRemote("prl.partnum")->MboRemote("prl.serviceitemnum")
		->MboRemote("pr.requireddate")->MboRemote("pr.requestedby")
		->getMboSet()->fetchData();

		$counter = 10;
		
		foreach ($prset as $key => $row) {
			$prset[$key]['prlinenum'] = (string)$counter;
			$prset[$key]['rfqnum'] = $rfqnum;
			$prset[$key]['rfqlinenum'] = (string)$counter;
			$counter += 10;
		}
		
		
		(new Set())->MboSetRemote("rfqline")->addStream($prset);
		Super::sendJsonResponse("success", "All PR Lines has been copied", $prset);
		
	}
	public static function AddQuotationLines(){
		$rfqnum = $_POST["rfqnum"];
		$addquote = $_POST["addquotationline"];
		$countrfqlines = count($addquote);
		$rfqvendorid = $_POST["rfqvendor"]["xxtableidvalue"];
		
		$setv = new Set();
		$rfqvendorSet = $setv->MboSetRemote("rfqvendor")->setWhere("rfqvendorid='$rfqvendorid'")->MboRemote("vendor")->getMboSet();
		$rfqvendor = $rfqvendorSet->getMbo();
		$vendor = $rfqvendor->getString("vendor");
		
		for($i=0; $i<$countrfqlines; $i++){
			$set = new Set();
			$rfqlineid = $addquote[$i]["rfqlinesid"];
			
			$set = new Set();
			$rfqlineSet = $set->MboSetRemote("rfqline")->setWhere("rfqlineid = $rfqlineid")->MboRemote("rfqnum")->MboRemote("rfqlinenum")->MboRemote("linetype")
			->MboRemote("itemnum")->MboRemote("description")->MboRemote("quantity")->MboRemote("conversionfactor")->MboRemote("orderunit")->MboRemote("partnum")
			->MboRemote("category")->MboRemote("serviceitemnum")->MboRemote("requestedby")
			->MboRemote("catalog")->MboRemote("prnum")->MboRemote("prlinenum")
			->MboRemote("warehouse")->MboRemote("wonum")->MboRemote("gldebitacct")->MboRemote("glcreditacct")
			->getMboSet();
			
			$rfqline = $rfqlineSet->getMbo();
			$rfqnum = $rfqline->getString("rfqnum");
			$rfqlinenum = $rfqline->getString("rfqlinenum");
			$linetype = $rfqline->getString("linetype");
			$itemnum = $rfqline->getString("itemnum");
			$description = $rfqline->getString("description");
			$quantity = $rfqline->getString("quantity");
			$conversionfactor = $rfqline->getString("conversionfactor");
			$orderunit = $rfqline->getString("orderunit");
			$partnum = $rfqline->getString("partnum");
			$category = $rfqline->getString("category");
			$catalog = $rfqline->getString("catalog");
			$prnum = $rfqline->getString("prnum");
			$prlinenum = $rfqline->getString("prlinenum");
			$serviceitemnum = $rfqline->getString("serviceitemnum");
			$requestedby = $rfqline->getString("requestedby");
			$warehouse = $rfqline->getString("warehouse");
			$wonum = $rfqline->getString("wonum");
			$gldebitacct = $rfqline->getString("gldebitacct");
			$glcreditacct = $rfqline->getString("glcreditacct");

			$set2 = new Set();
			$rfqlinenumquotation = $set2->MboSetRemote("rfqquotation")->setWhere("rfqnum = '$rfqnum' and vendor='$vendor' and rfqlinenum='$rfqlinenum'")->MboRemote("rfqlinenum")->getMboSet()->getMbo()->getString("rfqlinenum");
			if(empty($rfqlinenumquotation)) {
				$set3 = new Set();
				$set3->MboSetRemote("rfqquotation")
				->setValue("rfqnum", $rfqnum)
				->setValue("vendor", $vendor)
				->setValue("rfqlinenum", $rfqlinenum)
				->setValue("linetype", $linetype)
				->setValue("itemnum", $itemnum)
				->setValue("description", $description)
				->setValue("quantity", $quantity)
				->setValue("conversionfactor", $conversionfactor)
				->setValue("orderunit", $orderunit)
				->setValue("partnum", $partnum)
				->setValue("category", $category)
				->setValue("awarded", 'N')
				->setValue("catalog", $catalog)
				->setValue("prnum", $prnum)
				->setValue("prlinenum", $prlinenum)
				->setValue("serviceitemnum", $serviceitemnum)
				->setValue("requestedby", $requestedby)
				->setValue("gldebitacct", $gldebitacct)
				->setValue("glcreditacct", $glcreditacct)
				->setValue("wonum", $wonum)
				->setValue("warehouse", $warehouse)
				->add();	
			}else{
				continue;
			}
			
			
		}
		
		Set::throws(["msg"=>"added"]);
	}
	public static function CopyPRLines()
	{
		$key = $_POST["key"];
		$set = new Set();
		$prlines = $_POST["copyprline"];
		$schemaSet = $set->getAllColumns("prline");
		$columnsSet = $schemaSet->result;
		$countResult = $columnsSet->counts();
		$columnr = [];
		for ($i = 0; $i < $countResult; $i++) {
			$columns = $columnsSet->getMbo();
			$column = $columns->getString("columnname");
			$column = strtolower($column);
			array_push($columnr, $column);
		}
		
		$set = new Set();
		$prlineSet = $set->MboSetRemote("prline");
		$prliner = [];
		for ($j = 0; $j < count($prlines); $j++) {
			$prlineid = str_replace("`", "=", $prlines[$j]);
			array_push($prliner, $prlineid);
		}
		$clause = implode(" or ", $prliner);
		$prlineSet = $prlineSet->setWhere($clause);
		for ($i = 0; $i < count($columnr); $i++) {
			$prlineSet = $prlineSet->MboRemote($columnr[$i]);
		}
		$prlineSet = $prlineSet->getMboSet();
		$countprline = $prlineSet->counts();
		$poid = explode("^", $_POST["key"]) [1];
		
		$setpo = new Set();
		$po = $setpo->MboSetRemote("po")->setWhere("poid='$poid'")->MboRemote("ponum")->MboRemote("orgcode")->MboRemote("sitecode")->getMboSet()->getMbo();
		
		$orgcode = $po->getString("orgcode");
		$sitecode = $po->getString("sitecode");
		$ponum = $po->getString("ponum");
		$enterdate = Xbo::setEnterDate();
		$enterby = Xbo::getLoginUser();
		$changeby = $enterby;
		$changedate = $enterdate;
		$set2 = new Set();
		$set3 = new Set();
		for ($i = 0; $i < $countprline; $i++) {
			$prline = $prlineSet->getMbo();
			$prnum = $prlineSet->getString("prnum");
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
			
			$prs = new Set();
			$pr = $prs->MboSetRemote("pr")->setWhere("prnum='$prnum'")->MboRemote("requesteddate")->MboRemote("requestedby")->getMboSet()->getMbo();
			$requestedby = $pr->getString("requestedby");
			$requesteddate = $pr->getString("requesteddate");
			
			$prs = new Set();
			$nextpolinenum = $prs->MboSetRemote("poline")->setWhere("ponum='$ponum'")->MboRemote("max(polinenum) as nextpolinenum")->getMboSet()->getMbo()->getString("nextpolinenum");
			if (empty($nextpolinenum)) $nextpolinenum = 1;
			else $nextpolinenum = $nextpolinenum + 1;
			
			$set2 = new Set();
			$set2->MboSetRemote("poline")->setValue("ponum", $ponum)->setValue("polinenum", $nextpolinenum)->setValue("linetype", $linetype)
			->setValue("itemnum", $itemnum)->setValue("partnum", $partnum)->setValue("description", $description)
			->setValue("conditiontype", $conditiontype)->setValue("unitcost", $unitcost)->setValue("linecost", $linecost)
			->setValue("tax", $tax)->setValue("taxcost", $taxcost)->setValue("manufacturer", $manufacturer)
			->setValue("category", $category)->setValue("catalog", $catalog)->setValue("enterby", $enterby)
			->setValue("requestedby", $requestedby)->setValue("requesteddate", $requesteddate)
			->setValue("requireddate", $requireddate)->setValue("orderqty", $orderqty)
			->setValue("orderunit", $orderunit)->setValue("wonum", $wonum)
			->setValue("conversionfactor", $conversionfactor)
			->setValue("warehouse", $warehouse)->setValue("gldebitacct", $gldebitacct)
			->setValue("glcreditacct", $glcreditacct)->setValue("receiptstatus", "NONE")
			->setValue("prnum", $prnum)->setValue("prlinenum", $prlinenum)->add();
			
			$set3 = new Set();
			$set3->MboSetRemote("prline")->setWhere("prnum='$prnum' and prlinenum='$prlinenum'")->setValue("ponum", $ponum)->setValue("polinenum", $nextpolinenum)->change();
		}
	}
	public static function ValidateShowChildren()

	{
		$pts = explode("|", $_GET["validateshowchildren"]);
		if ($pts[3] == "poline") {
			$set = new Set();
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
		$set = new Set();
		$polineSet = $set->MboSetRemote("poline")->setWhere("ponum='$ponum' and prnum is not null")->MboRemote("prnum")->MboRemote("prlinenum")->MboRemote("polinenum")->getMboSet();
		$countpoline = $polineSet->counts();
		$statusdate = Xbo::setEnterDate();
		for ($i = 0; $i < $countpoline; $i++) {
			$set2 = new Set();
			$polinenum = $poline->getString("polinenum");
			$set2 = new Set();
			$set2->MboSetRemote("poline")->setWhere("ponum='$ponum'")->setValue("prnum", null)->setValue("prlinenum", null)->change();
			$set2 = new Set();
			$set2->MboSetRemote("pr")->setWhere("prnum='$prnum'")->setValue("status", "approved")->setValue("statusdate", $statusdate)->change();
			$poline = $polineSet->getMbo();
			$prnum = $poline->getString("prnum");
			$prlinenum = $poline->getString("prlinenum");
			$set2 = new Set();
			$set2->MboSetRemote("prline")->setWhere("prnum='$prnum' and ponum='$ponum' and polinenum='$polinenum'")->setValue("ponum", null)->setValue("polinenum", null)->change();
		}
	}
	public static function POChangeOrderServ()
	{
		$ponum = $_POST["pochangeorderserv"];
		$polinenum = $_POST["polinenum"];
		$orderqty = $_POST["orderqty"];
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
		
		$ponum = $_POST["pochangeorderqty"];
		$polinenum = $_POST["polinenum"];
		$itemnum = $_POST["itemnum"];
		$orderqty = $_POST["orderqty"];
		$set = new Set();
		if (empty($itemnum)) {
			$itemval = " and (itemnum='' or itemnum is null)";
		}
		else {
			$itemval = " and itemnum='$itemnum'";
		}
		$set = new Set();
		$counts = $set->MboSetRemote("matrectrans")->setWhere("ponum='$ponum' and polinenum='$polinenum' $itemval")->MboRemote("ponum")->getMboSet()->counts();
		if ($counts > 0) {
			$set = new Set();
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
	public static function sumrfqcost() {
	  $rfqnum = Super::getParam("sumrfqcost");
	  $whereClause = "rfqnum = '$rfqnum'";

	  $set = new Set();

	  // Get exchange rate
	  $exchangerate = $set->MboSetRemote("rfq")
						->setWhere($whereClause)  // BUG: This whereclause should be ""
						->MboRemote("exchangerate")
						->getMboSet()
						->getMbo()
						->getString("exchangerate");
	  $exchangerate = floatval($exchangerate); // Convert to float. No need to check null.

	  // Get total tax
	  $set = new Set();
	  $totaltax = $set->MboSetRemote("rfqline")
					->setWhere($whereClause)
					->MboRemote("SUM(taxcost) AS totaltax")
					->getMboSet()
					->getMbo()
					->getString("totaltax");
	  $totaltax = floatval($totaltax); // Convert to float. No need to check null.

	  // Get pre-tax total
	  $set = new Set();
	  $pretaxtotal = $set->MboSetRemote("rfqline")
					   ->setWhere($whereClause)
					   ->MboRemote("SUM(linecost) AS pretaxtotal")
					   ->getMboSet()
					   ->getMbo()
					   ->getString("pretaxtotal");
	  $pretaxtotal = floatval($pretaxtotal); // Convert to float. No need to check null.

	  // Calculate totals
	  $totalcost = $totaltax + $pretaxtotal;
	  $totalbasecost = $totalcost * $exchangerate;

	  Set::throws([
		"totalcost" => $totalcost,
		"totaltax" => $totaltax,
		"totalbasecost" => $totalbasecost,
		"pretaxtotal" => $pretaxtotal
	  ]);
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
		$businessnum = $dataPackage["ownership"]["owner"][0]["rfqnum"];
		
		
		if($childTable == "rfqline"){
			foreach($selectedRows as $row){
				$rfqlineid = $row["rfqlineid"];
				$rfqlinenum = (new Set())->useTransactionManager()->MboSetRemote("rfqline")->where("rfqlineid=?", $rfqlineid)->getMboValue("rfqlinenum");

				//Check RFQQuotations
				$RFQQset = (new Set())->useTransactionManager()->MboSetRemote("rfqquotation")->where("rfqnum=? and rfqlinenum=?", $businessnum, $rfqlinenum)->getMboSet();
				if($RFQQset->isEmpty()){
					$_POST["delChildPackage"] = $dataPackage;
					Super::deleteChildren();		
				}else{
					$RFQQ = $RFQQset->getMbo();
					$vendor = $RFQQ->getString("vendor");
					$vendor_name = (new Set())->useTransactionManager()->MboSetRemote("vendor")->where("vendorcode=?", $vendor)->getMboValue("name");
					Super::sendJsonResponse("error", " Cannot Delete. This RFQ LIne is related to the Quotation for Vendor: $vendor_name ($vendor)");
				}

			}
		}elseif($childTable == "rfqvendor"){

			foreach($selectedRows as $row){
				$rfqvendorid = $row["rfqvendorid"];
				
				$vendor = (new Set())->useTransactionManager()->MboSetRemote("rfqvendor")->where("rfqvendorid=?", $rfqvendorid)->getMboValue("vendor");
				//Check RFQQuotations
				$RFQQset = (new Set())->useTransactionManager()->MboSetRemote("rfqquotation")->where("rfqnum=? and vendor=?", $businessnum, $vendor)->getMboSet();
				if($RFQQset->isEmpty()){
					$_POST["delChildPackage"] = $dataPackage;
					Super::deleteChildren();		
				}else{
					
					Super::sendJsonResponse("error", " Cannot Delete. This RFQ LIne is related to the Quotation");
				}
				
			}

		}elseif($childTable == "rfqquotation"){
			foreach($selectedRows as $row){
				$rfqquotationid = $row["rfqquotationid"];
			}

		}
		Super::sendJsonResponse("error", "You cannot delete the RFQ Records. Contact your Administrator for further actions." );
	}
}

?>