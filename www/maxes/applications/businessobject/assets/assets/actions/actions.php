<?php

require_once ("../../../eat.php");
require_once ("dataloader.php");
$moveAssets =[];


Class ActionSet {
	public static function trainAssetAI() {
		$payload = Super::getParam("trainAssetAI");

		$assetnum  = $payload["assetnum"];
		

		$data = [
			"assetnum"  => $assetnum
		];

		$jsonPayload = json_encode($data);
		
		$endpoint = "/training/start-for-asset";
		$result = Super::sendToML($endpoint, $jsonPayload, true);

		$data = [json_decode($result)];
		Super::sendJsonResponse("success", "AI Training has been started for Asset: $assetnum", $data);
	}
	public static function AIAssetSummary(){
		
		$jspayload = Super::getParam("aiAsSum");
		$assetnum = $jspayload["assetnum"];
		
		$payload = [
			"assetnum" => $assetnum
		];
		$jsonPayload = json_encode($payload);
		$endpoint = "/generate/asset-summary";
		$narrative_result = Super::sendToML($endpoint, $jsonPayload);
		Super::sendJsonResponse("success", $narrative_result);
	}
	public static function createAssets() {
	
		$tm = TransactionManager::getInstance();

		try {
			$payload = Super::getParam("createAssets");
			$assetsToCreate = $payload['assets']; 
			$unitcost = $payload['unitCost'];
			$endDate = $payload['endDate'];
			
			$enterby = Xbo::getLoginUser();
			$enterdate = Xbo::setEnterDate(); // Waktu saat ini
			$orgInfo = Xbo::getOrgcode($enterby);
			$orgcode = $orgInfo->orgcode;
			$sitecode = $orgInfo->sitecode;

			$installDate = !empty($endDate) ? $endDate : $enterdate;

			$assetNumbersToCheck = array_column($assetsToCreate, 'assetNumber');
			
			if (!empty($assetNumbersToCheck)) {
				$existingAssetsSet = (new Set())->useTransactionManager()
												->MboSetRemote("assets")
												->whereIn("assetnum", $assetNumbersToCheck) // Cek semua nomor sekaligus
												->MboRemote("assetnum")
												->getMboSet();

				if (!$existingAssetsSet->isEmpty()) {
					$existingAsset = $existingAssetsSet->fetch();
					// Jika ada satu saja yang sudah ada, batalkan seluruh proses
					throw new Exception("Asset Number '" . $existingAsset['assetnum'] . "' already exists in the database. No assets were created.");
				}
			}
			
			$tm->beginTransaction(); 

			$createdAssetNumbers = []; 
			
			foreach ($assetsToCreate as $asset) {
				$set = new Set();
				$set->useTransactionManager()
					->MboSetRemote("assets")
					// -- Pemetaan Kolom Sesuai Diskusi --
					->setValue("assetnum", $asset['assetNumber'])
					->setValue("description", $asset['assetDescription'])
					->setValue("locnum", $asset['locnum'])
					->setValue("assettype", 'PRODUCTION')      // Aturan Bisnis
					->setValue("status", 'STANDBY')           // Aturan Bisnis
					->setValue("isdown", 'N')                 
					->setValue("healthrate", 100)             // Aset baru 100% sehat
					->setValue("purchaseprice", $unitcost)    // Biaya produksi = harga beli
					->setValue("manufacturer", $orgcode)      // Dibuat oleh organisasi sendiri
					->setValue("installdate", $installDate)
					->setValue("statusdate", $enterdate)
					->setValue("enterdate", $enterdate)
					->setValue("changedate", $enterdate)
					->setValue("lasthealthupdatedate", $enterdate)
					->setValue("enterby", $enterby)
					->setValue("changeby", $enterby)
					->setValue("orgcode", $orgcode)
					->setValue("sitecode", $sitecode)
					->add();
				$createdAssetNumbers[] = $asset['assetNumber'];
			}

			$tm->commit(); 

			$count = count($createdAssetNumbers);
			$message = "$count new asset(s) have been created successfully: " . implode(', ', $createdAssetNumbers);
			Super::sendJsonResponse("success", $message);

		} catch (Exception $e) {
			$tm->rollback();
			Super::sendJsonResponse("error", "Failed to create assets. Error: " . $e->getMessage());
		}
	}
	public static function generateSimpleBatchNumber($payload) {
    
		$itemnum = $payload['itemNumber']; 
		$startDateObj = date_create($payload['startDate']);
		
		$productionDate = date_format($startDateObj, 'ymd');
		
		$dateForQuery = date_format($startDateObj, 'Y-m-d');

		$set = (new Set())->useTransactionManager()
						  ->MboSetRemote("productionbatches") 
						  ->MboRemote("COUNT(*) as total_count")
						  ->where("itemnum = ? AND DATE(startdate) = ?", $itemnum, $dateForQuery); 
						  
		$result = $set->getMboSet()->fetch();
		
		$countToday = $result ? (int)$result['total_count'] : 0;
		$sequenceNumber = $countToday + 1;

		$formattedSequence = str_pad($sequenceNumber, 3, '0', STR_PAD_LEFT);
		$batchnum = "{$itemnum}-{$productionDate}-{$formattedSequence}";
		
		return $batchnum;
	}

	public static function createMatProdTrans($wonum, $batchnum, $prodstartdate, $prodenddate, $productnum){
		
		$matusetransSet = (new Set())->useTransactionManager()->MboSetRemote("matusetrans")->where("wonum=?", $wonum)->getMboSet();
		if($matusetransSet->isEmpty()){
			return null;
		}
		$logdate = Xbo::setEnterDate();
		$materials = [];
		while($row = $matusetransSet->fetch()){
			$materials [] = [
				"wonum" => $row["wonum"],
				"materialnum" => $row["itemnum"],
				"description" => $row["description"], 
				"quantity" => $row["quantity"],
				"unitcost" => $row["unitcost"],
				"linecost" => $row["linecost"],
				"warehouse" => $row["warehouse"],
				"racknum" => $row["racknum"],
				"batchnum" => $batchnum,
				"logdate" => $logdate,
				"prodstartdate" => $prodstartdate, 
				"prodenddate" => $prodenddate,
				"itemnum" => $productnum
			];
		}
		
		if(!empty($materials)){
			(new Set())->MboSetRemote("matprodtrans")->addStream($materials);
		}
	}
	public static function createServProdTrans($wonum, $batchnum, $prodstartdate, $prodenddate, $productnum){
		$services = [];
		$servprodtransSet = (new Set())->useTransactionManager()->MboSetRemote("servrectrans")->where("wonum=?", $wonum)->getMboSet();
		if($servprodtransSet->isEmpty()){
			return null;
		}
		$logdate = Xbo::setEnterDate();
		while($row = $servprodtransSet->fetch()){
			$services [] = [
				"wonum" => $row["wonum"],
				"serviceitemnum" => $row["serviceitemnum"],
				"description" => $row["description"], 
				"ponum" => $row["ponum"],
				"polinenum" => $row["polinenum"],
				"quantity" => $row["quantity"],
				"orderunit" => $row["orderunit"],
				"unitcost" => $row["unitcost"],
				"linecost" => $row["linecost"],
				"batchnum" => $batchnum,
				"logdate" => $logdate,
				"prodstartdate" => $prodstartdate, 
				"prodenddate" => $prodenddate,
				"itemnum" => $productnum
			];
		}

		if(!empty($services)){
			(new Set())->MboSetRemote("servprodtrans")->addStream($services);
		}

		return null;
	}
	public static function createToolProdTrans($wonum, $batchnum, $prodstartdate, $prodenddate, $productnum){
		$tools = [];
		$toolprodtransSet = (new Set())->useTransactionManager()->MboSetRemote("tooltrans")->where("wonum=?", $wonum)->getMboSet();
		if($toolprodtransSet->isEmpty()){
			return null;
		}
		$logdate = Xbo::setEnterDate();
		while($row = $toolprodtransSet->fetch()){
			$tools [] = [
				"wonum" => $row["wonum"],
				"toolnum" => $row["toolnum"],
				"description" => $row["description"], 
				"quantity" => $row["quantity"],
				"orderunit" => $row["orderunit"],
				"unitcost" => $row["unitcost"],
				"linecost" => $row["linecost"],
				"batchnum" => $batchnum,
				"logdate" => $logdate,
				"prodstartdate" => $prodstartdate, 
				"prodenddate" => $prodenddate,
				"itemnum" => $productnum
			];
		}
		if(!empty($tools)){
			(new Set())->MboSetRemote("toolprodtrans")->addStream($tools);
		}
		return null;
	}
	public static function createProductionLogs($wos, $batchnum, $prodstartdate, $prodenddate, $productnum){
		/* $wos = [{"wonum":"WO-295-2025","assetnum":"CVY-3000-BLT"},
		{"wonum":"CM-CNVY3000-20240105-001","assetnum":"CNVY-COAL-3000"},....,{"wonum":"CM-CNVT3000-20240915-001","assetnum":"CNVY-COAL-3000"}]*/

		if (is_array($wos) && !empty($wos)) {
			foreach ($wos as $wo) {
				$wonum = $wo['wonum'];
				self::createMatProdTrans($wonum, $batchnum, $prodstartdate, $prodenddate, $productnum);
				self::createServProdTrans($wonum, $batchnum, $prodstartdate, $prodenddate, $productnum);
				self::createToolProdTrans($wonum, $batchnum, $prodstartdate, $prodenddate, $productnum);
			}
		} else {
			return null;
		}
		
	}

	
	public static function createItems() {
	
		$tm = TransactionManager::getInstance();

		try {
		
			$payload = Super::getParam("createItems");
			$assetnum = $payload['assetName'];
			$totalCost = $payload['totalCost'];
			$startdate = $payload['startDate'];
			$enddate = $payload['endDate'];
			$itemnum = $payload['itemNumber'];
			$quantity = $payload['quantity'];
			$unitcost = $payload['unitCost'];
			$warehouse = $payload['warehouse'];
			
			$wos = Super::getParam("wos");
			

			if(empty($startdate) || empty($enddate)){
				Super::sendJsonResponse("error", "Start Date and End Date is required.");
			}
			
			$batchnum = self::generateSimpleBatchNumber($payload);
			$enterby = Xbo::getLoginUser();
			$enterdate = Xbo::setEnterDate(); 
			$orgInfo = Xbo::getOrgcode($enterby);
			$orgcode = $orgInfo->orgcode;
			$sitecode = $orgInfo->sitecode;

			$transactionDate = !empty($enddate) ? $enddate : $enterdate;
			
			$transactionType = 'PRODUCTION';
			
			$tm->beginTransaction(); // Mulai transaksi
			
			$itemConditionSet = (new Set())->useTransactionManager()->MboSetRemote("itemcondition")->where("itemnum=? and conditionrate=100", $itemnum)->MboRemote("conditiontype")->getMboSet();
			if($itemConditionSet->isEmpty()){
				$conditiontype = null;
			}else{
				$conditiontype = $itemConditionSet->getMbo()->getString("conditiontype");
			}
			
			//Check Inventory recordstatus
			$inventorySet = (new Set())->useTransactionManager()->MboSetRemote("inventory")->setWhere("itemnum='$itemnum' and warehouse='$warehouse'")->MboRemote("itemnum")->getMboSet();
			if ($inventorySet->isEmpty()) {
			
				$issuetype = "FIFO";
				$insertInventorySet = (new Set())->useTransactionManager()->MboSetRemote("inventory")->setValue("itemnum", $itemnum)->setValue("warehouse", $warehouse)->setValue("racknum", $defrack)
				->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)
				->setValue("eoqqty", 1)->setValue("reorder", "Y")->setValue("issuetype", $issuetype);
				$insertInventorySet->add();
			}
			

			//Create Production Log
			$issueunit = (new Set())->useTransactionManager()->MboSetRemote("item")->where("itemnum=?", $itemnum)->getMboValue("issueunit");
			$linecost = $quantity*$unitcost;
			$unitprice= 0;
			$lineprice = 0;
			$wonum = null;
			$healthrate = (new Set())->useTransactionManager()->MboSetRemote("assets")->where("assetnum =?", $assetnum)->getMboValue("healthrate");
			$teamnum = (new Set())->useTransactionManager()->MboSetRemote("assets")->where("assetnum =?", $assetnum)->getMboValue("teamnum");
			$unitprice = floatval((new Set())->useTransactionManager()->MboSetRemote("invprice")->where("itemnum=? AND defaultprice='Y'", $itemnum)->getMboValue("unitprice"));
			$lineprice = $unitprice*$quantity;

			$productionLog =[
				"itemnum" => $itemnum, 
				"issueunit" => $issueunit,
				"productiondate" => $transactionDate,
				"quantity" => $quantity,
				"unitcost" => $unitcost,
				"linecost" => $linecost,
				"unitprice" => $unitprice,
				"lineprice" => $lineprice,
				"assetnum" => $assetnum,
				"enterby" => $enterby,
				"enterdate" => $enterdate,
				"wonum" => $wonum, 
				"healthrate" => $healthrate,
				"teamnum" => $teamnum,
				"orgcode" => $orgcode,
				"sitecode" => $sitecode,
				'startdate' => $startdate,
				'enddate' => $enddate,
				'batchnum' => $batchnum,
				'outputtype' => "ITEM"
			];

			$sqlToInsert [] = $productionLog;

			 $overlapCheckSet = (new Set())->useTransactionManager()
			->MboSetRemote("productionlog")
			->where("assetnum = ?", $assetnum)
			->where("startdate <= ? AND enddate >= ?", $enddate, $startdate)
			->limit(1)
			->getMboSet();


			if (!$overlapCheckSet->isEmpty()) {
				$existingLog = $overlapCheckSet->fetch();
				throw new Exception(
					"Validation failed: The provided date range ({$startdate} to {$enddate}) " .
					"overlaps with an existing production log (ID: {$existingLog['productionlogid']} from {$existingLog['startdate']} to {$existingLog['enddate']}) " .
					"for the same asset."
				);
			}

			
			(new Set())->useTransactionManager()->MboSetRemote("productionlog")->addStream($sqlToInsert);
			
			
			$set = new Set();
			$set->useTransactionManager()
				->MboSetRemote("invfifolifocost")
				// -- Pemetaan Kolom --
				->setValue("itemnum", $itemnum)
				->setValue("batchnum", $batchnum)
				->setValue("unitcost", $unitcost)
				->setValue("quantity", $quantity)
				->setValue("physqty", $quantity) 
				->setValue("costdate", $transactionDate)
				->setValue("transdate", $transactionDate)
				->setValue("transtype", $transactionType)
				->setValue("conditiontype", $conditionType)
				->setValue("warehouse", $warehouse)
				->setValue("sitecode", $sitecode)
				->setValue("orgcode", $orgcode)
				->setValue("enterby", $enterby)
				->setValue("enterdate", $enterdate)
				->add();

			$productnum = $itemnum;
			self::createProductionLogs($wos, $batchnum, $startdate, $enddate, $productnum);

			$tm->commit();
			Super::sendJsonResponse("success", "Items have been created with batch number: $batchnum and moved to Warehouse: $warehouse");

		} catch (Exception $e) {
			
			$tm->rollback(); 
			Super::sendJsonResponse("error", "Failed to create items. Error: " . $e->getMessage());
		}
	}
	
	public static function getFleetOverview(){
		$set = new Set();

		$allLocations = $set->MboSetRemote("assettracking")
			->setRelationship("assets", "assets.assetnum = assettracking.assetnum")
			->MboRemote("assettracking.*, assets.description, assets.assettype, assets.status")
			->getMboSet()
			->fetchData();

		Set::throws($allLocations);
	}
	public static function getReverseGeocode(){
		$lat = Super::getParam('lat');
		$lon = Super::getParam('lon');

		if (empty($lat) || empty($lon)) {
			Set::throws(["status" => "error", "message" => "Latitude dan Longitude wajib diisi."]);
			exit();
		}
		
		// URL API Nominatim
		$url = "https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat={$lat}&lon={$lon}";

		// PENTING: Nominatim mewajibkan User-Agent yang jelas.
		$options = [
			'http' => [
				'header' => "User-Agent: MyApp/1.0 (maxesqriven@gmail.com)\r\n"
			]
		];
		$context = stream_context_create($options);

		// Panggil API
		$response = @file_get_contents($url, false, $context);

		if ($response === FALSE) {
			Set::throws(["status" => "error", "message" => "Failed to call Geocoding"]);
			exit();
		}

		$data = json_decode($response, true);

		$locationName = $data['display_name'] ?? "Place Not Found";

		// Kembalikan hasilnya
		Set::throws(["status" => "success", "locationName" => $locationName]);
	}


	public static function getAssetTrackHistory(){
		$assetnum = Super::getParam('getAssetTrackHistory');
		$startdate = Super::getParam('startdate'); // Format 'YYYY-MM-DD HH:MM:SS'
		$enddate = Super::getParam('enddate');     // Format 'YYYY-MM-DD HH:MM:SS'

		if (empty($assetnum) || empty($startdate) || empty($enddate)) {
			Set::throws(["status" => "error", "message" => "Parameter assetnum, startdate, dan enddate wajib diisi."]);
			exit();
		}

		$set = new Set();

		$history = $set->MboSetRemote("assettrackinghistory")
			->where("assetnum = ? AND timestamp BETWEEN ? AND ?", $assetnum, $startdate, $enddate)
			->order("timestamp ASC") // PENTING: diurutkan berdasarkan waktu untuk animasi
			->getMboSet()
			->fetchData();

		Set::throws($history);
	}

	public static function generateDescription(){
		$assetnum = Super::getParam("gendescription");
	
		$assetclassSet = (new Set())->useTransactionManager()->MboSetRemote("assetclassification")->where("assetnum = ?", $assetnum)
		->MboRemote("attributename, description, value, unit")->getMboSet();
		

		$specs = [];

		while($assetClass = $assetclassSet->fetch()){
			$specs [] = [
				"attributename" =>  $assetClass["attributename"],
				"description" => $assetClass["description"],
				"value" => $assetClass["value"],
				"unit" => $assetClass["unit"]
			];
		}
		
		$specTexts = array_map(function($spec){
			return $spec['attributename'] . ': ' . $spec['value'] . ' ' . $spec['unit'];
		}, $specs);

		$finalText = implode("; ", $specTexts);

		(new Set())->useTransactionManager()->MboSetRemote("assets")->where("assetnum=?", $assetnum)->setValue("spectext", $finalText)->change();
		Super::sendJsonResponse("success", $finalText);
	}	
	public static function addAttributes(){
		$payload = Super::getParam("addAttributes");
		$assetnum = $payload["assetnum"];
		$data = $payload["data"];

		$attrList = [];

		$attributes = [];
		foreach($data as $row){
			$attributeid = $row["attributeid"];
			$attrSet = (new Set())->useTransactionManager()->MboSetRemote("attribute")->where("attributeid = ?", $attributeid)->MboRemote("attributename, description, type")->getMboSet()->fetch();
			$attributes [] = [
				"attributename" => $attrSet["attributename"],
				"description" => $attrSet["description"],
				"type" => $attrSet["type"]
			];
		}

		$enterby = Xbo::getLoginUser();
		$enterdate = Xbo::setEnterDate();

		foreach($attributes as &$row){
			$row["assetnum"] = $assetnum;
			$row["enterby"] = $enterby;
			$row["enterdate"] = $enterdate;
		}

		
		(new Set())->MboSetRemote("assetclassification")->addStream($attributes);
		Super::sendJsonResponse("success", "Attributes has been added to Asset : $assetnum");

	}
	public static function getCurrentAssetLocations(){
		$assetnum = Super::getParam("getCurrentAssetLocations");
		$set = new Set();
		// Join tabel tracking dengan master asset untuk mendapatkan info deskripsi, dll.
		$assetLocations = $set->MboSetRemote("assettracking")
			->where("assettracking.assetnum = ?", $assetnum)
			->setRelationship("assets", "assets.assetnum = assettracking.assetnum")
			->MboRemote("assettracking.*, assets.description, assets.assettype, assets.model")
			->getMboSet()
			->fetchData(); // Ambil semua data sebagai array

		Set::throws($assetLocations);
	}
	public static function setGPSPosition(){
		$assetnum = Super::getParam('setGPSPosition'); 
		$latitude = Super::getParam('latitude');
		$longitude = Super::getParam('longitude');
		$speed = Super::getParam('speed') ?? 0;
		$bearing = Super::getParam('bearing') ?? 0;
		$timestamp = Super::getParam('timestamp') ?? date("Y-m-d H:i:s");
		 
		if (empty($assetnum) || empty($latitude) || empty($longitude)) {
			Set::throws(["status" => "error", "message" => "Parameter tidak lengkap: assetnum, latitude, dan longitude wajib diisi."]);
			exit();
		}

		$tm = TransactionManager::getInstance();
		$set = new Set();
		
		
		try {
			// Mulai transaksi database untuk memastikan integritas data
			$tm->beginTransaction();
			$set->useTransactionManager();

			// 1. Cek apakah aset sudah ada di tabel tracking saat ini
			$currentTracking = $set->MboSetRemote("assettracking")
								   ->where("assetnum = ?", $assetnum)
								   ->getMboSet();

			if (!$currentTracking->isEmpty()) {
				$oldData = $currentTracking->fetch();
				$set->MboSetRemote("assettrackinghistory")
					->setValue("assetnum", $oldData['assetnum'])
					->setValue("latitude", $oldData['latitude'])
					->setValue("longitude", $oldData['longitude'])
					->setValue("speed", $oldData['speed'])
					->setValue("bearing", $oldData['bearing'])
					->setValue("timestamp", $oldData['timestamp'])
					->add();

				// 2. Update data saat ini dengan lokasi baru
				$set->MboSetRemote("assettracking")
					->where("assetnum = ?", $assetnum)
					->setValue("latitude", $latitude)
					->setValue("longitude", $longitude)
					->setValue("speed", $speed)
					->setValue("bearing", $bearing)
					->setValue("timestamp", $timestamp)
					->change();
					
			} else {
				// Jika belum ada (data pertama kali masuk), langsung insert ke tabel tracking
				$set->MboSetRemote("assettracking")
					->setValue("assetnum", $assetnum)
					->setValue("latitude", $latitude)
					->setValue("longitude", $longitude)
					->setValue("speed", $speed)
					->setValue("bearing", $bearing)
					->setValue("timestamp", $timestamp)
					->add();
			}

			// Jika semua berhasil, commit transaksi
			$tm->commit();
			Set::throws(["status" => "success", "message" => "Lokasi aset $assetnum berhasil diperbarui."]);

		} catch (Exception $e) {
			// Jika ada error, batalkan semua perubahan
			$tm->rollback();
			throw $e; // Dilempar ke globalExceptionHandler
		}

	}
	
public static function getGaugeMeters(){
    $assetnum = Super::getParam("getGaugeMeters");
    if (empty($assetnum)) {
        Super::sendJsonResponse("error", "Asset number is required.", []);
        return;
    }
    
    $assetMeterSet = (new Set())
        ->useTransactionManager()
        ->MboSetRemote("assetmeter am") 
        ->MboRemote("am.meternum, am.lastreading, amc.normal_low, amc.normal_high, amc.warning_low, amc.warning_high, amc.limit_low, amc.limit_high, amc.optimal_zone")
        ->setRelationship("assetmeterconfig amc", "am.assetnum = amc.assetnum AND am.meternum = amc.meternum")
        ->where("am.assetnum = ?", $assetnum)
        ->getMboSet();
    
    $meters = [];
    
    while($meter = $assetMeterSet->fetch()){
        $lastreading = (float)($meter["lastreading"] ?? 0);
        
        $limitHigh = (float)($meter["limit_high"] ?? 0);
        $limitLow  = (float)($meter["limit_low"] ?? 0);
        $normalLow = (float)($meter["normal_low"] ?? 0) != 0 ? (float)$meter["normal_low"] : null;
        $normalHigh = (float)($meter["normal_high"] ?? 0) != 0 ? (float)$meter["normal_high"] : null;
        $warningLow = (float)($meter["warning_low"] ?? 0) != 0 ? (float)$meter["warning_low"] : null;
        $warningHigh = (float)($meter["warning_high"] ?? 0) != 0 ? (float)$meter["warning_high"] : null;
        $optimalZone = $meter["optimal_zone"] ?? 'lower_is_safe';
        
        // --- DETEKSI APAKAH INI METER DENGAN NILAI NEGATIF ---
        $isNegativeRange = ($limitLow < 0 || $normalLow < 0 || $lastreading < 0);
        
        // --- Tentukan Skala Gauge (min/max) ---
       if ($isNegativeRange) {
			// Untuk meter negatif (seperti Draft Pressure)
			$minValue = $limitLow != 0 ? $limitLow : min(-100, $lastreading * 1.5);
			$maxValue = $limitHigh != 0 ? $limitHigh : 0;
		} else {
			// Untuk meter positif
			// Min value: gunakan limit_low jika ada, default 0
			$minValue = $limitLow;
			
			// Max value: gunakan limit_high jika ada, fallback ke perhitungan
			if ($limitHigh > 0) {
				$maxValue = $limitHigh;
			} else if ($normalHigh !== null && $normalHigh > 0) {
				$maxValue = $normalHigh * 1.5; // Tambah 50% dari normal high
			} else {
				$maxValue = max(100, $lastreading * 2);
			}
		}
        
        // --- Inisialisasi zona warna ---
        $greenFrom = $yellowFrom = $redFrom = null;
        $greenTo = $yellowTo = $redTo = null;
        
        // --- Cek apakah ada konfigurasi zona ---
        $hasConfig = $normalLow !== null || $normalHigh !== null;
        
        if ($hasConfig) {
            // ========== LOGIKA BERDASARKAN OPTIMAL ZONE ==========
            
            if ($optimalZone === 'higher_is_safe') {
                // ===== HIGHER IS SAFE (Oil Pressure, Battery, Fluid Qty) =====
                // Zona Merah: Nilai rendah berbahaya
                // Zona Hijau: Nilai tinggi aman
                
                if ($normalLow !== null && $normalHigh !== null) {
                    // Ada range normal lengkap
                    $greenFrom = $normalLow;
                    $greenTo = $normalHigh;
                    
                    // Yellow zone: antara warning dan normal (di bawah)
                    $yellowFrom = $warningLow ?? $limitLow;
                    $yellowTo = $normalLow;
                    
                    // Red zone: di bawah warning
                    $redFrom = $limitLow;
                    $redTo = $warningLow ?? $normalLow;
                    
                } else if ($normalLow !== null && $normalHigh === null) {
                    // Hanya ada batas bawah
                    $greenFrom = $normalLow;
                    $greenTo = $maxValue;
                    
                    $yellowFrom = $warningLow ?? $limitLow;
                    $yellowTo = $normalLow;
                    
                    $redFrom = $limitLow;
                    $redTo = $warningLow ?? $normalLow;
                    
                } else if ($normalLow === null && $normalHigh !== null) {
                    // Hanya ada batas atas (tidak umum untuk higher_is_safe)
                    $greenFrom = $minValue;
                    $greenTo = $normalHigh;
                    
                    $yellowFrom = $normalHigh;
                    $yellowTo = $warningHigh ?? $limitHigh;
                    
                    $redFrom = $warningHigh ?? $limitHigh;
                    $redTo = $maxValue;
                }
                
            } else if ($isNegativeRange) {
                // ===== NILAI NEGATIF (VACUUM/DRAFT) =====
                // Zona hijau ada di tengah (normal range)
                
                if ($normalLow !== null && $normalHigh !== null) {
                    $greenFrom = $normalLow;
                    $greenTo = $normalHigh;
                    
                    // Warning High (mendekati nol = vakum lemah)
                    $yellowHighFrom = $normalHigh;
                    $yellowHighTo = $warningHigh ?? $limitHigh;
                    
                    // Warning Low (terlalu negatif = vakum terlalu kuat)
                    $yellowLowFrom = $warningLow ?? $limitLow;
                    $yellowLowTo = $normalLow;
                    
                    // Red High (mendekati/melewati nol)
                    $redHighFrom = $warningHigh ?? $normalHigh;
                    $redHighTo = $maxValue;
                    
                    // Red Low (terlalu vakum)
                    $redLowFrom = $minValue;
                    $redLowTo = $warningLow ?? $normalLow;
                    
                    // Gunakan zona atas untuk display (lebih kritikal)
                    $yellowFrom = $yellowHighFrom;
                    $yellowTo = $yellowHighTo;
                    $redFrom = $redHighFrom;
                    $redTo = $redHighTo;
                    
                } else if ($normalLow !== null && $normalHigh === null) {
                    $greenFrom = $normalLow;
                    $greenTo = $maxValue;
                    
                    $yellowFrom = $warningLow ?? $limitLow;
                    $yellowTo = $normalLow;
                    
                    $redFrom = $minValue;
                    $redTo = $warningLow ?? $normalLow;
                    
                } else if ($normalLow === null && $normalHigh !== null) {
                    $greenFrom = $minValue;
                    $greenTo = $normalHigh;
                    
                    $yellowFrom = $normalHigh;
                    $yellowTo = $warningHigh ?? $limitHigh;
                    
                    $redFrom = $warningHigh ?? $limitHigh;
                    $redTo = $maxValue;
                }
                
            } else {
                // ===== LOWER IS SAFE (Temperature, Vibration, EGT) =====
                // Nilai rendah = aman, nilai tinggi = bahaya
                
                if ($normalLow !== null && $normalHigh !== null) {
                    $greenFrom = $normalLow;
                    $greenTo = $normalHigh;
                    
                    $yellowFrom = $normalHigh;
                    $yellowTo = $warningHigh ?? $limitHigh;
                    
                    $redFrom = $warningHigh ?? $limitHigh;
                    $redTo = $maxValue;
                    
                } else if ($normalLow !== null && $normalHigh === null) {
                    $greenFrom = $normalLow;
                    $greenTo = $maxValue;
                    
                    $yellowFrom = $warningLow ?? $limitLow;
                    $yellowTo = $normalLow;
                    
                    $redFrom = $limitLow;
                    $redTo = $warningLow ?? $normalLow;
                    
                } else if ($normalLow === null && $normalHigh !== null) {
                    $greenFrom = $minValue;
                    $greenTo = $normalHigh;
                    
                    $yellowFrom = $normalHigh;
                    $yellowTo = $warningHigh ?? $limitHigh;
                    
                    $redFrom = $warningHigh ?? $limitHigh;
                    $redTo = $maxValue;
                }
            }
        }
        
        // --- Format output JSON ---
        $meterData = [
            "meternum" => $meter["meternum"],
            "lastreading" => $lastreading,
            "min_value" => round($minValue, 2),
            "max_value" => round($maxValue, 2),
            
            "greenFrom" => $greenFrom !== null ? round($greenFrom, 2) : null,
            "greenTo" => $greenTo !== null ? round($greenTo, 2) : null,
            "yellowFrom" => $yellowFrom !== null ? round($yellowFrom, 2) : null,
            "yellowTo" => $yellowTo !== null ? round($yellowTo, 2) : null,
            "redFrom" => $redFrom !== null ? round($redFrom, 2) : null,
            "redTo" => $redTo !== null ? round($redTo, 2) : null,
            
            // Metadata tambahan untuk debugging
            "optimal_zone" => $optimalZone,
            "is_negative_range" => $isNegativeRange
        ];
        
        $meters[] = $meterData;
    }
    
    Super::sendJsonResponse("success", "Gauge Meter data retrieved", $meters);
}

public static function getGaugeMeters_v1(){
    $assetnum = Super::getParam("getGaugeMeters");
    if (empty($assetnum)) {
        Super::sendJsonResponse("error", "Asset number is required.", []);
        return;
    }

    $assetMeterSet = (new Set())
        ->useTransactionManager()
        ->MboSetRemote("assetmeter am") 
        ->MboRemote("am.meternum, am.lastreading, amc.normal_low, amc.normal_high, amc.warning_low, amc.warning_high, amc.limit_low, amc.limit_high")
        ->setRelationship("assetmeterconfig amc", "am.assetnum = amc.assetnum AND am.meternum = amc.meternum")
        ->where("am.assetnum = ?", $assetnum)
        ->getMboSet();

    $meters = [];

	while($meter = $assetMeterSet->fetch()){
		
		$lastreading = (float)($meter["lastreading"] ?? 0);

		// --- Langkah 1: Tentukan Skala Utama (min/max) ---
		$limitHigh = (float)($meter["limit_high"] ?? 0);
		$limitLow  = (float)($meter["limit_low"] ?? 0);
		
		// Logika skala: Prioritaskan limit_high, jika tidak ada, buat skala dinamis di sekitar lastreading
		$maxValue = $limitHigh > 0 ? $limitHigh : max(100, $lastreading * 2); // Buffer 100%
		$minValue = $limitLow;

		// --- Langkah 2: Ekstrak semua batas dari DB, ganti 0 menjadi null ---
		$normalLow = (float)($meter["normal_low"] ?? 0) > 0 ? (float)$meter["normal_low"] : null;
		$normalHigh = (float)($meter["normal_high"] ?? 0) > 0 ? (float)$meter["normal_high"] : null;
		$warningLow = (float)($meter["warning_low"] ?? 0) > 0 ? (float)$meter["warning_low"] : null;
		$warningHigh = (float)($meter["warning_high"] ?? 0) > 0 ? (float)$meter["warning_high"] : null;
		
		// Deklarasi variabel zona, defaultnya null (tanpa warna)
		$greenFrom = $yellowFrom = $redFrom = null;
		$greenTo = $yellowTo = $redTo = null;

		// --- Langkah 3: LOGIKA UTAMA UNTUK ZONA WARNA (HANYA JIKA ADA KONFIGURASI!!!) ---
		$hasConfig = $normalLow !== null || $normalHigh !== null;
		
		if ($hasConfig) {
			// Skenario A: "Range Normal" (Nilai ideal di tengah)
			if ($normalLow !== null && $normalHigh !== null) {
				$greenFrom = $normalLow;
				$greenTo = $normalHigh;
				// Prioritaskan zona atas untuk visual
				$yellowFrom = $normalHigh;
				$yellowTo = $warningHigh ?? $limitHigh;
				$redFrom = $warningHigh ?? $limitHigh;
				$redTo = $maxValue;
			} 
			// Skenario B: "Batas Bawah" (Nilai tinggi lebih baik)
			else if ($normalLow !== null && $normalHigh === null) {
				$greenFrom = $normalLow;
				$greenTo = $maxValue;
				$yellowFrom = $warningLow ?? $limitLow;
				$yellowTo = $normalLow;
				$redFrom = $limitLow;
				$redTo = $warningLow ?? $normalLow;
			}
			// Skenario C: "Batas Atas" (Nilai rendah lebih baik)
			else if ($normalLow === null && $normalHigh !== null) {
				$greenFrom = $minValue;
				$greenTo = $normalHigh;
				$yellowFrom = $normalHigh;
				$yellowTo = $warningHigh ?? $limitHigh;
				$redFrom = $warningHigh ?? $limitHigh;
				$redTo = $maxValue;
			}
		}
		// Jika !$hasConfig, semua variabel zona akan tetap null, menghasilkan gauge polos.

		// --- Langkah 4: Format output JSON ---
		$meterData = [
			"meternum" => $meter["meternum"],
			"lastreading" => $lastreading,
			"min_value" => round($minValue),
			"max_value" => round($maxValue),
			
			"greenFrom" => $greenFrom,
			"greenTo" => $greenTo,
			"yellowFrom" => $yellowFrom,
			"yellowTo" => $yellowTo,
			"redFrom" => $redFrom,
			"redTo" => $redTo
		];
		
		$meters[] = $meterData;
	}
		
		
	Super::sendJsonResponse("success", "Gauge Meter data retrieved", $meters);
}
	
	
	
	public static function getAssetHealth(){
		$assetnum = Super::getParam("getAssetHealth");
		if(!$assetnum || empty($assetnum)){
			self::sendJsonResponse("Error", "Asset Number(assetnum) is required.");
		}
		
	
		$healthrate = (new Set())->useTransactionManager()->MboSetRemote("assets")->setWhere("assetnum='$assetnum'")->getMboValue("healthrate");
		
		$ahS = (new Set())->useTransactionManager()->MboSetRemote("assethealthscore")->where("assetnum=?" , $assetnum)->getMboSet()->getMbo();
		$ai_confident = $ahS->getString("lastriskscore");
		$risklevel = $ahS->getString("risklevel");
		$predicted_failure_date = $ahS->getString("predictedfailuredate");
		$predicted_ttf =  $ahS->getString("predictedtimetofail");
		$lastupdated =  $ahS->getString("lastupdated");


		$data = new stdClass();
		$data->assetnum = $assetnum; 
		$data->healthrate = $healthrate;
		$data->ai_confident = $ai_confident;
		$data->risklevel = $risklevel;
		$data->predicted_failure_date = $predicted_failure_date;
		$data->lastupdated = $lastupdated;
		$data->predicted_ttf = $predicted_ttf;
		Set::throws($data);
	}
	public static function saveAssetHealth(){
		$payload = Super::getParam("save_asset_health");
		$healthrate = $payload["healthRate"];
		$assessmentDate = $payload["assessmentDate"];
		$basis = $payload["basis"];
		$notes = $payload["notes"];
		$assessedBy = $payload["assessedBy"];
		$assetnum = $payload["assetnum"];
		$enterby = Xbo::getLoginUser();
		$enterdate = Xbo::setEnterDate();
		
		
		if(empty($assetnum)) {
			self::sendJsonResponse("error", "Requires ASSET to be assigned with");
		}
		if(empty($healthrate)){
			self::sendJsonResponse("error", "Health Rate cannot be empty");
		}
		
		$whereClause = "assetnum='$assetnum' and wonum is null";
		
		
		// Void yang lama (jika ada) dan catet ID-nya
		

		$lastActiveId = null; 
		$lastActiveSet = (new Set())->MboSetRemote("assethealthrate")->setWhere("wonum is null AND assetnum='$assetnum' AND recordstatus='ACTIVE'")->getMboSet();
		
		if(!$lastActiveSet->isEmpty()){
			$lastActiveMbo = $lastActiveSet->getMbo(0);
			$lastActiveId = $lastActiveMbo->getString("assethealthrateid");

			(new Set())->MboSetRemote("assethealthrate")->setWhere("assethealthrateid='$lastActiveId'")
			->setValue("recordstatus", "VOID")
			->setValue("changeby", $enterby)
			->setValue("changedate", $enterdate)
			->change();
			
		}

		
		// SELALU INSERT RECORD BARU
		$addSet = new Set();
		$addSet->MboSetRemote("assethealthrate")
			->setValue("assetnum", $assetnum)
			->setValue("healthrate", $healthrate)
			->setValue("assessmentdate", $assessmentDate)
			->setValue("basis", $basis)
			->setValue("notes", $notes)
			->setValue("assessedby", $assessedBy)
			->setValue("enterby", $enterby)
			->setValue("recordstatus", "ACTIVE")
			->setValue("replacesid", $lastActiveId) 
			->add();
		
		
		$set = new Set();
		$set->MboSetRemote("assets")->setWhere("assetnum='$assetnum'")->setValue("healthrate", $healthrate)->change();
        // Integrasi dengan CBM 
        
        ActionSet::checkAndTriggerHealthRules($assetnum, $healthrate);
        
		
		$message = ($lastActiveId === null) 
			? "Asset Health Rate history has been successfully recorded." 
			: "Asset Health Rate record has been successfully corrected.";
			
		self::sendJsonResponse("success", $message);
	}
	
	 
   public static function checkAndTriggerHealthRules($assetnum, $new_health_rate) {
    
		
		$rulesSet = (new Set())->useTransactionManager()
							   ->MboSetRemote("pmhealthrule r")
							   ->where("r.assetnum = ? OR p.assetnum = ?", $assetnum, $assetnum)
							   ->setRelationship("pm p", "r.pmnum = p.pmnum")
							   ->getMboSet();

		if ($rulesSet->isEmpty()) {
			return;
		}

		
		while ($rule = $rulesSet->fetch()) {
			$rule_id = $rule['pmhealthruleid'];
			$pmnum = $rule['pmnum'];
			$reservedfor = (new Set())->useTransactionManager()->MboSetRemote("pm")->where("pmnum=?", $pmnum)->MboRemote("owner")->getString("owner");
			$operator = $rule['operator'];
			$trigger_point = (float)$rule['triggerpoint'];
			$auto_reserve = ($rule['autoreserve'] === 'Y');

			$status_data = self::_getRuleStatus($rule_id, 'HEALTH');

			//$is_already_active = $status_data ? ($status_data['isactive'] === 'Y') : false;
			
			if(empty($status_data)) $status_data = "N";
			$is_already_active = $status_data['isactive'] ;
			
			$is_condition_met = self::evaluateCondition($new_health_rate, $operator, $trigger_point);
			
			$should_reset = false;
			if ($is_already_active  === "Y") {
				// Jika aturan sedang aktif, dan kondisi pemicunya TIDAK LAGI terpenuhi, maka kita harus reset.
				if (!$is_condition_met) {
					$should_reset = true;
				}
			}
			if ($is_condition_met && $is_already_active == "N") {
				// KASUS 1: Kondisi terpenuhi dan aturan belum aktif.
				$new_wonum = self::createCbmWorkOrder($rule_id, $assetnum, 'HEALTH', $auto_reserve, $reservedfor, $pmnum);
				self::updateRuleStatus($rule_id, 'HEALTH', $assetnum, $pmnum, true, $new_wonum, $new_health_rate);
			} elseif ($should_reset) {
				// KASUS 2: RESET! Aturan sedang aktif dan kondisi reset (kondisi pemicu tidak terpenuhi) terpenuhi.
				self::updateRuleStatus($rule_id, 'HEALTH', $assetnum, $pmnum, false, null, $new_health_rate);
			
			} else {
				// KASUS 3: 
				// Entah kondisi normal, atau kondisi "berbahaya" yang berkelanjutan (sudah dilaporkan).
				// Biarkan status apa adanya.
			}
		}
	}

	public static function sendJsonResponse($status, $message, $data = []) {
		
		$response = ['status' => $status, 'message' => $message];
		if (!empty($data)) {
			$response['data'] = $data;
		}
		echo json_encode($response);
		
		exit();
	}
	
	public static function getAssetHealthHistory(){
		
		$dates = [];        // Untuk sumbu X
		$lineData = [];     // Untuk data grafik garis
		$scatterData = [];  // Untuk data titik-titik kejadian (markers)

		$assetnum = Super::getParam("getAssetHealthHistory");
		
		
		// LANGKAH 1: AMBIL DATA UTAMA & WONUM
		
		$set = new Set();
		$healthRateResults = [];
		$wonumsToFetch = [];

		$assetHealthRateSet = $set->MboSetRemote("assethealthrate ahr")
			->where("ahr.assetnum = ? AND ahr.recordstatus = ?", $assetnum, 'ACTIVE')
			->setRelationship("workorder wo", "ahr.wonum = wo.wonum and ahr.assetnum = wo.assetnum")
			->order("ahr.assessmentdate ASC, ahr.assethealthrateid ASC")
			->MboRemote("ahr.wonum, ahr.healthrate, ahr.assessmentdate, ahr.basis, ahr.notes, ahr.assessedby, wo.description, wo.actualcost")
			->getMboSet();
			
		if($assetHealthRateSet->isEmpty()){
			self::sendJsonResponse("error", "No asset health history found for this asset.");
			return; 
		}

		while ($row = $assetHealthRateSet->fetch()) {
			$healthRateResults[] = $row;
			if (!empty($row['wonum'])) {
				$wonumsToFetch[] = $row['wonum'];
			}
		}

		
		// LANGKAH 2: AMBIL SEMUA DATA PENDUKUNG (ROOT CAUSE)
		
		$rootCausesByWonum = [];
		if (!empty($wonumsToFetch)) {
			$rcfaSet = (new Set())->MboSetRemote("worootcause")
				->where("wonum IN ('" . implode("','", array_unique($wonumsToFetch)) . "')")
				->getMboSet();

			// Buat "kamus" root cause dengan kunci wonum
			while ($rcfaRow = $rcfaSet->fetch()) {
				$wonum = $rcfaRow['wonum'];
				$rootCausesByWonum[$wonum][] = [
					'failurecode' => $rcfaRow['failurecode'],
					'zone' => $rcfaRow['zone'],
					'failurelevel' => $rcfaRow['failurelevel']
				];
			}
		}

		
		// LANGKAH 3: LOOP UTAMA YANG BERSIH, CEPAT, DAN TANPA QUERY
		
		$dates = [];
		$lineData = [];
		$scatterData = [];

		foreach ($healthRateResults as $healthRate) {
			$wonum = $healthRate['wonum'];
			
			// Ambil data root cause dari "kamus", super cepat!
			// Gunakan null coalescing operator (??) untuk default ke array kosong jika tidak ada
			$rcfa = $rootCausesByWonum[$wonum] ?? [];
			
			// Logika penentuan warna, sudah benar
			$markerColor = '#007bff';
			switch ($healthRate['basis']) {
				case 'POST_EMERGENCY': $markerColor = '#dc3545'; break;
				case 'POST_CORRECTIVE': $markerColor = '#ffc107'; break;
				case 'POST_PREVENTIVE': $markerColor = '#28a745'; break;
			}
			
			// Susun data untuk chart, sama seperti sebelumnya
			$dates[] = $healthRate['assessmentdate'];
			$lineData[] = (int)$healthRate['healthrate'];

			$scatterPoint = [
				'value' => [$healthRate['assessmentdate'], (int)$healthRate['healthrate']],
				'itemStyle' => ['color' => $markerColor],
				'tooltipInfo' => [
					'wonum' => $wonum,
					'basis' => str_replace('_', ' ', $healthRate['basis']),
					'notes' => $healthRate['notes'],
					'assessedBy' => $healthRate['assessedby'],
					'woDescription' => $healthRate['description'],
					'rcfa' => $rcfa, // Ambil dari variabel yang sudah siap
					'actualCost' => 'Rp ' . number_format((float)$healthRate['actualcost'], 0, ',', '.')
				]
			];
			$scatterData[] = $scatterPoint;
		}

		$chartData = [
			'dates' => $dates,
			'lineData' => $lineData,
			'scatterData' => $scatterData,
			'markLines' => [ // Data untuk garis penanda (threshold)
				['yAxis' => 75, 'name' => 'Warning'],
				['yAxis' => 50, 'name' => 'Critical']
			]
		];
		
		// Kirim respons JSON yang sudah siap "dimakan" oleh ECharts
		self::sendJsonResponse("success", "Data retrieved successfully", $chartData);
	}


	public static function createprassetmod(){
		$assetnum = Super::getParam("createprassetmod");
		$assetmodid = Super::getParam("assetmodid");
		$prnum = Super::getParam("new_prnum");
		
		$whereClause = "assetnum='$assetnum'";
		$status = "new";
		
		$set = new Set();
		$assetSet = $set->MboSetRemote("assets")->setWhere($whereClause)->MboRemote("orgcode")->MboRemote("sitecode")->getMboSet();
		$asset = $assetSet->getMbo();
		$orgcode = $asset->getString("orgcode");
		$sitecode = $asset->getString("sitecode");
		$enterdate = Xbo::setenterdate();
		$enterby = Xbo::getLoginUser();
		$glaccount = Xbo::getAssetGLAccount($assetnum, $orgcode, $sitecode);
		
		$set = new Set();
		$assetmodSet = $set->MboSetRemote("assetmod")
		->setWhere("assetnum='$assetnum' and assetmodid='$assetmodid'")
		->MboRemote("assetnum")
		->MboRemote("subject")
		->MboRemote("description")
		->MboRemote("objective")
		->MboRemote("status")
		->MboRemote("totalcost")
		->MboRemote("modificationdate")
		->MboRemote("approvedby")
		->MboRemote("approveddate")
		->getMboSet();	
		
		
		if($assetmodSet->isEmpty()){
			$msg = "assetmodnotfound";
			Set::throws(["msg"=>$msg, "prnum"=>$prnum]);
			return;
		}else{
			$setpr = new Set();
			$prSet = $setpr->MboSetRemote("pr")
			->setWhere("assetmodid='$assetmodid'")
			->MboRemote("prnum")->getMboSet();
			
			if($prSet->isEmpty()){
				//Create PR Header
				$am = $assetmodSet->getMbo();
				$assetnum =  $am->getString("assetnum");
				$set = new Set();
				$assetSet = $set->MboSetRemote("assets")->setWhere("assetnum='$assetnum'")->MboRemote("orgcode")->MboRemote("sitecode")->getMboSet();
				$asset = $assetSet->getMbo();
				$orgcode = $asset->getString("orgcode");
				$sitecode = $asset->getString("sitecode");
				$description = $am->getString("subject");
				$requestedby = $am->getString("approvedby");
				$priority = "normal";
				$remarks = $am->getString("description");
				$statusdate = Xbo::setenterdate();
				$requesteddate = $am->getString("approveddate");
				$requireddate = $requesteddate;
				
				//get Default Currency
				$setc = new Set();
				$currencycode = $setc->MboSetRemote("organization")->setWhere("orgcode='$orgcode'")->MboRemote("basecurrency")->getMboSet()->getMbo()->getString("basecurrency");
				$exchangerate = 1;
				
				
				$setpr = new Set();
				$setpr->MboSetRemote("pr")
				->setValue("prnum", $prnum)
				->setValue("description", $description)
				->setValue("requestedby", $requestedby)
				->setValue("priority", $priority)
				->setValue("remarks", $remarks)
				->setValue("status", $status)
				->setValue("statusdate", $statusdate)
				->setValue("requesteddate", $requesteddate)
				->setValue("requireddate", $requireddate)
				->setValue("currencycode", $currencycode)
				->setValue("exchangerate", $exchangerate)
				->setValue("enterdate", $enterdate)
				->setValue("enterby", $enterby)
				->setValue("orgcode", $orgcode)
				->setValue("sitecode", $sitecode)
				->setValue("assetmodid", $assetmodid)
				->add();
				
			}else{
				//PR Header has already exist
			}
			
			$set = new Set();
			$itemSet = new Set();
			$prlineCheckSet = new Set();
			$warehouseSet = new Set();

			
			// LANGKAH 1: AMBIL DATA UTAMA (ASSETMODLINE)
			
			$assetModLines = [];
			$itemnumsToFetch = [];
			$assetmodlineIdsToFetch = [];

			$assetmodLineSet = $set->MboSetRemote("assetmodline")
				->where("assetmodid = ?", $assetmodid)
				->getMboSet();
				
			while ($row = $assetmodLineSet->fetch()) {
				$assetModLines[] = $row;
				if (!empty($row['itemnum'])) {
					$itemnumsToFetch[] = $row['itemnum'];
				}
				$assetmodlineIdsToFetch[] = $row['assetmodlineid'];
			}

			// Jika tidak ada data, hentikan proses
			if (empty($assetModLines)) {
				return; // Atau kirim response yang sesuai
			}

			
			// LANGKAH 2, 3, 4: AMBIL SEMUA DATA PENDUKUNG 
			

			// 2. Buat "kamus" deskripsi item
			$itemDescriptions = [];
			if (!empty($itemnumsToFetch)) {
				$itemDataSet = $itemSet->MboSetRemote("item")
					->where("itemnum IN ('" . implode("','", array_unique($itemnumsToFetch)) . "')")
					->MboRemote("itemnum, description")
					->getMboSet();
				while ($itemRow = $itemDataSet->fetch()) {
					$itemDescriptions[$itemRow['itemnum']] = $itemRow['description'];
				}
			}

			// 3. Buat "kamus" prline yang sudah ada
			$existingPrLines = [];
			if (!empty($assetmodlineIdsToFetch)) {
				$prlineCheckDataSet = $prlineCheckSet->MboSetRemote("prline")
					->where("assetmodlineid IN ('" . implode("','", array_unique($assetmodlineIdsToFetch)) . "')")
					->MboRemote("assetmodlineid")
					->getMboSet();
				while ($prlineRow = $prlineCheckDataSet->fetch()) {
					$existingPrLines[$prlineRow['assetmodlineid']] = true;
				}
			}

			// 4. Ambil default warehouse SEKALI SAJA
			$defaultWarehouse = $warehouseSet->MboSetRemote("warehouse")->limit("0,1")->getMboSet()->fetch();

			
			// LANGKAH 5: LOOP DI MEMORI
			
			$prLinesToAdd = [];
			foreach ($assetModLines as $aml) {
				$assetmodlineid = $aml['assetmodlineid'];
				// Lewati jika prline sudah ada
				if (isset($existingPrLines[$assetmodlineid])) {
					continue;
				}
				
				$aml_linetype = strtolower($aml['linetype']);
				if ($aml_linetype == "part" || $aml_linetype == "consumable") $linetype = "item";
				elseif ($aml_linetype == "service" || $aml_linetype == "other") $linetype = "services";
				else continue; 

				$itemnum = $aml['itemnum'];
				$description = ($linetype == "item")
					? ($itemDescriptions[$itemnum] ?? 'Item description not found') // Ambil dari kamus
					: $aml['description'];

				// Siapkan data untuk di-insert
				$prLinesToAdd[] = [
					'prnum' => $prnum,
					'prlinenum' => Xbo::getLineNumber("prline", "prlinenum", "prnum='$prnum'"),
					'linetype' => $linetype,
					'itemnum' => $itemnum,
					'description' => $description,
					'unitcost' => $aml['unitcost'],
					'orderqty' => $aml['quantity'],
					'linecost' => $aml['linecost'],
					'enterby' => $enterby,
					'enterdate' => $enterdate,
					'requireddate' => $requireddate,
					'orderunit' => $aml['measureunit'],
					'conversionfactor' => 1,
					'warehouse' => $defaultWarehouse ? $defaultWarehouse['warehouse'] : 'DEFAULT_WH',
					'receiptstatus' => "NONE",
					'gldebitacct' => $defaultWarehouse ? $defaultWarehouse['controlacc'] : 'DEFAULT_GL'
				];
			}

			
			// LANGKAH 6: BULK INSERT DALAM SATU TRANSAKSI YANG AMAN
			
			if (!empty($prLinesToAdd)) {
				$tm = TransactionManager::getInstance();
				try {
					$tm->beginTransaction();
					foreach ($prLinesToAdd as $data) {
						$prlSet = new Set();
						$prlSet->useTransactionManager()
							->MboSetRemote("prline");
						
						// Loop setValue dari data array
						foreach ($data as $column => $value) {
							$prlSet->setValue($column, $value);
						}
						$prlSet->add();
					}
					$tm->commit();
				} catch (Exception $e) {
					$tm->rollback();
					throw $e; // Lempar error untuk ditangani di level atas
				}
			}
			
			
			//Calculate PR pretax, tax, Total Cost, total basecost
		
			$set = new Set();
			$prSet = $set->MboSetRemote("prline")->setWhere("prnum='$prnum'")->MboRemote("sum(linecost) as pretaxcost")->MboRemote("sum(taxcost) as total_taxcost")->getMboSet();
			$pr = $prSet->getMbo();
			
			$pretaxtotal = $pr->getString("pretaxcost");
			$total_taxcost = $pr->getString("total_taxcost");
			$totalbasecost = floatval($pretaxtotal)+floatval($total_taxcost);
		
			//Update PR Header Cost, use default exchange rate.
			$set = new Set();
			$set->MboSetRemote("pr")->setWhere("prnum='$prnum'")
			->setValue("pretaxtotal",$pretaxtotal )
			->setValue("totaltax",$total_taxcost )
			->setValue("totalcost",$totalbasecost )
			->setValue("totalbasecost",$totalbasecost )
			->change();
			
			//create pr status date history
			$remarks = "PR for Asset Modification.";
			$set = new Set();
			$set->MboSetRemote("prstatus")->setValue("prnum", $prnum)->setValue("status", $status)->setValue("changeby", $enterby)->setValue("changedate", $changedate)->setValue("remarks", $remarks)->add();
			$msg = "success";
			
			//update Asset Modification Proposal to Completed
			$set = new Set();
			$set->MboSetRemote("assetmod")->setWhere("assetmodid='$assetmodid'")->setValue("status", "completed")->change();
		}
		Set::throws(["msg"=>$msg, "prnum"=>$prnum]);
	}
  public static function approveModification() {
    $approveModification = Super::getParam("approveModification");

		if (is_array($approveModification)) {
		 
		  $assetmodid = isset($approveModification['assetmodid']) ? htmlspecialchars($approveModification['assetmodid'], ENT_QUOTES, 'UTF-8') : null;
		  $assetnum = isset($approveModification['assetnum']) ? htmlspecialchars($approveModification['assetnum'], ENT_QUOTES, 'UTF-8') : null;
		  $rowindex = isset($approveModification['rowindex']) ? htmlspecialchars($approveModification['rowindex'], ENT_QUOTES, 'UTF-8') : null;
		  $status = isset($approveModification['status']) ? htmlspecialchars($approveModification['status'], ENT_QUOTES, 'UTF-8') : null;
			$enterby = Xbo::getLoginUser();
			$enterdate = Xbo::setenterdate();
			
			if($status == "approved" || $status == "completed"){
				$isapproved = 'Y';
				$approvedby = $enterby;
				$approveddate = $enterdate;
				$set = new Set();
				$set->MboSetRemote("assetmod")->setWhere("assetmodid='$assetmodid' and assetnum='$assetnum'")
				->setValue("status", $status)
				->setValue("approvedby",$approvedby)
				->setValue("isapproved",$isapproved)
				->setValue("statusdate",$approveddate)
				->setValue("approveddate",$approveddate)
				->change();
				
			}else{
				$isapproved = 'N';
				$approvedby = null;
				$approveddate = null;
				$set = new Set();
				$set->MboSetRemote("assetmod")->setWhere("assetmodid='$assetmodid' and assetnum='$assetnum'")
				->setValue("status", $status)
				->setValue("approvedby",$approvedby)
				->setValue("isapproved",$isapproved)
				->setValue("statusdate",$enterdate)
				->setValue("approveddate",$approveddate)
				->change();
			}
			$msg = "success";
		} else {
			$msg = "datainvalid";
			$status = null;
		}
		
		Set::throws(["msg"=>$msg, "status"=>$status]);
	  }

public static function requestItemNum() {
    $nullItems = Super::getParam("requestItemNum");
    $object = Super::getParam("object");

    if (is_array($nullItems)) {
        foreach ($nullItems as $itemData) { 
            $item = $itemData["item"] ?? null;  
            $description = $itemData["description"] ?? null; 
            $id = $itemData["ID"] ?? null;
            $set = new Set();
            $itemRequestSet = $set->MboSetRemote("itemrequest")->setWhere("object='$object' and objectkey='$id'")->getMboSet();

            if ($itemRequestSet->isEmpty()) {
                $set2 = new Set();
                $set2->MboSetRemote("itemrequest")
                    ->setValue("object", $object)
                    ->setValue("objectkey", $id)
                    ->setValue("description", $item)
                    ->setValue("remarks", $description)
                    ->add();
            }
        }
        Set::throws(["msg" => "success", "message" => "Item number requests submitted."]);
    } else {
       
        Set::throws(["msg" => "error", "message" => "Invalid data format."]);
    }
}

	public static function checkPRMod(){
		$assetnum = Super::getParam("checkPRMod");
		$assetmodid = Super::getParam("assetmodid");
		$getAutoNum = new Super();
		
		$remarks = null;
		$set = new Set();
		$assetmodlineSet = $set->MboSetRemote("assetmodline")->setWhere("assetmodid='$assetmodid' and assetnum='$assetnum' and linetype in('Part', 'Consumable') and itemnum is null")
		->MboRemote("item")->MboRemote("description")->MboRemote("assetmodlineid")
		->getMboSet();
		$nullItems = [];
		
		$counts = $assetmodlineSet->counts();
		if($counts >0){
			$nullItems = []; // Inisialisasi array di luar loop

			for ($i = 0; $i < $counts; $i++) {
				$aml = $assetmodlineSet->getMbo();
				$item = $aml->getString("item");
				$description = $aml->getString("description");
				$assetmodlineid = $aml->getString("assetmodlineid");
				$nullItems[] = ["item" => $item, "description" => $description, "ID" => $assetmodlineid];
			}
			
			Set::throws(["msg"=>"nullItem", "remarks"=>"Some Items require Item Number",  "nullItems"=>$nullItems, "object"=>"assetmodline"]);
			return;
		}else{
			//Check PR for this assetmod is already exist
			$set = new Set();
			$prSet =  $set->MboSetRemote("pr")->setWhere("assetmodid='$assetmodid'")->MboRemote("prnum")->getMboSet();
			if($prSet->isEmpty()){
				$prnum = $getAutoNum->getAutoNumber("pr", "update_next_value")->autonumber;
				$remarks = "newpr";
			}else{
				//PR Exist
				$pr = $prSet->getMbo();
				$prnum = $pr->getString("prnum");
				$remarks = "prexist";
			}
		}
		
		$set = new Set();
		$prdesc = $set->MboSetRemote("assetmod")->setWhere("assetmodid='$assetmodid'")->MboRemote("subject")->getMboSet()->getMbo()->getString("subject");
		Set::throws(["msg"=>"success", "prnum"=>$prnum, "prdesc"=>$prdesc, "remarks"=>$remarks]);
	}
		
	
	public static function productivityModChart() {
	$assetnum = Super::getParam("productivityModChart");
	$assetmodid = Super::getParam("assetmodid");
	
	if(empty($assetmodid)){
		$whereclause = "assetnum='$assetnum'";
	}else{
		$whereclause = "assetnum='$assetnum' and assetmodid='$assetmodid'";
	}
	
	
	$set = new Set();
    $assetmodproductivitySet = $set->MboSetRemote("assetmodproductivity")
        ->setWhere($whereclause)
        ->MboRemote("assetnum")
        ->MboRemote("assetparamnum")
        ->MboRemote("value as potentialvalue")
        ->MboRemote("actualvalue")
        ->MboRemote("changevalue")
		->MboRemote("changetype")
        ->MboRemote("estimatedvalue")
		->MboRemote("measureunit")
		->MboRemote("year")
        ->MboRemote("estyear")
        ->getMboSet();

	$counts = $assetmodproductivitySet->counts();

	$chartData = [
        "xAxis" => [],
        "series" => [
            [
                "name" => "Actual Productivity",
                "type" => "bar",
                "stack" => "total",
                "data" => []
            ],
            [
                "name" => "Predicted Productivity",
                "type" => "bar",
                "stack" => "total",
                "data" => []
            ]
        ],
        "tooltips" => [] 
    ];
	
	if ($counts > 0) {
        for ($i = 0; $i < $counts; $i++) {
            $amp = $assetmodproductivitySet->getMbo();
            $assetparamnum = $amp->getString("assetparamnum");
            $potentialvalue = floatval($amp->getString("potentialvalue"));
            $actualvalue = floatval($amp->getString("actualvalue"));
			$changevalue = floatval($amp->getString("changevalue"));
			$estimatedvalue = floatval($amp->getString("estimatedvalue"));
			$measureunit = floatval($amp->getString("measureunit"));
			$changetype = $amp->getString("changetype");
            $year = $amp->getString("year");
            $estyear = $amp->getString("estyear");

            $chartData["xAxis"][] = $assetparamnum; 
            $chartData["series"][0]["data"][] = $actualvalue;
            $chartData["series"][1]["data"][] = $estimatedvalue;
			
			$set2 = new Set();
			$prodDesc = $set2->MboSetRemote("assetparam")->setWhere("assetparamnum='$assetparamnum'")->MboRemote("description")->getMboSet()->getMbo()->getString("description");
           
			$tooltip = htmlspecialchars($prodDesc) . " ($assetparamnum)<br>";
            $tooltip .= "<b>Potential Value:</b> " . htmlspecialchars(number_format($potentialvalue, 2)) . " $measureunit<br>";
			$tooltip .= "<b>Actual Value:</b> " . htmlspecialchars(number_format($actualvalue, 2)) . " $measureunit<br>";
			$tooltip .= "<b>Change Type:</b> " . htmlspecialchars($changetype)."<br>";
			$tooltip .= "<b>Estimated Value:</b> " . htmlspecialchars(number_format($estimatedvalue, 2)) . " $measureunit<br>";
            $tooltip .= "<b>Estimated Year:</b> " . htmlspecialchars($estyear) . "<br>";
			$tooltip .= "<b>Year:</b> " . htmlspecialchars($year) . "<br>";
            $chartData["tooltips"][] = $tooltip;
        }
    } else {
        $chartData["xAxis"][] = "No Data";
        $chartData["series"][0]["data"][] = 0;
        $chartData["series"][1]["data"][] = 0;
        $chartData["tooltips"][] = "No Data Available";
    }
	
	
	$set = new Set();
	$assetModSet = $set->MboSetRemote("assetmod")->setWhere("assetnum = '$assetnum'")
	->MboRemote("assetmodid")
	->MboRemote("subject")
	->MboRemote("description")
	->MboRemote("totalcost")
	->getMboSet();
	
	$counts = $assetModSet->counts();
	if($counts == 0 ){
		$options = null;
	}else{
		$labels=[];
		for($i=0; $i<$counts; $i++){
			$am = $assetModSet->getMbo();
			$assetmodid = $am->getString("assetmodid");
			$subject = $am->getString("subject");
			$description = $am->getString("description");
			$totalcost = $am->getString("totalcost");
			$label = "$subject | ".number_format(floatval($totalcost), 2, ',', '.');
			$options[] = [
				"value" => htmlspecialchars($assetmodid),
				"label" => htmlspecialchars($label)
			];
		}
	
	}
	
    header('Content-Type: application/json');	
    echo json_encode(["chartData"=>$chartData, "options" => $options]);
    exit();
	
}
	public static function downtimeModChart() {
		$assetnum = Super::getParam("downtimeModChart");
		$assetmodid = Super::getParam("assetmodid");
		
		if(empty($assetmodid)){
			$whereclause = "assetnum='$assetnum'";
		}else{
			$whereclause = "assetnum='$assetnum' and assetmodid='$assetmodid'";
		}
		
		$set = new Set();
		$assetmoddowntimeSet = $set->MboSetRemote("assetmoddowntime")
			->setWhere($whereclause)
			->MboRemote("assetnum")
			->MboRemote("failurecode")
			->MboRemote("histdurationmin")
			->MboRemote("estreduction")
			->MboRemote("predictdurationmin")
			->MboRemote("year")
			->MboRemote("estyear")
			->getMboSet();

		$counts = $assetmoddowntimeSet->counts();

		$chartData = [
			"xAxis" => [],
			"series" => [
				[
					"name" => "Historical Downtime",
					"type" => "bar",
					"stack" => "total",
					"data" => []
				],
				[
					"name" => "Predicted Downtime",
					"type" => "bar",
					"stack" => "total",
					"data" => []
				]
			],
			"tooltips" => [] 
		];

		if ($counts > 0) {
			for ($i = 0; $i < $counts; $i++) {
				$assetmoddowntime = $assetmoddowntimeSet->getMbo();
				$failurecode = $assetmoddowntime->getString("failurecode");
				$histdurationmin = floatval($assetmoddowntime->getString("histdurationmin"));
				$predictdurationmin = floatval($assetmoddowntime->getString("predictdurationmin"));
				$year = $assetmoddowntime->getString("year");
				$estyear = $assetmoddowntime->getString("estyear");

				$chartData["xAxis"][] = $failurecode; 
				$chartData["series"][0]["data"][] = $histdurationmin;
				$chartData["series"][1]["data"][] = $predictdurationmin;

				$tooltip = "<b>Failure Code:</b> " . htmlspecialchars($failurecode) . "<br>";
				$tooltip .= "<b>Year:</b> " . htmlspecialchars($year) . "<br>";
				$tooltip .= "<b>Historical Downtime:</b> " . htmlspecialchars(number_format($histdurationmin, 2)) . " minutes<br>";
				$tooltip .= "<b>Estimated Year:</b> " . htmlspecialchars($estyear) . "<br>";
				$tooltip .= "<b>Predicted Downtime:</b> " . htmlspecialchars(number_format($predictdurationmin, 2)) . " minutes";

				$chartData["tooltips"][] = $tooltip;
			}
		} else {
			// Handle case where there is no data
			$chartData["xAxis"][] = "No Data";
			$chartData["series"][0]["data"][] = 0;
			$chartData["series"][1]["data"][] = 0;
			$chartData["tooltips"][] = "No Data Available";
		}

		$set = new Set();
		$assetModSet = $set->MboSetRemote("assetmod")->setWhere("assetnum = '$assetnum'")
		->MboRemote("assetmodid")
		->MboRemote("subject")
		->MboRemote("description")
		->MboRemote("totalcost")
		->getMboSet();
		
		$counts = $assetModSet->counts();
		
		$labels=[];
		$counts = $assetModSet->counts();
		if($counts == 0 ){
			$options = null;
		}else{
			for($i=0; $i<$counts; $i++){
				$am = $assetModSet->getMbo();
				$assetmodid = $am->getString("assetmodid");
				$subject = $am->getString("subject");
				$description = $am->getString("description");
				$totalcost = $am->getString("totalcost");
				$label = "$subject | ".number_format(floatval($totalcost), 2, ',', '.');
				$options[] = [
					"value" => htmlspecialchars($assetmodid),
					"label" => htmlspecialchars($label)
				];
			}
		}
		
		
		header('Content-Type: application/json');	
		echo json_encode(["chartData"=>$chartData, "options" => $options]);
		exit();
	}

public static function meterModChart() {
    $assetnum = Super::getParam("meterModChart");
    $selectedMeter = Super::getParam("selectedMeter"); 
	$assetmodid = Super::getParam("assetmodid");
	
	if(empty($assetmodid)){
		$propWC = "";
	}else{
		$propWC = "and assetmodid='$assetmodid'";
	}
	
	
    $set = new Set();
    $assetMeterSet = $set->MboSetRemote("assetmeter am")
                       ->setWhere("am.assetnum='$assetnum'")
                       ->setRelationship("meter m", "m.meternum = am.meternum")
                       ->MboRemote("am.meternum")
                       ->MboRemote("m.description")
                       ->MboRemote("m.metertype")
                       ->getMboSet();
    
    $counts = $assetMeterSet->counts();
    $options = [];
    $firstMeter = null; // Untuk menyimpan meter pertama sebagai default
    
    for ($i = 0; $i < $counts; $i++) {
        $asset = $assetMeterSet->getMbo();
        $meternum = $asset->getString("meternum");
        $description = $asset->getString("description");
        $metertype = $asset->getString("metertype");
        
        // Simpan meter pertama sebagai default jika tidak ada yang dipilih
        if ($i == 0 && empty($selectedMeter)) {
            $firstMeter = $meternum;
        }
        
        // Buat value untuk options
        $value = $meternum;
        $label = "$meternum - $description - $metertype";
        
        // Tambahkan data ke array options
        $options[] = [
            "value" => htmlspecialchars($value),
            "label" => htmlspecialchars($label)
        ];
    }
    
    $meterToUse = !empty($selectedMeter) ? $selectedMeter : $firstMeter;
    
    $chartData = [
        "xAxis" => [],
        "series" => [
            [
                "name" => "Meter Readings",
                "type" => "line",
                "data" => []
            ],
            [
                "name" => "Modified Reading",
                "type" => "line",
                "data" => []
            ]
        ]
    ];
    
    if (!empty($meterToUse)) {
        // Ambil data modification meter sekali untuk efisiensi
        $set2 = new Set();
        $assetModMeterSet = $set2->MboSetRemote("assetmodmeter")
            ->setWhere("assetnum='$assetnum' and meternum='$meterToUse' $propWC ")
            ->MboRemote("changevalue")
            ->MboRemote("changetype")
            ->getMboSet();
        
        $changevalue = 0;
        $changetype = "";
        $countModMeter = $assetModMeterSet->counts();
        
        if ($countModMeter > 0) {
            $assetmodMeter = $assetModMeterSet->getMbo();
            $changevalue = floatval($assetmodMeter->getString("changevalue"));
            $changetype = $assetmodMeter->getString("changetype");
        }
        
        $set = new Set();
        $assetMeterHistorySet = $set->MboSetRemote("assetmeterhistory amh")
            ->setWhere("amh.assetnum='$assetnum' and amh.meternum='$meterToUse'")
            ->setRelationship("meter m", "m.meternum = amh.meternum")
            ->MboRemote("amh.value")
            ->MboRemote("amh.readingdate")
            ->getMboSet();
        
        $counts = $assetMeterHistorySet->counts();
        
        if($counts > 0){
            for ($i = 0; $i < $counts; $i++) {
                $amh = $assetMeterHistorySet->getMbo();
                $value = floatval($amh->getString("value"));
                $readingdate = $amh->getString("readingdate");
                
                // Hitung modification value
                $modificationValue = 0;
                if ($countModMeter > 0) {
                    if ($changetype === "PERCENTAGE") {
                        $modificationValue = $value + ($value * ($changevalue / 100));
                    } else if ($changetype === "ABSOLUTE") {
                        $modificationValue = $value + $changevalue;
                    } else {
                        $modificationValue = $value; // Jika tipe tidak dikenali, gunakan nilai asli
                    }
                } else {
                    $modificationValue = $value; // Jika tidak ada modification, gunakan nilai asli
                }
                
                // Format Tanggal untuk xAxis (Asumsi format YYYY-MM-DD HH:MM:SS)
                $formattedDate = date('Y-m-d', strtotime($readingdate));
                
                // Tambahkan data ke array chartData
                $chartData["xAxis"][] = $formattedDate;
                $chartData["series"][0]["data"][] = $value;
                $chartData["series"][1]["data"][] = $modificationValue;
            }
        } else {
            $chartData["xAxis"][] = "Data Kosong";
            $chartData["series"][0]["data"][] = 0;
            $chartData["series"][1]["data"][] = 0;
        }
    } else {
        $chartData["xAxis"][] = "No Meter Selected";
        $chartData["series"][0]["data"][] = 0;
        $chartData["series"][1]["data"][] = 0;
    }
   
   
	$set = new Set();
	$assetModSet = $set->MboSetRemote("assetmod")->setWhere("assetnum = '$assetnum'")
	->MboRemote("assetmodid")
	->MboRemote("subject")
	->MboRemote("description")
	->MboRemote("totalcost")
	->getMboSet();
	
	$counts = $assetModSet->counts();
	$proposal_options = [];
	$labels=[];
	for($i=0; $i<$counts; $i++){
		$am = $assetModSet->getMbo();
		$assetmodid = $am->getString("assetmodid");
		$subject = $am->getString("subject");
		$description = $am->getString("description");
		$totalcost = $am->getString("totalcost");
		$label = "$subject | ".number_format(floatval($totalcost), 2, ',', '.');
		$proposal_options[] = [
            "value" => htmlspecialchars($assetmodid),
            "label" => htmlspecialchars($label)
        ];
		
		
	}
	
	
	
    // Response dengan data options dan chart
    $response = [
        "options" => $options,
		"proposal_options"=>$proposal_options,
        "chartData" => $chartData,
        "selectedMeter" => $meterToUse
    ];
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

public static function createAnalyticForROI(){
    $data = Super::getParam("createroi");
    $daysinayear = $data["daysinayear"]; 

    $assetmodid = $data["assetmodid"];

    $set = new Set();
    $assetModSet = $set->MboSetRemote("assetmod")->setWhere("assetmodid='$assetmodid'")
	->MboRemote("subject")->MboRemote("description")->MboRemote("objective")->MboRemote("totalcost")->MboRemote("assetnum")
	->getMboSet();
	
	
    $assetmod = $assetModSet->getMbo();
    $subject = $assetmod->getString("subject");
    $description = $assetmod->getString("description");
    $objective = $assetmod->getString("objective");
    $totalmodificationCost = floatval($assetmod->getString("totalcost"));
    $objective = str_replace("[br]", "\n", $objective);
	$assetnum = $assetmod->getString("assetnum");
	
	$set = new Set();
	$assetSet = $set->MboSetRemote("assets")->setWhere("assetnum='$assetnum'")
	->MboRemote("description")->MboRemote("spectext")->getMboSet();
	
	$asset = $assetSet->getMbo();
	$asset_description = $asset->getString("description");
	$asset_specification = $asset->getString("spectext");
	
	$orion = new stdClass;
	$orion->document_type = "Equipment(Asset) Modification Proposal";
	$orion->subject = $subject;
	$orion->objective = $objective;
	$orion->proposal_description = $description;
	$orion->affected_asset_description = $asset_description;
	$orion->affected_asset_code = $assetnum;
	$orion->affected_asset_specification= $asset_specification;
	$orion->total_modification_cost = $totalmodificationCost;
	
	
    echo "<style>
        @import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@300;400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap');
        
        :root {
            --primary-blue: #0f62fe;
            --primary-blue-hover: #0353e9;
            --secondary-blue: #4589ff;
            --accent-teal: #009d9a;
            --success-green: #24a148;
            --warning-orange: #ff832b;
            --error-red: #da1e28;
            --neutral-gray-100: #f4f4f4;
            --neutral-gray-200: #e0e0e0;
            --neutral-gray-300: #c6c6c6;
            --neutral-gray-700: #525252;
            --neutral-gray-900: #161616;
            --text-primary: #161616;
            --text-secondary: #525252;
            --text-helper: #6f6f6f;
            --background-light: #ffffff;
            --background-subtle: #f4f4f4;
            --shadow-light: 0 2px 6px rgba(0, 0, 0, 0.05);
            --shadow-medium: 0 4px 12px rgba(0, 0, 0, 0.1);
            --shadow-strong: 0 8px 24px rgba(0, 0, 0, 0.15);
            --border-radius-sm: 4px;
            --border-radius-md: 8px;
            --border-radius-lg: 12px;
            --spacing-xs: 4px;
            --spacing-sm: 8px;
            --spacing-md: 16px;
            --spacing-lg: 24px;
            --spacing-xl: 32px;
            --spacing-xxl: 48px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'IBM Plex Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--background-subtle);
            color: var(--text-primary);
            line-height: 1.5;
            font-size: 14px;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        .enterprise-container {
            max-width: 1440px;
            margin: 0 auto;
            background: var(--background-light);
            min-height: 100vh;
        }
        
        /* Header Section */
        .header-main {
            background: var(--background-light);
            border-bottom: 1px solid var(--neutral-gray-200);
            padding: var(--spacing-lg) var(--spacing-xl);
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: var(--spacing-xl);
        }
        
        .header-left {
            flex: 1;
        }
        
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            color: var(--text-helper);
            font-size: 12px;
            margin-bottom: var(--spacing-sm);
            text-transform: uppercase;
            letter-spacing: 0.32px;
        }
        
        .breadcrumb-separator {
            color: var(--neutral-gray-300);
        }
        
        .page-title {
            font-size: 28px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: var(--spacing-xs);
            line-height: 1.25;
        }
        
        .page-subtitle {
            color: var(--text-secondary);
            font-size: 16px;
            font-weight: 400;
        }
        
        .header-meta {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-sm);
            min-width: 200px;
        }
        
        .meta-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
        }
        
        .meta-label {
            color: var(--text-helper);
            text-transform: uppercase;
            letter-spacing: 0.32px;
        }
        
        .meta-value {
            color: var(--text-primary);
            font-weight: 500;
            font-family: 'IBM Plex Mono', monospace;
        }
        
        /* Main Content */
        .main-content {
            padding: var(--spacing-xl);
            display: grid;
            grid-template-columns: 1fr 320px;
            gap: var(--spacing-xl);
        }
        
        .content-primary {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-xl);
        }
        
        .content-sidebar {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-lg);
        }
        
        /* Card Components */
        .card {
            background: var(--background-light);
            border: 1px solid var(--neutral-gray-200);
            border-radius: var(--border-radius-md);
            overflow: hidden;
        }
        
        .card-header {
            padding: var(--spacing-lg);
            border-bottom: 1px solid var(--neutral-gray-200);
            background: var(--background-subtle);
        }
        
        .card-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: var(--spacing-xs);
        }
        
        .card-description {
            font-size: 14px;
            color: var(--text-secondary);
        }
        
        .card-content {
            padding: var(--spacing-lg);
			max-width:1000px;
        }
        .card-sub-content {
		  word-wrap: break-word;         
		  overflow-wrap: break-word;
		  white-space: normal;  
		}
		
        /* Project Overview */
        .project-overview {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--spacing-lg);
        }
        
        .overview-item {
            padding: var(--spacing-lg);
            border: 1px solid var(--neutral-gray-200);
            border-radius: var(--border-radius-md);
            background: var(--background-light);
        }
        
        .overview-label {
            font-size: 12px;
            color: var(--text-helper);
            text-transform: uppercase;
            letter-spacing: 0.32px;
            margin-bottom: var(--spacing-sm);
        }
        
        .overview-value {
            font-size: 14px;
            color: var(--text-primary);
            line-height: 1.4;
        }
        
        .overview-objective {
            grid-column: 1 / -1;
        }
        
        .overview-objective .overview-value {
            white-space: pre-line;
        }
        
        /* Data Tables */
        .data-table {
            border-collapse: separate;
            border-spacing: 0;
            font-size: 12px;
        }
        
        .data-table thead th {
            background: var(--neutral-gray-100);
            border-bottom: 1px solid var(--neutral-gray-200);
            padding: var(--spacing-md);
            text-align: left;
            font-weight: 600;
            font-size: 12px;
            color: var(--text-primary);
            text-transform: uppercase;
            letter-spacing: 0.32px;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .data-table thead th:first-child {
            border-left: 1px solid var(--neutral-gray-200);
        }
        
        .data-table thead th:last-child {
            border-right: 1px solid var(--neutral-gray-200);
        }
        
        .data-table tbody td {
            padding: var(--spacing-md);
            border-bottom: 1px solid var(--neutral-gray-200);
            color: var(--text-primary);
            vertical-align: top;
        }
        
        .data-table tbody tr:hover {
            background-color: var(--background-subtle);
        }
        
        .data-table tbody td:first-child {
            border-left: 1px solid var(--neutral-gray-200);
        }
        
        .data-table tbody td:last-child {
            border-right: 1px solid var(--neutral-gray-200);
        }
        
        .data-table tbody tr:last-child td {
            border-bottom: 1px solid var(--neutral-gray-200);
        }
        
        .cell-primary {
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .cell-numeric {
            font-family: 'IBM Plex Mono', monospace;
            text-align: right;
            font-weight: 500;
        }
        
        .cell-positive {
            color: var(--success-green);
        }
        
        .cell-negative {
            color: var(--error-red);
        }
        
        .cell-highlight {
            background-color: #fff3cd;
            font-weight: 600;
        }
        
        /* KPI Cards */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: var(--spacing-lg);
        }
        
        .kpi-card {
            background: var(--background-light);
            border: 1px solid var(--neutral-gray-200);
            border-radius: var(--border-radius-md);
            padding: var(--spacing-lg);
            text-align: center;
            transition: all 0.2s ease;
        }
        
        .kpi-card:hover {
            box-shadow: var(--shadow-medium);
            border-color: var(--primary-blue);
        }
        
        .kpi-value {
            font-size: 24px;
            font-weight: 700;
            font-family: 'IBM Plex Mono', monospace;
            margin-bottom: var(--spacing-xs);
            color: var(--primary-blue);
        }
        
        .kpi-label {
            font-size: 12px;
            color: var(--text-helper);
            text-transform: uppercase;
            letter-spacing: 0.32px;
            line-height: 1.3;
        }
        
        .kpi-positive {
            color: var(--success-green) !important;
        }
        
        .kpi-negative {
            color: var(--error-red) !important;
        }
        
        /* Investment Summary */
        .investment-summary {
            background: linear-gradient(135deg, #f4f4f4 0%, #e8e8e8 100%);
            border: 1px solid var(--neutral-gray-300);
            border-radius: var(--border-radius-md);
            padding: var(--spacing-xl);
            text-align: center;
        }
        
        .investment-label {
            font-size: 14px;
            color: var(--text-secondary);
            margin-bottom: var(--spacing-sm);
            text-transform: uppercase;
            letter-spacing: 0.32px;
        }
        
        .investment-amount {
            font-size: 32px;
            font-weight: 700;
            font-family: 'IBM Plex Mono', monospace;
            color: var(--text-primary);
        }
        
        /* Status Indicators */
        .status-indicator {
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-xs);
            padding: var(--spacing-xs) var(--spacing-sm);
            border-radius: var(--border-radius-sm);
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.32px;
        }
        
        .status-positive {
            background: #d4f1d4;
            color: var(--success-green);
        }
        
        .status-negative {
            background: #fdd;
            color: var(--error-red);
        }
        
        .status-neutral {
            background: var(--neutral-gray-100);
            color: var(--text-secondary);
        }
        
        /* Section Dividers */
        .section-divider {
            height: 1px;
            background: var(--neutral-gray-200);
            margin: var(--spacing-xxl) 0;
        }
        
        /* Responsive Design */
        @media (max-width: 1200px) {
            .main-content {
                grid-template-columns: 1fr;
                gap: var(--spacing-lg);
            }
            
            .content-sidebar {
                order: -1;
            }
        }
        
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: var(--spacing-lg);
            }
            
            .project-overview {
                grid-template-columns: 1fr;
            }
            
            .main-content {
                padding: var(--spacing-lg);
            }
            
            .kpi-grid {
                grid-template-columns: 1fr;
            }
            
            .data-table {
                font-size: 12px;
            }
            
            .data-table thead th,
            .data-table tbody td {
                padding: var(--spacing-sm);
            }
        }
        
        /* Print Styles */
        @media print {
            body {
                background: white;
                font-size: 12px;
            }
            
            .enterprise-container {
                max-width: none;
                box-shadow: none;
            }
            
            .main-content {
                grid-template-columns: 1fr;
                padding: 0;
            }
            
            .card {
                break-inside: avoid;
                box-shadow: none;
                border: 1px solid #ccc;
            }
        }
        
        /* Accessibility */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
        
        /* Focus States */
        .kpi-card:focus,
        .overview-item:focus {
            outline: 2px solid var(--primary-blue);
            outline-offset: 2px;
        }
    </style>";

    echo "<div class='enterprise-container'>";
    
    // Header Section
    echo "<header class='header-main'>
            <div class='header-content'>
                <div class='header-left'>
                    <nav class='breadcrumb'>
                        <span>Asset Management</span>
                        <span class='breadcrumb-separator'>›</span>
                        <span>Modifications</span>
                        <span class='breadcrumb-separator'>›</span>
                        <span>ROI Analysis</span>
                    </nav>
                    <h1 class='page-title'>Asset Modification ROI Analysis</h1>
                    <p class='page-subtitle'>Comprehensive financial performance assessment and investment analysis</p>
                </div>
                <div class='header-meta'>
                    <div class='meta-item'>
                        <span class='meta-label'>Report ID</span>
                        <span class='meta-value'>" . strtoupper(substr(md5($assetmodid), 0, 8)) . "</span>
                    </div>
                    <div class='meta-item'>
                        <span class='meta-label'>Generated</span>
                        <span class='meta-value'>" . date('Y-m-d H:i') . "</span>
                    </div>
                    <div class='meta-item'>
                        <span class='meta-label'>Asset Mod ID</span>
                        <span class='meta-value'>" . htmlspecialchars($assetmodid) . "</span>
                    </div>
                </div>
            </div>
          </header>";
    
    echo "<main class='main-content'>";
    echo "<div class='content-primary'>";
    
    // Project Overview Card
    echo "<div class='card'>
            <div class='card-header'>
                <h2 class='card-title'>Project Overview</h2>
                <p class='card-description'>Key project information and strategic objectives</p>
            </div>
            <div class='card-content'>
                <div class='project-overview'>
                    <div class='overview-item'>
                        <div class='overview-label'>Project Subject</div>
                        <div class='overview-value'>" . htmlspecialchars($subject) . "</div>
                    </div>
                    <div class='overview-item'>
                        <div class='overview-label'>Description</div>
                        <div class='overview-value'>" . htmlspecialchars($description) . "</div>
                    </div>
                    <div class='overview-item overview-objective'>
                        <div class='overview-label'>Strategic Objective</div>
                        <div class='overview-value'>" . htmlspecialchars($objective) . "</div>
                    </div>
                </div>
            </div>
          </div>";

    // Initialize ROI Variables
    $totalRevenueIncrease = 0;
    $totalCostSavings = 0;

    echo "<div class='card'>
            <div class='card-header'>
                <h2 class='card-title'>Revenue Enhancement Analysis</h2>
                <p class='card-description'>Asset productivity parameters driving revenue growth</p>
            </div>
            <div class='card-content'>
                <table class='data-table'>
                    <thead>
                        <tr>
                            <th>Asset</th>
                            <th>Parameter</th>
                            <th>Description</th>
                            <th>Current Value</th>
                            <th>Predicted Daily</th>
                            <th>Predicted Annual</th>
                            <th>Unit</th>
                            <th>Est. Year</th>
                            <th>Potential Value</th>
                        </tr>
                    </thead>
                    <tbody>";
    
	$profitParams = [];
    foreach ($data["prodparamasprofit"] as $item) {
        $set = new Set();
        $assetparam_description = $set->MboSetRemote("assetparam")->setWhere("assetparamnum='".$item["assetparamnum"]."'")->MboRemote("description")->getMboSet()->getMbo()->getString("description");

        $set = new Set();
        $assetProductivitySet = $set->MboSetRemote("assetmodproductivity amp")
        ->setRelationship("assetproductivity ap", "ap.assetparamnum = amp.assetparamnum")
        ->setWhere("amp.assetmodid='$assetmodid' and amp.assetnum = '". $item["assetnum"]."' and amp.assetparamnum='".$item["assetparamnum"]."'")
        ->MboRemote("amp.actualvalue")->MboRemote("amp.value")->getMboSet();
        $assetproductivity = $assetProductivitySet->getMbo();
        
        $actualvalue = floatval($assetproductivity->getString("actualvalue"));
        $potentialValue = floatval($assetproductivity->getString("value"));
        $estimatedvalue = floatval($item["estimatedvalue"]);
        $predictedValueYear = $estimatedvalue * $daysinayear;
		
		$zd = new stdClass;
		$zd->profit_parameter_description = $assetparam_description;
		$zd->profit_parameter_actual_value = $actualvalue;
		$zd->profit_parameter_potential_Value = $potentialValue;
		$zd->profit_parameter_daily_estimated_value_after_modification = $estimatedvalue;
		$zd->profit_parameter_measure_unit = $item["measureunit"];
		$zd->profit_parameter_yearly_estimated_value_after_modification = $predictedValueYear;
		
		$profitParams [] = $zd;
		
        echo "<tr>
                <td class='cell-primary'>" . htmlspecialchars($item["assetnum"]) . "</td>
                <td>" . htmlspecialchars($item["assetparamnum"]) . "</td>
                <td>" . htmlspecialchars($assetparam_description) . "</td>
                <td class='cell-numeric'>" . number_format($actualvalue, 2, ',', '.') . "</td>
                <td class='cell-numeric'>" . number_format($estimatedvalue, 2, ',', '.') . "</td>
                <td class='cell-numeric cell-highlight'>" . number_format($predictedValueYear, 2, ',', '.') . "</td>
                <td>" . htmlspecialchars($item["measureunit"]) . "</td>
                <td class='cell-numeric'>" . htmlspecialchars($item["estyear"]) . "</td>
                <td class='cell-numeric'>" . number_format($potentialValue, 2, ',', '.') . "</td>
              </tr>";
        
        $revenueIncrease = ($estimatedvalue - $actualvalue) * $daysinayear;
        $totalRevenueIncrease += $revenueIncrease;
    }
	
	
	$orion->profit_parameters = $profitParams;
	
    echo "</tbody></table>
            </div>
          </div>";

    // Operational Cost Optimization
    echo "<div class='card'>
            <div class='card-header'>
                <h2 class='card-title'>Operational Cost Optimization</h2>
                <p class='card-description'>Cost reduction opportunities through operational efficiency</p>
            </div>
            <div class='card-content'>
                <table class='data-table'>
                    <thead>
                        <tr>
                            <th>Asset</th>
                            <th>Parameter</th>
                            <th>Predicted Daily</th>
                            <th>Predicted Annual</th>
                            <th>Total Annual Cost</th>
                            <th>Unit</th>
                            <th>Est. Year</th>
                            <th>Target Value</th>
                        </tr>
                    </thead>
                    <tbody>";
    
	$costParams = [];
    foreach ($data["opcostsaving"] as $item) {
        $set = new Set();
        $assetparam_description = $set->MboSetRemote("assetparam")->setWhere("assetparamnum='".$item["assetparamnum"]."'")->MboRemote("description")->getMboSet()->getMbo()->getString("description");

        $set = new Set();
        $assetProductivitySet = $set->MboSetRemote("assetmodproductivity amp")
        ->setWhere("amp.assetmodid='$assetmodid' and amp.assetnum = '". $item["assetnum"]."' and amp.assetparamnum='".$item["assetparamnum"]."'")
        ->MboRemote("amp.actualvalue")->MboRemote("amp.value")->getMboSet();
        $assetproductivity = $assetProductivitySet->getMbo();
        
        $actualvalue = floatval($assetproductivity->getString("actualvalue"));
        $potentialValue = floatval($assetproductivity->getString("value"));
        
        $set2 = new Set();
        $unitcost = floatval($set2->MboSetRemote("assetproductivity")->setWhere("assetparamnum='".$item["assetparamnum"]."' and assetnum='".$item["assetnum"]."'")->MboRemote("unitcost")->getMboSet()->getMbo()->getString("unitcost"));
        
        $estimatedvalue = floatval($item["estimatedvalue"]);
        $predictedValueYear = $estimatedvalue * $daysinayear;
        $linecost = $predictedValueYear * $unitcost;
        
		
		$zd = new stdClass; 
		$zd->cost_parameter_description = $assetparam_description;
		$zd->cost_parameter_default_potential = $potentialValue;
		$zd->cost_parameter_daily_estimated_value_after_modification = $estimatedvalue;
		$zd->cost_parameter_yearly_estimated_value_after_modification = $predictedValueYear;
		$zd->cost_parameter_measure_unit_= $item["measureunit"];
		$zd->cost_parameter_annual_cost_after_modification = $linecost;
		$costParams [] = $zd;
        echo "<tr>
                <td class='cell-primary'>" . htmlspecialchars($item["assetnum"]) . "</td>
                <td>" . htmlspecialchars($item["assetparamnum"]) . " - " .htmlspecialchars($assetparam_description)." </td>
                <td class='cell-numeric'>" . number_format($estimatedvalue, 2, ',', '.') . "</td>
                <td class='cell-numeric'>" . number_format($predictedValueYear, 2, ',', '.') . "</td>
                <td>" . htmlspecialchars($item["measureunit"]) . "</td>
                <td class='cell-numeric cell-highlight'>" . number_format($linecost, 2, ',', '.') . "</td>
                <td class='cell-numeric'>" . htmlspecialchars($item["estyear"]) . "</td>
                <td class='cell-numeric'>" . number_format($potentialValue, 2, ',', '.') . "</td>
              </tr>";
        
		
        $costSavings = ($actualvalue - $estimatedvalue) * $unitcost * $daysinayear;
        $totalCostSavings += $costSavings;
    }
	
	$orion->operational_costing_parameter = $costParams;
	
    echo "</tbody></table>
            </div>
          </div>";

    echo "<div class='card'>
            <div class='card-header'>
                <h2 class='card-title'>Maintenance Cost Reduction</h2>
                <p class='card-description'>Expected maintenance cost savings and efficiency improvements</p>
            </div>
            <div class='card-content'>
                <table class='data-table'>
                    <thead>
                        <tr>
                            <th>Asset</th>
                            <th>Current YTD Cost</th>
                            <th>Cost Change</th>
                            <th>Predicted YTD Cost</th>
                            <th>Annual Savings</th>
                            <th>Est. Year</th>
                            <th>Year</th>
                        </tr>
                    </thead>
                    <tbody>";
    
    $totalMaintenanceSavings = 0;
	$maintenance_params = [];
    foreach ($data["maintcostsaving"] as $item) {
        $currentytdcost = floatval(str_replace('.', '', $item["currentytdcost"]));
        $predictedytdcost = floatval(str_replace('.', '', $item["predictedytdcost"]));
        $maintenancesaving = $currentytdcost - $predictedytdcost;
        $zd = new stdClass;
		$zd->current_year_to_date_cost = $currentytdcost;
		$zd->predicted_ytd_cost = $predictedytdcost;
		$zd->maintenance_saving = $maintenancesaving;
		$maintenance_params [] = $zd;
        echo "<tr>
                <td class='cell-primary'>" . htmlspecialchars($item["assetnum"]) . "</td>
                <td class='cell-numeric'>" . number_format($currentytdcost, 2, ',', '.') . "</td>
                <td>" . htmlspecialchars($item["ytdcostchange"]) . "</td>
                <td class='cell-numeric'>" . number_format($predictedytdcost, 2, ',', '.') . "</td>
                <td class='cell-numeric cell-highlight " . ($maintenancesaving > 0 ? 'cell-positive' : 'cell-negative') . "'>" . number_format($maintenancesaving, 2, ',', '.') . "</td>
                <td class='cell-numeric'>" . htmlspecialchars($item["estyear"]) . "</td>
                <td class='cell-numeric'>" . htmlspecialchars($item["year"]) . "</td>
              </tr>";
        
        $totalMaintenanceSavings += $maintenancesaving;
    }
	
	$orion->workorder_maintenance_costing = $maintenance_params;	
	$orion->total_modification_cost = $totalmodificationCost;
	 // Financial KPIs
    $totalAnnualBenefit = $totalRevenueIncrease + $totalCostSavings + $totalMaintenanceSavings;
    $roi = (($totalAnnualBenefit - $totalmodificationCost) / $totalmodificationCost) * 100;
    $paybackPeriodYears = $totalmodificationCost / $totalAnnualBenefit;
	
	
	$orion->total_Maintenance_Savings = $totalMaintenanceSavings;
	$orion->total_Cost_Savings = $totalCostSavings;
	$orion->totalmodificationCost = $totalmodificationCost;
	$orion->total_Annual_Benefit = $totalAnnualBenefit;
	$orion->roi = $roi;
	$orion->paybackPeriodYears = $paybackPeriodYears;
	
	//Posting $orion object to AI Server to get $orion_summary
	
	$endpoint = "/generate/financial-summary";
	$payload = json_encode($orion);

	$narrative_result = Super::sendToML($endpoint, $payload);
	
	echo "</tbody></table>
            </div>
          </div>";
	echo "<div class='card'>
            <div class='card-header'>
                <h2 class='card-title'>ORION Summary and Recommendation</h2>
                <p class='card-description'>
				Operational Readiness Intelligence & Orchestration Network - Return On Investment
				</p>
            </div>
            <div class='card-content'> <div class='card-sub-content'>
			".$narrative_result." </div>
            </div>
		</div>";
    echo "</div>"; // content-primary
	
	//echo json_encode($orion);
	
    
    // Sidebar with KPIs and Summary
    echo "<div class='content-sidebar'>";
    
    // Investment Summary
    echo "<div class='investment-summary'>
            <div class='investment-label'>Total Investment Required</div>
            <div class='investment-amount'>" . number_format($totalmodificationCost, 0, ',', '.') . "</div>
          </div>";
	
    echo "<div class='card'>
            <div class='card-header'>
                <h3 class='card-title'>Key Performance Indicators</h3>
                <p class='card-description'>Financial metrics and projections</p>
            </div>
            <div class='card-content'>
                <div class='kpi-grid'>
                    <div class='kpi-card'>
                        <div class='kpi-value'>" . number_format($totalAnnualBenefit, 0, ',', '.') . "</div>
                        <div class='kpi-label'>Total Annual Benefit</div>
                    </div>
                    <div class='kpi-card'>
                        <div class='kpi-value " . ($roi > 0 ? 'kpi-positive' : 'kpi-negative') . "'>" . number_format($roi, 1, ',', '.') . "%</div>
                        <div class='kpi-label'>Return on Investment</div>
                    </div>
                    <div class='kpi-card'>
                        <div class='kpi-value'>" . 
                        ($paybackPeriodYears < 1 ? 
                            number_format($paybackPeriodYears * 12, 1, ',', '.') . "M" : 
                            number_format($paybackPeriodYears, 1, ',', '.') . "Y") . 
                        "</div>
                        <div class='kpi-label'>Payback Period</div>
                    </div>
                </div>
            </div>
          </div>";

    echo "<div class='card'>
            <div class='card-header'>
                <h3 class='card-title'>Benefit Breakdown</h3>
                <p class='card-description'>Annual benefit components</p>
            </div>
            <div class='card-content'>
                <div class='kpi-grid'>
                    <div class='kpi-card'>
                        <div class='kpi-value kpi-positive'>" . number_format($totalRevenueIncrease, 0, ',', '.') . "</div>
                        <div class='kpi-label'>Revenue Enhancement</div>
                    </div>
                    <div class='kpi-card'>
                        <div class='kpi-value kpi-positive'>" . number_format($totalCostSavings, 0, ',', '.') . "</div>
                        <div class='kpi-label'>Operational Savings</div>
                    </div>
                    <div class='kpi-card'>
                        <div class='kpi-value " . ($totalMaintenanceSavings > 0 ? 'kpi-positive' : 'kpi-negative') . "'>" . number_format($totalMaintenanceSavings, 0, ',', '.') . "</div>
                        <div class='kpi-label'>Maintenance Savings</div>
                    </div>
                </div>
            </div>
          </div>";

    $roiStatus = $roi > 15 ? 'positive' : ($roi > 0 ? 'neutral' : 'negative');
    $paybackStatus = $paybackPeriodYears < 2 ? 'positive' : ($paybackPeriodYears < 5 ? 'neutral' : 'negative');
    
    echo "<div class='card'>
            <div class='card-header'>
                <h3 class='card-title'>Investment Assessment</h3>
                <p class='card-description'>Project viability indicators</p>
            </div>
            <div class='card-content'>
                <div style='display: flex; flex-direction: column; gap: var(--spacing-md);'>
                    <div>
                        <div class='overview-label'>ROI Performance</div>
                        <span class='status-indicator status-$roiStatus'>
                            " . ($roi > 15 ? '● Excellent' : ($roi > 0 ? '● Good' : '● Poor')) . "
                        </span>
                    </div>
                    <div>
                        <div class='overview-label'>Payback Timeline</div>
                        <span class='status-indicator status-$paybackStatus'>
                            " . ($paybackPeriodYears < 2 ? '● Fast' : ($paybackPeriodYears < 5 ? '● Moderate' : '● Slow')) . "
                        </span>
                    </div>
                    <div>
                        <div class='overview-label'>Risk Assessment</div>
                        <span class='status-indicator status-neutral'>
                            ● Standard
                        </span>
                    </div>
                </div>
            </div>
          </div>";

    // Recommendation Summary
    $recommendation = $roi > 15 && $paybackPeriodYears < 3 ? 'Highly Recommended' : 
                     ($roi > 0 && $paybackPeriodYears < 5 ? 'Recommended' : 'Requires Review');
    $recommendationClass = $roi > 15 && $paybackPeriodYears < 3 ? 'positive' : 
                          ($roi > 0 && $paybackPeriodYears < 5 ? 'neutral' : 'negative');

    echo "<div class='card'>
            <div class='card-header'>
                <h3 class='card-title'>Executive Summary</h3>
                <p class='card-description'>Investment recommendation</p>
            </div>
            <div class='card-content'>
                <div style='text-align: center; padding: var(--spacing-lg) 0;'>
                    <div class='kpi-value' style='font-size: 18px; margin-bottom: var(--spacing-md);'>$recommendation</div>
                    <span class='status-indicator status-$recommendationClass' style='font-size: 14px;'>
                        " . ($roi > 15 && $paybackPeriodYears < 3 ? '🟢 Strong Business Case' : 
                             ($roi > 0 && $paybackPeriodYears < 5 ? '🟡 Positive ROI' : '🔴 Requires Analysis')) . "
                    </span>
                </div>
                <div style='margin-top: var(--spacing-lg); font-size: 12px; color: var(--text-helper); text-align: center; line-height: 1.4;'>
                    This analysis is based on projected values and current operational data. 
                    Actual results may vary depending on market conditions and implementation effectiveness.
                </div>
            </div>
          </div>";

    echo "</div>"; // content-sidebar
    echo "</main>"; // main-content

    // Footer
    echo "<footer style='padding: var(--spacing-lg) var(--spacing-xl); border-top: 1px solid var(--neutral-gray-200); background: var(--background-subtle);'>
            <div style='display: flex; justify-content: space-between; align-items: center; font-size: 12px; color: var(--text-helper);'>
                <div>© " . date('Y') . " Asset Management System - ROI Analysis Report</div>
                <div>Generated on " . date('F j, Y \a\t g:i A') . "</div>
            </div>
          </footer>";
    
    echo "</div>"; // enterprise-container
	
	
	
}


public static function sumModificationCost(){
	$assetnum = Super::getParam("sumModificationCost");
	
	$set = new Set();
	$total_actualcost = $set->MboSetRemote("workorder")->setWhere("assetnum='$assetnum'")->MboRemote("sum(actualcost) as total_actualcost")->getMboSet()->getMbo()->getString("total_actualcost");
	$set = new Set();
	$set->MboSetRemote("assets")->setWhere("assetnum='$assetnum'")->setValue("ytdcost", $total_actualcost)->change();
	
	
	
	$set = new Set();
	$assets = $set->MboSetRemote("assetmod")->setWhere("assetnum='$assetnum'")->MboRemote("assetmodid")->getMboSet();
	$counts = $assets->counts();
	if($counts == 0){
		Set::throws(["msg"=>"performance_modification_notfound", "ytdcost"=>$total_actualcost]);
		return;
	}
	
	for($i=0; $i<$counts; $i++){
		$asset = $assets->getMbo();
		$assetmodid = $asset->getString("assetmodid");
		$set2 = new Set();
		
		$set2 = new Set();
		$totallinecost = $set2->MboSetRemote("assetmodline")->setWhere("assetmodid='$assetmodid'")->MboRemote("sum(linecost) as totallinecost")->getMboSet()->getMbo()->getString("totallinecost");
		$set3 = new Set();
		
		$set3->MboSetRemote("assetmod")->setWhere("assetmodid='$assetmodid'")->setValue("totalcost", $totallinecost)->change();
	}
	
	Set::throws(["msg"=>"success", "ytdcost"=>$total_actualcost]);
	
}

public static function addDowntimePrediction(){
  $assetmodid = Super::getParam("addDowntimePrediction");
  $assetnum = Super::getParam("assetnum");
  $downtimestartdate = Super::getParam("downtimestartdate");
  $downtimeenddate = Super::getParam("downtimeenddate");

  if (strtotime($downtimestartdate) > strtotime($downtimeenddate)) {
    Set::throws(["msg" => "Downtime start date cannot be after downtime end date."]);
    return; 
  }

  $timestamp = strtotime($downtimestartdate);
  $year = date("Y", $timestamp);

  $set = new Set();
  $assetDowntimeSet = $set->MboSetRemote("assetdowntimehistory")
                      ->setWhere("assetnum='$assetnum' and downtimestart>='$downtimestartdate' and downtimeend<='$downtimeenddate'")
                      ->group("assetnum, failurecode")
                      ->MboRemote("assetnum")
                      ->MboRemote("durationminutes")
                      ->MboRemote("failurecode")
                      ->getMboSet();

  $counts = $assetDowntimeSet->counts();
  if($counts >0){
    $setd = new Set();
    $setd->MboSetRemote("assetmoddowntime")
        ->setWhere("assetmodid='$assetmodid' and assetnum='$assetnum'")
        ->remove();

    for($i=0; $i<$counts; $i++){
      $ad = $assetDowntimeSet->getMbo();
      $assetnum = $ad->getString("assetnum");
      $histdurationmin = $ad->getString("durationminutes");
      $failurecode = $ad->getString("failurecode");

      $set2 = new Set();
      $set2->MboSetRemote("assetmoddowntime")
          ->setValue("assetmodid", $assetmodid)
          ->setValue("assetnum", $assetnum)
          ->setValue("failurecode", $failurecode)
          ->setValue("histdurationmin", $histdurationmin)
          ->setValue("year", $year)
          ->add();
    }
  }
  Set::throws(["msg"=>"success"]);
}
public static function addMeterPrediction(){
	$assetmodid = Super::getParam("addMeterPrediction");
	$assetnum = Super::getParam("assetnum");
	
	$set = new Set();
	$assetSet = $set->MboSetRemote("assetmeter")->setWhere("assetnum='$assetnum'")
	->MboRemote("meternum")->MboRemote("assetnum")->MboRemote("unit")
	->getMboSet();
	
	$counts = $assetSet->counts();
	for($i=0; $i<$counts; $i++){
		$asset = $assetSet->getMbo();
		$meternum = $asset->getString("meternum");
		$unit = $asset->getString("unit");
		$set2 = new Set();
		$assetModMeterSet = $set2->MboSetRemote("assetmodmeter")->setWhere("assetnum='$assetnum' and meternum='$meternum' and assetmodid='$assetmodid'")->getMboSet();
		if($assetModMeterSet->isEmpty()){
			$set3 = new Set();
			$set3->MboSetRemote("assetmodmeter")
			->setValue("meternum", $meternum)
			->setValue("assetnum", $assetnum)
			->setValue("measureunit", $unit)
			->setValue("assetmodid", $assetmodid)
			->add();
		}
	}
	
	Set::throws(["msg"=>"success"]);
}	
public static function addProductivityPrediction(){
	$assetmodid = Super::getParam("addProductivityPrediction");
	$assetnum = Super::getParam("assetnum");
	
	$set = new Set();
	$assetSet = $set->MboSetRemote("assetproductivity ap")
	->setRelationship("assetparam am", "ap.assetparamnum=am.assetparamnum")
	->setWhere("ap.assetnum='$assetnum'")
	->MboRemote("ap.assetparamnum")->MboRemote("ap.value")->MboRemote("ap.actualvalue")->MboRemote("am.measureunit")
	->getMboSet();
	
	$counts = $assetSet->counts();
	for($i=0; $i<$counts; $i++){
		$asset = $assetSet->getMbo();
		$assetparamnum = $asset->getString("assetparamnum");
		$value = $asset->getString("value");
		$actualvalue = $asset->getString("actualvalue");
		$measureunit = $asset->getString("measureunit");
		$set2 = new Set();
		$assetProductivitySet = $set2->MboSetRemote("assetmodproductivity")->setWhere("assetnum='$assetnum' and assetparamnum='$assetparamnum' and assetmodid='$assetmodid'")->getMboSet();
		if($assetProductivitySet->isEmpty()){
			$set3 = new Set();
			$set3->MboSetRemote("assetmodproductivity")
			->setValue("assetparamnum", $assetparamnum)
			->setValue("assetnum", $assetnum)
			->setValue("value", $value)
			->setValue("actualvalue", $actualvalue)
			->setValue("assetmodid", $assetmodid)
			->setValue("year", date("Y"))
			->setValue("measureunit", $measureunit)
			->add();
		}
	}
	
	Set::throws(["msg"=>"success"]);
	
	
}
public static function reportDowntime() {

    $assetnum    = trim(Super::getParam("reportDowntime")); //Asset Number
    $assetstate  = strtolower(trim(Super::getParam("assetstate"))); //"up" atau "down"
    $failurecode = trim(Super::getParam("failurecode")); //Kode penyebab Downtime
    $wonum = trim(Super::getParam("wonum"));       //Nomor Work Order (jika ada)
    $comments    = trim(Super::getParam("comments"));       //Komentar
    $reportdate  = Super::getParam("reportdate");       //Tanggal dan waktu pelaporan
    $enterby    = Xbo::getLoginUser();   // user yg lapor
    $enterdate = Xbo::setenterdate();

    if (empty($assetnum) || empty($assetstate) || !in_array($assetstate, ['up', 'down']) || empty($reportdate)) {
        Set::throws(["msg" => "Asset Number, Asset State (up/down), and Report Date are required."]);
        return; 
    }

    // 2. Validasi Tanggal (Tambahan)
    $validationError = self::validateReportDate($assetnum, $reportdate);
    if ($validationError) {
        Set::throws(["msg" => $validationError]);
        return;
    }

    // 3. Definisikan Status Aset (Berbasis Keadaan "UP" atau "DOWN")
    $newAssetStatus = ($assetstate == "down") ? "shutdown" : "operating";

    // 4. Periksa Status Aset TERKINI dari Database
    $set = new Set();
    $assetsSet = $set->MboSetRemote("assets")
                    ->setWhere("assetnum = '$assetnum'")
                    ->MboRemote("status")
                    ->MboRemote("statusdate")
                    ->getMboSet();

    $asset          = $assetsSet->getMbo();
    $currentStatus  = $asset->getString("status");
    $statusDate     = $asset->getString("statusdate");
	
    // 5. Logika Utama: Apakah Aset Sudah Dalam Keadaan yang Sama?
    if (($assetstate == "down" && $currentStatus == "shutdown") || ($assetstate == "up" && $currentStatus != "shutdown")) {
        // Kasus 1: Aset SUDAH shutdown, dilaporkan shutdown LAGI
        // Kasus 2: Aset SUDAH operating, dilaporkan operating LAGI
        Set::throws(["msg" => "Asset is already in the reported state."]);
        return;
    }

    // 6. Logika: Catat Event ke assetdowntime dan Update Status Aset (JIKA perlu)
    if ($assetstate == "down") { // Aset berubah menjadi DOWN
        $isdown = "Y";
        // a. Catat Event ke tabel `assetdowntime`
        $set = new Set();
        $set->MboSetRemote("assetdowntime")
            ->setValue("assetnum",      $assetnum)
            ->setValue("assetstate",    $assetstate)
            ->setValue("reportdate",    $reportdate)
            ->setValue("enterby",       $enterby)
            ->setValue("enterdate",     $enterdate)
            ->setValue("failurecode",   $failurecode)
            ->setValue("wonum",         $wonum)
            ->setValue("comments",      $comments)
            ->add();

        // b. Update Status Aset di tabel `assets` (JIKA Berubah)
        if ($currentStatus != "shutdown") {
            $set = new Set();
            $set->MboSetRemote("assets")->setWhere("assetnum = '$assetnum'")
                ->setValue("status",     $newAssetStatus)
                ->setValue("statusdate", $reportdate)
                ->setValue("isdown",     $isdown)
                ->change();
        }

    } else { // $assetstate == "up" Aset berubah menjadi UP (Logika yang Berubah)
        $isdown = "N";
        $set = new Set();
        // **A. Cari Periode Downtime Terakhir (Jika Ada)**
        $downtimeSet = $set->MboSetRemote("assetdowntime")
        ->setWhere("assetnum = '$assetnum' AND assetstate = 'down'")
        ->MboRemote("max(reportdate) as reportdate")
        ->MboRemote("failurecode")
        ->MboRemote("assetdowntimeid")
        ->order("reportdate DESC") //Terakhir
        ->getMboSet();

        if ($downtimeSet && $downtimeSet->counts() > 0) {

            $lastDowntime = $downtimeSet->getMbo();
            $downtimeStart = $lastDowntime->getString("reportdate");
            $assetdowntimeid =  $lastDowntime->getString("assetdowntimeid");
          

            // **B. Hitung Durasi Downtime**
            $downtimeStartObj = new DateTime($downtimeStart);
            $reportDateObj    = new DateTime($reportdate);

            $interval         = $downtimeStartObj->diff($reportDateObj);
            $durationMinutes  = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;

            // C. Simpan ke `assetdowntimehistory`
            $set = new Set();
            $set->MboSetRemote("assetdowntimehistory")
                ->setValue("assetnum",       $assetnum)
                ->setValue("downtimestart",  $downtimeStart)
                ->setValue("downtimeend",    $reportdate)
                ->setValue("durationminutes", $durationMinutes)
                ->setValue("failurecode",    $lastDowntime->getString("failurecode"))
                ->setValue("wonum",          $wonum)
                ->add();

            /* D. Hapus Record Downtime Saat Ini (assetdowntime)
			$set = new Set();
            $set->MboSetRemote("assetdowntime")
                ->setWhere("assetdowntimeid = ".$assetdowntimeid)
                ->delete();
			*/
            // E. Perbarui `assetproductivity.actualvalue` 
            self::updateActualProductivity($assetnum, $reportdate);
        }

        //a. Catat Event ke tabel `assetdowntime`
        $set = new Set();
        $set->MboSetRemote("assetdowntime")
            ->setValue("assetnum",      $assetnum)
            ->setValue("reportdate", $reportdate)
            ->setValue("assetstate",    $assetstate) // "up"
            ->setValue("failurecode",      null)
            ->setValue("wonum",  $wonum)
            ->setValue("enterby",    $enterby)
            ->setValue("enterdate",  $enterdate)
            ->setValue("comments",      $comments)
            ->add();

        // b. Update Status Aset di tabel `assets` (JIKA Berubah)
        if ($currentStatus == "shutdown") {
            $set = new Set();
            $set->MboSetRemote("assets")->setWhere("assetnum = '$assetnum'")
                ->setValue("status",     $newAssetStatus) // "operating"
                ->setValue("statusdate", $reportdate)
                ->setValue("isdown",     $isdown)
                ->change();
        }
    }
    Set::throws(["msg" => "Asset has been change to ".$newAssetStatus]);
}

	public static function recalculateproductivity(){
		$assetnum = Super::getParam("recalculateproductivity");
		$now = new DateTime(); 
		$nowFormatted = $now->format('Y-m-d H:i:s'); 
		self::updateActualProductivity($assetnum, $nowFormatted);
		$msg = "updated";
		Set::throws(["msg"=>$msg]);
	}

	private static function updateActualProductivity($assetNum, $reportDate) {

		$set = new Set();

		$productivitySet = $set->MboSetRemote("assetproductivity")->MboRemote("assetparamnum")->MboRemote("value")
			->where("assetnum = ?", $assetNum)
			->getMboSet();

		$updatesToPerform = [];

		while ($row = $productivitySet->fetch()) {
			$assetparamnum = $row['assetparamnum'];
			$potentialValue = $row['value'];
			
			$actualValue = self::calculateActualValue($assetNum, $assetparamnum, $potentialValue, $reportDate);
			
			$updatesToPerform[] = [
				'assetparamnum' => $assetparamnum,
				'actualvalue' => $actualValue
			];
		}

		$tm = TransactionManager::getInstance();
		try {
			$tm->beginTransaction();
			foreach ($updatesToPerform as $update) {
				$setUpdate = new Set();
				$setUpdate->useTransactionManager() 
					->MboSetRemote("assetproductivity")
					->where("assetnum = ? AND assetparamnum = ?", $assetNum, $update['assetparamnum'])
					->setValue("actualvalue", $update['actualvalue'])
					->change(); 
			}
			
			$tm->commit();
			
		} catch (Exception $e) {
			$tm->rollback();
			
			error_log("Failed to update asset productivity: " . $e->getMessage());
			throw $e; 
		}
	}


	private static function calculateActualValue($assetNum, $assetparamnum, $potentialValue, $reportDate) {
		
		// 1. Tentukan Tanggal Mulai (Productivity Date atau Install Date)
		$startDate = self::getProductivityStartDate($assetNum);
		// 2. Hitung Total Waktu yang Tersedia (dalam Menit)
		$startDateObj = new DateTime($startDate);
		//$reportDateObj = new DateTime($reportDate);
		$reportDateObj = new DateTime('now');
		
		$interval = $startDateObj->diff($reportDateObj);
		$totalAvailableMinutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;
		
		// 3. Hitung Total Downtime (dalam Menit)
		$totalDowntime = self::getTotalDowntime($assetNum, $startDate, $reportDate);
		
		// 4. Hitung Waktu Operasi Efektif
		$effectiveMinutes = $totalAvailableMinutes - $totalDowntime;
		
		// 5. Hitung Rasio Downtime
		$downtimeRatio = ($totalAvailableMinutes > 0) ? ($totalDowntime / $totalAvailableMinutes) : 0; //Hindari pembagian dengan nol
		
		// 6. Hitung Actual Value
		$actual_value = $potentialValue * (1 - $downtimeRatio);
		
		return $actual_value;
	}

	// Fungsi untuk Mendapatkan Productivity Start Date (dari Assets.productivitydate atau Assets.installdate)
	private static function getProductivityStartDate($assetNum) {
		$set = new Set();
		$assetSet = $set->MboSetRemote("assets")
			->setWhere("assetnum = '$assetNum'")
			->MboRemote("productivitydate")
			->MboRemote("installdate")
			->getMboSet();

		$asset = $assetSet->getMbo();
		$productivityDate = $asset->getString("productivitydate");
		$installDate      = $asset->getString("installdate");

		if ($productivityDate) {
			return $productivityDate;
		} else {
			return $installDate;
		}
	}

	// Fungsi untuk Mendapatkan Total Downtime (dalam Menit)
	private static function getTotalDowntime($assetNum, $startDate, $endDate) {
		$set = new Set();
		$downtimeSet = $set->MboSetRemote("assetdowntimehistory")
			->setWhere("assetnum = '$assetNum' AND downtimeStart >= '$startDate' AND downtimeEnd <= '$endDate'")
			->MboRemote("sum(durationminutes) as totalDowntime")
			->getMboSet();

		$downtime = $downtimeSet->getMbo();
		return $downtime ? $downtime->getString("totalDowntime") : 0; //handle nilai null
	}

	// Fungsi Validasi Tanggal (Tambahan)
	private static function validateReportDate($assetNum, $reportDate) {
		// 1. Tanggal Tidak Boleh di Masa Depan
		if ($reportDate > date('Y-m-d H:i:s')) {
			return "Report Date cannot be in the future.";
		}

		$set = new Set();

		// 2. Ambil Tanggal Downtime Terawal
		$minDowntimeStartSet = $set->MboSetRemote("assetdowntimehistory")
			->setWhere("assetnum = '$assetNum'")
			->MboRemote("min(downtimestart) as minDowntimeStart") // Alias untuk kolom
			->getMboSet();

		$minDowntimeStartMbo = $minDowntimeStartSet->getMbo();
		$minDowntimeStart    = $minDowntimeStartMbo ? $minDowntimeStartMbo->getString("minDowntimeStart") : null; //handle jika kosong
		
		// 3. Ambil Tanggal Uptime Terakhir
		$set = new Set();
		$maxDowntimeEndSet = $set->MboSetRemote("assetdowntimehistory")
			->setWhere("assetnum = '$assetNum'")
			->MboRemote("max(downtimeend) as maxDowntimeEnd") // Alias untuk kolom
			->getMboSet();

		$maxDowntimeEndMbo = $maxDowntimeEndSet->getMbo();
		$maxDowntimeEnd    = $maxDowntimeEndMbo ? $maxDowntimeEndMbo->getString("maxDowntimeEnd") : null; //handle jika kosong

		// 4. Validasi terhadap Rentang Waktu di Histori (JIKA ada data di histori)
		if ($minDowntimeStart && $maxDowntimeEnd) {
			if ($reportDate > $minDowntimeStart && $reportDate < $maxDowntimeEnd) {
				return "Report Date overlaps with an existing downtime period.";
			}
		}

		return null; // Validasi Sukses
	}


	public static function getFailureCodeOptions() {
		$set = new Set();
		$failureCodesSet = $set->MboSetRemote("assetfailure")->getMboSet()->order("category, failurecode asc"); // Dapatkan semua record

		$optionsHTML = '';
		if (!$failureCodesSet->isEmpty()) {
			$counts = $failureCodesSet->counts();
			for($i=0; $i<$counts; $i++){
				$failureCodes = $failureCodesSet->getMbo();
				$failurecode = $failureCodes->getString("failurecode");
				$description = $failureCodes->getString("description");
				$category = $failureCodes->getString("category");

				// Gabungkan Failurecode, Description, dan Category
				$displayValue = htmlspecialchars("$category - $failurecode - $description", ENT_QUOTES, 'UTF-8');

				$optionsHTML .= "<option value='" . htmlspecialchars($failurecode, ENT_QUOTES, 'UTF-8') . "'>" . $displayValue . "</option>";
			}
		} else {
			$optionsHTML = "<option value=''>No Failure Codes Found</option>";
		}

		Set::throws(["options"=> $optionsHTML]);
	}
	public static function enterparamproductivity(){
		$assetnum = Super::getParam("enterparamproductivity");
		$paramvalues = Super::getParam("paramvalues");
		$changedtable = "assetproductivity";
		$changeby = Xbo::getLoginUser();
		$changedate = Xbo::setenterdate();
		for($i=0; $i<count($paramvalues); $i++){
			$assetparamnum = $paramvalues[$i]["assetparamnum"];
			$value = floatval($paramvalues[$i]["value"]);
			$unitcost = floatval($paramvalues[$i]["unitcost"]);
			
			$where = "assetnum='$assetnum' and assetparamnum='$assetparamnum'";
			$set = new Set();
			$oldValue = $set->MboSetRemote($changedtable)->setWhere($where)->MboRemote("value")->getMboSet()->getMbo()->getString("value");
			
			$set = new Set();
			$set->MboSetRemote($changedtable)->setWhere($where)
			->setValue("value", $value)
			->setValue("unitcost", $unitcost)
			->setValue("changeby", $changeby)
			->setValue("changedate", $changedate)
			->change();
			$variance = floatval($value)-floatval($oldValue);
			
			$set2 = new Set();
			$set2->MboSetRemote("assetprodhistory")
			->setValue("assetnum", $assetnum)
			->setValue("assetparamnum", $assetparamnum)
			->setValue("value", $value)
			->setValue("unitcost", $unitcost)
			->setValue("variance", $variance)
			->setValue("changeby", $changeby)
			->setValue("changedate", $changedate)
			->add();
		}
		
		
		$msg = "ok";
		Set::throws(["msg"=>$msg]);
	}
	public static function addparamsForAsset(){
		$assetnum = Super::getParam("addparamsForAsset");
		$addparams = Super::getParam("addparams");
		$insert_table = "assetproductivity";
		
		for($i=0; $i<count($addparams); $i++){
			$assetparamnum = $addparams[$i]["assetparamnum"];
			$set = new Set();
			$where = "assetnum='$assetnum' and assetparamnum='$assetparamnum'";
			$assetproductivitySet = $set->MboSetRemote($insert_table)->setWhere($where)->getMboSet();
			if($assetproductivitySet->isEmpty()){
				$set2 = new Set();
				$set2->MboSetRemote($insert_table)
				->setValue("assetnum", $assetnum)
				->setValue("assetparamnum", $assetparamnum)
				->setValue("value", 0)
				->setValue("enterby",Xbo::getLoginUser())
				->setValue("enterdate", Xbo::setenterdate())
				->add();
			}
		}
		
		$msg = "ok";
		Set::throws(["msg"=>$msg]);
	}
	public static function addAssetParam(){
		$assetparamnum = strtoupper(Super::getParam("addAssetParam"));
		$assetparamdescription = Super::getParam("assetparamdescription");
		$datatype = Super::getParam("datatype");
		$sortorder = Super::getParam("sortorder");
		$isactive = Super::getParam("isactive");
		$msg = "";
		$set = new Set();
		$assetParamSet = $set->MboSetRemote("assetparam")->setWhere("assetparamnum='$assetparamnum'")->getMboSet();
		if($assetParamSet->isEmpty()){
			$set = new Set();
			$set->MboSetRemote("assetparam")
			->setValue("assetparamnum", $assetparamnum)
			->setValue("description", $assetparamdescription)
			->setValue("datatype", $datatype)
			->setValue("isactive", $isactive)
			->setValue("sortorder", $sortorder)
			->add();
			$msg = "Asset Production Parameter has been added.";
		}else{
			$msg = "Asset Production Parameter has already exist.";
		}
		Set::throws([
		"msg"=>$msg,
		"assetparamnum"=>$assetparamnum,
		"assetparamdescription"=>$assetparamdescription
		]);
	}
	public static function MTTRChart(){
		$assetnum = $_POST['MTTRChart'];
		$tahun = $_POST['currentyear'] ?? date('Y');

		$bulanLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
		$mttrData = array_fill(0, 12, '-');
		$tooltips = array_fill(0, 12, '');

		$set = new Set();

		$woSet = $set->MboSetRemote("workorder")
			->where(
				"assetnum = ? AND status = ? AND actstartdate IS NOT NULL AND actfinishdate IS NOT NULL AND YEAR(actstartdate) = ?", 
				$assetnum, 
				'completed', 
				$tahun
			)
			->order("actstartdate")
			->MboRemote("wonum, actstartdate, actfinishdate") 
			->getMboSet();

		$monthly = array_fill(0, 12, ['total' => 0, 'count' => 0, 'tooltip' => []]);

		while ($row = $woSet->fetch()) {
			$actStart = $row['actstartdate'];
			$actFinish = $row['actfinishdate'];

			try {
				$startDate = new DateTime($actStart);
				$endDate = new DateTime($actFinish);
			} catch (Exception $e) {
				error_log("Invalid date in WO {$row['wonum']}: " . $e->getMessage());
				continue;
			}
			
			if ($startDate > $endDate) {
				continue;
			}
			
			$bulan = (int)$startDate->format('n') - 1;
			
			$duration = ($endDate->getTimestamp() - $startDate->getTimestamp()) / 3600;

			$monthly[$bulan]['total'] += $duration;
			$monthly[$bulan]['count']++;
			$monthly[$bulan]['tooltip'][] = "WO#: " . $row['wonum'] . 
				" (" . $startDate->format('d M') . " - " . $endDate->format('d M') . ") = " . round($duration, 2) . " hour";
		}

		$mttrData = array_fill(0, 12, 0); 
		$tooltips = array_fill(0, 12, '');

		foreach ($monthly as $bulan => $data) {
			if ($data['count'] > 0) {
				$avg = $data['total'] / $data['count'];
				$mttrData[$bulan] = round($avg, 2);
				$tooltips[$bulan] = implode('<br>', $data['tooltip']);
			}
		}

		$output = [
			'labels' => $bulanLabels,
			'series' => [[
				'name' => 'MTTR (hours)',
				'type' => 'line',
				'data' => $mttrData,
				'areaStyle' => new stdClass(), // area style aktif
				'smooth' => true,
				'symbol' => 'circle',
				'itemStyle' => ['color' => '#FF7F50'],
				'label' => [
					'show' => true,
					'position' => 'top',
					'formatter' => '{c}'
				]
			]],
			'tooltips' => $tooltips
		];

		Set::throws($output);
	}
	
	
	public static function MTBFChart(){
		$assetnum = Super::getParam("MTBFChart");
		$set = new Set();


		$woSet = $set->MboSetRemote("workorder wo")
			// 2. Ganti `setWhere` dengan `where` yang aman
			->where("wo.assetnum = ? AND wo.status = ? AND wo.actstartdate IS NOT NULL AND wo.actfinishdate IS NOT NULL", $assetnum, 'completed')
			->order("wo.actstartdate") 
			->MboRemote("wo.wonum, wo.actstartdate, wo.actfinishdate") 
			->getMboSet();

		$monthlyData = array_fill(1, 12, ['total' => 0, 'count' => 0, 'tooltips' => []]);

		$prevFinish = null;
		$prevWonum = null;

		while ($row = $woSet->fetch()) {
			$wonum = $row['wonum'];
			try {
				$startDate = new DateTime($row['actstartdate']);
				$finishDate = new DateTime($row['actfinishdate']);
			} catch (Exception $e) {
				error_log("Invalid date format for WO {$wonum}: " . $e->getMessage());
				continue;
			}
			
			$month = (int)$startDate->format("n");

			if ($prevFinish !== null) {
				$intervalHours = round(($startDate->getTimestamp() - $prevFinish->getTimestamp()) / 3600, 2);

				if ($intervalHours > 0) {
					$monthlyData[$month]['total'] += $intervalHours;
					$monthlyData[$month]['count']++;
					$tooltip = "Failure on WO: ($wonum) occured $intervalHours hours <br> after the previous failure on WO: ($prevWonum) was resolved.";
					$monthlyData[$month]['tooltips'][] = $tooltip;
				}
			}

			$prevFinish = $finishDate;
			$prevWonum = $wonum;
		}

		$labels = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
		$data = [];
		$tooltips = [];

		for ($i = 1; $i <= 12; $i++) {
			$count = $monthlyData[$i]['count'];
			$total = $monthlyData[$i]['total'];
			$avg = ($count > 0) ? round($total / $count, 2) : null;

			$data[] = $avg;
			$tooltipText = ($count > 0) ? implode('<br>', $monthlyData[$i]['tooltips']) : '';
			$tooltips[] = $tooltipText;
		}

		$result = [
			"labels" => $labels,
			"series" => [[
				"name" => "MTBF",
				"type" => "line", // atau "bar" kalau tetap ingin bentuk batang
				"data" => array_map(function($value, $tooltip) {
					return ['value' => $value, 'tooltip' => $tooltip];
				}, $data, $tooltips)
			]]
		];

		Set::throws($result);
	}
	public static function meterChart(){
		$assetnum = $_POST['meterChart']; // nama asset dari user
		$startDate = $_POST['startdate'] ?? date('Y-01-01');
		$endDate = $_POST['enddate'] ?? date('Y-m-d');

		$set = new Set();
		$meterSet = $set->MboSetRemote("assetmeterhistory am")
			->setRelationship("meter m", "am.meternum = m.meternum")
			->where("am.assetnum = ? AND m.metertype = ? AND am.readingdate BETWEEN ? AND ?", $assetnum, 'GAUGE', $startDate, $endDate)
			->order("am.meternum, am.readingdate") 
			->MboRemote("am.meternum, am.value, am.readingdate")
			->getMboSet();

		$seriesData = [];
		$allDates = []; 

		while ($row = $meterSet->fetch()) {
			$meter = $row['meternum'];
			$value = (float)$row['value'];
			$date = substr($row['readingdate'], 0, 10); 
			$allDates[] = $date;
			$seriesData[$meter][$date] = $value;
		}

		$dateLabels = array_unique($allDates);
		 
		sort($dateLabels);

		$series = [];
		foreach ($seriesData as $meter => $data) {
			$chartData = [];
			foreach ($dateLabels as $tgl) {
				$chartData[] = $data[$tgl] ?? null; 
			}

			$series[] = [
				"name" => $meter,
				"type" => "line",
				"data" => $chartData
			];
		}

		$output = [
			"labels" => $dateLabels,
			"series" => $series
		];

		Set::throws($output);
	}
	public static function SeatConfig(){
		$assetnum = $_POST["seatconfig"];
		$obj = new stdClass();
		
		$set = new Set();
		$assetseatconfigSet = $set->MboSetRemote("assetseatconfig")->setWhere("assetnum='$assetnum'")->order("sort")
		->MboRemote("map")->MboRemote("sort")->getMboSet();
		
		$cnt = $assetseatconfigSet->counts();
		
		$mapr = [];
		$seat_temp = [];	
		
		for($i=0; $i<$cnt; $i++){
			$assetseatconfig = $assetseatconfigSet->getMbo();
			$map = $assetseatconfig->getString("map");
			
			
			array_push($mapr, $map);
			$map_temp = str_split($map);
			$cntmaptemp = count($map_temp);
			for($j=0; $j<$cntmaptemp; $j++){
				$map_letter = $map_temp[$j];
				if($map_letter == "_"){
				}else{
					array_push($seat_temp, $map_letter);
				}
				
			}
			
			
		}
		$seat_temp = array_unique($seat_temp);
		$seat_temp = array_values($seat_temp);
		$seats = new stdClass();
		$legendobj = new stdClass();
		$legendobj->items = [];
		$seatmsg = "ok";
		for($i=0; $i<count($seat_temp); $i++){
			$legend = [];
			$prop =  $seat_temp[$i];	
			$set = new Set();
			$assetseatSet = $set->MboSetRemote("assetseat")->setWhere("seatcode='$prop'")->MboRemote("seattype")->MboRemote("description")->MboRemote("class")->getMboSet();
			$assetseatSetCount = $assetseatSet->counts();
			$assetseat = $assetseatSet->getMbo();
			$seats->{$prop} = new stdClass();
			if($assetseatSetCount == 0){
				$seatmsg = "notok";
				$seats->seattype = $prop;
				$seats->{$prop}->classes = null;
				$seats->{$prop}->category = null;
				array_push($legendobj->items, null);
				break;
			}else{
				$seatmsg = "ok";
				$seats->seattype = $prop;
				$seattype = $assetseat->getString("seattype");
				$description = $assetseat->getString("description");
				$class = $assetseat->getString("class");
				
				$seats->{$prop}->classes = $class;
				$seats->{$prop}->category = $seattype;
				
				array_push($legend, $prop);
				array_push($legend, "available");
				array_push($legend, $description);
				
				array_push($legendobj->items, $legend);
			}
		}
		$seatobj = new stdClass();
		$namingobj = new stdClass();
		
		$obj->seatmsg = $seatmsg;
		$obj->map = $mapr;
		$obj->seats = $seats;
		$obj->legend = $legendobj;
		
		Set::throws($obj);
	}
	public static function ScanRFIDAsset(){
		$rfidsn = $_POST["scanrfidasset"];
		$antenna = $_POST["antenna"];
		$enterby = Xbo::getLoginUser();
		$enterdate = Xbo::setenterdate();
		$changeby = $enterby;
		$changedate = $changedate;
		
		$set = new Set();
		
		$locationSet = $set->MboSetRemote("locantenna")->setWhere("antenna = '$antenna'")->MboRemote("locnum as newlocnum")->getMboSet();
		$locations = $locationSet->getMbo();
		$newlocnum = $locations->getString("newlocnum");
		
		$assetSet = $set->MboSetRemote("assets")->setWhere("rfidsn='$rfidsn'")->MboRemote("assetnum")->MboRemote("locnum as currentlocnum")->getMboSet();
		$asset = $assetSet->getMbo();
		
		$currentlocnum = $asset->getString("currentlocnum");
		$assetnum = $asset->getString("assetnum");
		
		if($currentlocnum != $newlocnum) {
			$set->MboSetRemote("assets")->setWhere("rfidsn = '$rfidsn' and assetnum = '$assetnum'")->setValue("locnum", $newlocnum)->change();
			$set->MboSetRemote("assettrans")->setValue("assetnum", $assetnum )->setValue("fromlocation", $currentlocnum )->setValue("tolocation",$newlocnum )
			->setValue("enterdate",$enterdate )->setValue("enterby",$enterby )->setValue("changeby",$changeby )->setValue("changedate",$changedate )->add();
			
			Set::throws(["msg"=>"assetmoved"]);
		}else{
			Set::throws(["msg"=>"scanned"]);
		}
		
	}
	public static function updateMeterHealth($assetnum, $meternum, $new_reading) {
		
		$configSet = (new Set())->useTransactionManager()
								->MboSetRemote("assetmeterconfig")
								->where("assetnum = ? AND meternum = ?", $assetnum, $meternum)
								->getMboSet();
		
		if ($configSet->isEmpty()) return;
		$config = $configSet->getMbo();
		$new_reading_float = (float)$new_reading;
		// Ambil semua batas yang relevan
		$normal_low   = (float)$config->getString("normal_low");
		$normal_high  = (float)$config->getString("normal_high");
		$limit_low    = (float)$config->getString("limit_low");
		$limit_high   = (float)$config->getString("limit_high");

		// Inisialisasi skor awal
		$meter_score = 100.0;
		$meter_status = 'NORMAL';

		
		// Cek penyimpangan ke ATAS (Overheating, Overpressure, dll.)
		if ($limit_high > $normal_high && $new_reading_float > $normal_high) {
			// Aset berada di luar batas normal atas
			$total_abnormal_range = $limit_high - $normal_high;
			$current_deviation = $new_reading_float - $normal_high;
			
			// Hitung persentase kerusakan (damage percentage)
			$damage_percent = ($current_deviation / $total_abnormal_range) * 100;
			
			// Batasi kerusakan maksimal 100% (jika nilai aktual melebihi limit)
			$damage_percent = min($damage_percent, 100); 
			
			$meter_score = 100 - $damage_percent;
			$meter_status = ($meter_score < 50) ? 'CRITICAL' : 'WARNING'; // Tentukan status berdasarkan skor
		}
		// Cek penyimpangan ke BAWAH (Underpressure, Underlevel, dll.)
		elseif ($limit_low < $normal_low && $new_reading_float < $normal_low) {
			// Aset berada di luar batas normal bawah
			$total_abnormal_range = $normal_low - $limit_low;
			$current_deviation = $normal_low - $new_reading_float;

			$damage_percent = ($current_deviation / $total_abnormal_range) * 100;
			$damage_percent = min($damage_percent, 100);

			$meter_score = 100 - $damage_percent;
			$meter_status = ($meter_score < 50) ? 'CRITICAL' : 'WARNING';
		}
		
		// Jika tidak ada penyimpangan, skor tetap 100 dan status NORMAL.
		// echo "meter_score: $meter_score normal_low: $normal_low, normal_high: $normal_high, limit_low: $limit_low, limit_high: $limit_high <br>";
		// Update tabel assetmeter dengan skor yang sudah dihitung
		(new Set())->useTransactionManager()
				   ->MboSetRemote("assetmeter")
				   ->where("assetnum = ? AND meternum = ?", $assetnum, $meternum)
				   ->setValue("meterhealthscore", round($meter_score)) // Simpan sebagai integer bulat
				   ->setValue("meterstatus", $meter_status)
				   ->change();
	}
	public static function EnterMeterReading(){
    
		$payload = Super::getParam();
		$mtrd = $payload["entermeterreading"];
		$assetnum = $payload["assetnum"];
		
		if(empty($assetnum)){
			self::sendJsonResponse("error", "Asset Number is required");
		}	
		
		if(empty($mtrd)){
			self::sendJsonResponse("error", "Cannot find value of the reading");
		}
		
		$countmtrdata = count($mtrd);
		$enterdate = Xbo::setEnterDate();
		
		if(Super::hasParam("enterby")){
			$enterby = Super::getParam("enterby");
		}else{
			$enterby = Xbo::getLoginUser();
		}
		
		// 1. Pindahkan TransactionManager ke luar loop
		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();
		
		try {
			for($i=0; $i<$countmtrdata; $i++){
				$mtr = $mtrd[$i];
				$meternum = $mtr["meternum"];
				$delta = $mtr["delta"];
				$readingdate = $mtr["newreadingdate"]; // Tanggal baru dari input
				$entered_value = $mtr["value"];
				
				// Mengambil metertype (Ini bisa dioptimalkan jika semua meter sama)
				$metertype = (new Set())->useTransactionManager()->MboSetRemote("meter")->setWhere("meternum='$meternum'")->MboRemote("metertype")->getMboSet()->getMbo()->getString("metertype");
				
				// Mengambil data pembacaan terakhir dari history
				$historySet = (new Set())->useTransactionManager()->MboSetRemote("assetmeterhistory")
					->setWhere("assetnum='$assetnum' and meternum='$meternum' and readingdate = (select max(readingdate) from assetmeterhistory where assetnum='$assetnum' and meternum='$meternum')")
					->MboRemote("value")->MboRemote("readingdate")->getMboSet();
				
				$lastreadingdate = null;
				$lastreadingValue = 0;

				if(!$historySet->isEmpty()){
					$history = $historySet->getMbo();
					$lastreadingdate = $history->getString("readingdate");
					$lastreadingValue = $history->getString("value");
				}
				
				if($delta == "true" && $historySet->isEmpty()){
					self::sendJsonResponse("error", "Delta reading mode is selected, but no prior meter reading was found for $meternum. <br> Please enter an initial value before proceeding.");
				}
				
				if($delta == "true"){
					$isDelta = "Y";
					$new_value = $entered_value + $lastreadingValue; 
					$variance = $entered_value;
				}else{
					$isDelta = "N";
					$new_value = $entered_value;
					$variance = null; // Variance hanya relevan untuk delta
				}

				$formattedNewDate = date('Y-m-d', strtotime($readingdate));
				$formattedLastDate = $lastreadingdate ? date('Y-m-d', strtotime($lastreadingdate)) : null;

				if ($formattedNewDate === $formattedLastDate) {
					// Koreksi Data untuk tanggal yang sama
					// 1. Hapus record history yang lama untuk tanggal tersebut
					(new Set())->useTransactionManager()->MboSetRemote("assetmeterhistory")
						->setWhere("assetnum='$assetnum' and meternum='$meternum' and readingdate='$lastreadingdate'")
						->delete();
				}
				
				// Baik itu koreksi atau data baru, kita selalu INSERT record history yang baru
				(new Set())->useTransactionManager()->MboSetRemote("assetmeterhistory")
					->setValue("assetnum", $assetnum)
					->setValue("meternum", $meternum)
					->setValue("metertype", $metertype)
					->setValue("delta", $isDelta)
					->setValue("deltavalue", $variance)
					->setValue("value", $new_value)
					->setValue("readingdate", $readingdate)
					->setValue("enterby", $enterby)
					->setValue("enterdate", $enterdate)
					->add();
				
				// Selalu update tabel master 'assetmeter' dengan data terbaru
				(new Set())->useTransactionManager()->MboSetRemote("assetmeter")
					->setWhere("assetnum='$assetnum' and meternum='$meternum'")
					->setValue("lastreading", $new_value)
					->setValue("lastreadingdate", $readingdate) // Selalu update dengan tanggal baru
					->setValue("enterby", $enterby)
					->setValue("enterdate", $enterdate)
					->change();
					
					
				//INTEGRASI	
				// Check Active CBM
				ActionSet::checkAndTriggerMeterRules($assetnum, $meternum, $new_value);
				//Scoring the Asset Health
				ActionSet::updateMeterHealth($assetnum, $meternum, $new_value);
				
			}
			
			// 2. Commit transaksi SETELAH semua loop selesai
			$tm->commit();
			self::sendJsonResponse("success", "Asset Meter Reading has been updated  and CBM rules checked.");

		} catch(Exception $e) {
			// 3. Rollback
			$tm->rollback();
			throw $e;
		}
	}
	
	
	/* START CBM ENGINE */
	 private static function _getRuleStatus($rule_id, $ruletype) {
        $statusSet = (new Set())->useTransactionManager()
                                ->MboSetRemote("pmrulestatus")
                                ->where("ruleid = ? AND ruletype = ?", $rule_id, $ruletype)
                                ->getMboSet();
        if ($statusSet->isEmpty()) {
            return [
            'isactive' => "N",
            'lasttriggerwonum' => null
      	  ];
        }
        $statusMbo = $statusSet->getMbo();
        return [
            'isactive' => $statusMbo->getString("isactive"),
            'lasttriggerwonum' => $statusMbo->getString("lasttriggerwonum")
        ];
    }

	/**
     * Engine  CBM yang dipicu oleh pembacaan meter baru.
     *  Prevent WO duplikat.
     */
    public static function checkAndTriggerMeterRules($assetnum, $meternum, $new_value) {
        
        // 1. Ambil semua aturan yang relevan.
        $rulesSet = (new Set())->useTransactionManager()
                               ->MboSetRemote("pmmeterrule")
                               ->where("assetnum = ? AND meternum = ?", $assetnum, $meternum)
                               ->getMboSet();

        if ($rulesSet->isEmpty()) {
            return; // Tidak ada aturan, tidak ada aksi.
        }
        
        while ($rule = $rulesSet->fetch()) {
            $rule_id = $rule['pmmeterruleid'];
            $pmnum = $rule['pmnum'];
            $operator = $rule['operator'];
            $trigger_point1 = (float)$rule['triggerpoint'];
			$trigger_point2 = null; 
            if (isset($rule['triggerpoint2']) && is_numeric($rule['triggerpoint2']) && (float)$rule['triggerpoint2'] !== 0.0) {
				$trigger_point2 = (float)$rule['triggerpoint2'];
			}
			if (($operator === 'IN_RANGE' || $operator === 'OUT_RANGE') && $trigger_point2 === null) {
				error_log("Skipping rule ID {$rule['pmmeterruleid']} for asset {$assetnum}: Range operator used without a valid second trigger point.");
				continue;
			}
		
            $auto_reserve = ($rule['autoreserve'] === 'Y');
            $status_data = self::_getRuleStatus($rule_id, 'METER');
			
            // Jika tidak ada data, berarti never aktif. 'isactive' adalah 'N'.
            // Jika ada data, nilainya ('Y' atau 'N').
			
            $is_already_active = $status_data ? ($status_data['isactive'] === 'Y') : false;
			
            $is_condition_met = self::evaluateCondition($new_value, $operator, $trigger_point1, $trigger_point2);
            $reservedfor = (new Set())->useTransactionManager()->MboSetRemote("pm")->where("pmnum=?", $pmnum)->getMboSet()->getMbo()->getString("owner");

            if ($is_condition_met && !$is_already_active) {
				
                // KASUS 1:  Kondisi terpenuhi DAN aturan ngggak aktif.
                $new_wonum = self::createCbmWorkOrder($rule_id, $assetnum, 'METER', $auto_reserve, $reservedfor, $pmnum);
                self::updateRuleStatus($rule_id, 'METER', $assetnum, $pmnum, true, $new_wonum, $new_value);
            
            } elseif (!$is_condition_met && $is_already_active) {
                // KASUS 2: Kondisi sudah tidak terpenuhi (kembali normal) DAN aturan masih aktif.
                self::updateRuleStatus($rule_id, 'METER', $assetnum, $pmnum, false, null, $new_value);
            
            }
            // KASUS 3 (implisit): Jika kondisi terpenuhi & sudah aktif, ATAU jika kondisi tidak terpenuhi & tidak aktif -> Maka TIDAK ADA AKSI .
        }
    }
	
	
	// Fungsi-fungsi helper untuk CBM lainnya (createCbmWorkOrder, updateRuleStatus, dan evaluateCondition)
	//1. FUNGSI EVALUATE CONDITION 
	/**
	 * The Heart Of CBM ENGINE.
	 * Mengevaluasi apakah sebuah nilai memenuhi kondisi yang ditentukan oleh operator dan titik pemicu.
	 */
	private static function evaluateCondition($current_value, $operator, $trigger_point, $trigger_point2 = null) {
        $value = (float)$current_value;
        $threshold1 = (float)$trigger_point;
        
		$threshold2 = ($trigger_point2 !== null && (float)$trigger_point2 !== 0.0) ? (float)$trigger_point2 : null;
        switch ($operator) {
            case '>':
                return $value > $threshold1;
            case '>=':
                return $value >= $threshold1;
            case '<':
                return $value < $threshold1;
            case '<=':
                return $value <= $threshold1;
            case '==':
                return $value == $threshold1;
            case '!=':
                return $value != $threshold1;
            
            case 'IN_RANGE':
                if ($threshold2 === null) {
                    error_log("Operator 'IN_RANGE' requires a non-zero trigger points.");
                    return false;
                }
                // Memastikan threshold1 adalah yang lebih kecil untuk perbandingan
                $lower = min($threshold1, $threshold2);
                $upper = max($threshold1, $threshold2);
                return ($value >= $lower && $value <= $upper);

            case 'OUT_RANGE':
                if ($threshold2 === null) {
                    error_log("Operator 'OUT_RANGE' requires a non-zero second trigger point.");
                    return false;
                }
                $lower = min($threshold1, $threshold2);
                $upper = max($threshold1, $threshold2);
                return ($value < $lower || $value > $upper);
           
            default:
                error_log("Invalid operator '{$operator}' passed to evaluateCondition function.");
                return false;
        }
    }
	
	
	
	//2. Create CBM Work ORDER 
	 private static function createCbmWorkOrder($rule_id, $assetnum, $ruletype, $auto_reserve, $reservedfor, $pmnum) {
        $getAutoNum = new Super();
        $enterdate = Xbo::setEnterDate();
        $enterby = "SYSTEM:CBM";

        $assetSet = (new Set())->useTransactionManager()->MboSetRemote("assets")->where("assetnum=?", $assetnum)->getMboSet();
        if ($assetSet->isEmpty()) {
            throw new Exception("Asset $assetnum not found for CBM WO creation.");
        }
        $asset = $assetSet->getMbo();
        $teamnum = $asset->getString("teamnum");
		
        $rule = self::_getRuleDetails($rule_id, $ruletype);
		
	
        if (!$rule) {
            throw new Exception("Rule ID $rule_id of type $ruletype not found.");
        }
		
        $wonum = self::_createWoHeader($asset, $rule, $auto_reserve, $getAutoNum, $enterby, $enterdate, $pmnum);
			
        $jobPlanSet = self::_getAssignedJobPlans($rule_id, $ruletype);

        if ($jobPlanSet->isEmpty()) {
            error_log("CBM Triggered for rule $rule_id, but no Job Plans are assigned.");
            (new Set())->useTransactionManager()->MboSetRemote("workorder")
                       ->where("wonum = ?", $wonum)->setValue("status", "WAPPR")->change();
            return $wonum; 
        }

        while ($jp = $jobPlanSet->fetch()) {
            $wtnum = $jp["wtnum"];
            self::_copyTasksToWo($wonum, $wtnum, $enterby, $enterdate);
            self::_copyMaterialsToWo($wonum, $wtnum, $asset, $enterby, $enterdate);
        }

        if ($auto_reserve) {
            self::executeReservationForWo($wonum, $enterby, $enterdate, $reservedfor);
        }
		// Hitung WO Cost 
		
		$costData = Xbo::calculateWOCost($wonum);
		if ($costData) {
			$updateSet = (new Set())->useTransactionManager();
			$updateSet->MboSetRemote("workorder")
					  ->where("wonum = ?", $wonum)
					  ->setValue("actualcost", $costData->actualcost)
					  ->setValue("plannedcost", $costData->plannedcost)
					   ->setValue("variancecost", $costData->variancecost)
					  ->change();
		}
		//END Hitung WO Cost
		
		//Update Last WO Information 
		//SINIBANG
		
		if($ruletype == "HEALTH"){
		(new Set())->useTransactionManager()
		   ->MboSetRemote("pmhealthrule")
		   ->where("pmhealthruleid = ?", $rule_id)
		   ->setValue("lastwonum", $wonum)
		   ->setValue("lastwodate", $enterdate)
		   ->change();
		}
		else if($ruletype == "METER"){
		(new Set())->useTransactionManager()
		   ->MboSetRemote("pmmeterrule")
		   ->where("pmmeterruleid = ?", $rule_id)
		   ->setValue("lastwonum", $wonum)
		   ->setValue("lastwodate", $enterdate)
		   ->change();
		}
		//End Update Last WO 
        $wo_desc = "Auto Generated CBM WO for " . ($ruletype === 'METER' ? $rule->getString("meternum") : "Asset Health") . ".";
        self::notifyTeamForNewCbmWo($wonum, $teamnum, $wo_desc, $enterby, $enterdate);
		
        return $wonum;
    }

    private static function _getRuleDetails($rule_id, $ruletype) {
        $tableName = ($ruletype === 'METER') ? "pmmeterrule" : "pmhealthrule";
        $idColumn = ($ruletype === 'METER') ? "pmmeterruleid" : "pmhealthruleid";
        $ruleSet = (new Set())->useTransactionManager()->MboSetRemote($tableName)->where("$idColumn = ?", $rule_id)->getMboSet();
        return $ruleSet->isEmpty() ? null : $ruleSet->getMbo();
    }

    private static function _createWoHeader($asset, $rule, $auto_reserve, $getAutoNum, $enterby, $enterdate, $pmnum) {
		$wonum = $getAutoNum->getAutoNumber("workorder", "update_next_value")->autonumber;
		$wo_status = $auto_reserve ? "SCHED" : "WAITSCHED";
		
		$wo_desc = ""; 
		
		 $meternum_check = method_exists($rule, 'getString') ? $rule->getString("meternum") : null;

		if ($meternum_check) {
			$trigger_source = "Meter '{$meternum_check}'";
			$trigger_point = $rule->getString("triggerpoint");
			$operator = $rule->getString("operator");
			$wo_desc = "Auto CBM WO: {$trigger_source} on asset has triggered a rule ({$operator} {$trigger_point}).";
		} else {
			$trigger_source = "Asset Health";
			$trigger_point = $rule->getString("triggerpoint");
			$operator = $rule->getString("operator");
			$wo_desc = "Auto CBM WO: {$trigger_source} has triggered a rule ({$operator} {$trigger_point}%).";
		}
	

		$teamnum = $asset->getString("teamnum");
		
		$leader = Xbo::getTeamLeader($teamnum)->personid;
	
		$woSet = (new Set())->useTransactionManager()->MboSetRemote("workorder");
		$woSet->setValue("wonum", $wonum)
			->setValue("description", $wo_desc) // Menggunakan deskripsi baru yang dinamis
			->setValue("assetnum", $asset->getString("assetnum"))
			->setValue("location", $asset->getString("location"))
			->setValue("glaccount", $asset->getString("glaccount"))
			->setValue("priority", "URGENT")
			->setValue("worktype", "CBM")
			->setValue("workclass", "PM")
			->setValue("pmnum", $pmnum)
			->setValue("teamnum", $teamnum)
			->setValue("targstartdate", $enterdate)
			->setValue("leader", $leader)
			->setValue("status", $wo_status)
			->setValue("progress", 0)
			->setValue("statusdate", $enterdate)
			->setValue("enterby", $enterby)
			->setValue("enterdate", $enterdate)
			->setValue("reportedby", $enterby)
			->setValue("reportdate", $enterdate)
			->setValue("orgcode", $asset->getString("orgcode"))
			->setValue("sitecode", $asset->getString("sitecode"))
			->add();
		return $wonum;
	}

    private static function _getAssignedJobPlans($rule_id, $ruletype) {
        $tableName = ($ruletype === 'METER') ? "pmmeterrulejp" : "pmhealthrulejp";
        $idColumn = ($ruletype === 'METER') ? "pmmeterruleid" : "pmhealthruleid";
        
        return (new Set())->useTransactionManager()->MboSetRemote($tableName)
                          ->where("$idColumn = ?", $rule_id)
                          ->order("sequence ASC")->getMboSet();
    }

    private static function _copyTasksToWo($wonum, $wtnum, $enterby, $enterdate) {
        $jobPlanTaskSet = (new Set())->useTransactionManager()->MboSetRemote("wttask")
                                    ->where("wtnum=?", $wtnum)->order("tasklinenum ASC")->getMboSet();

        while ($task = $jobPlanTaskSet->fetch()) {
            (new Set())->useTransactionManager()->MboSetRemote("wotask")
                ->setValue("wonum", $wonum)
                ->setValue("wtnum", $wtnum)
                ->setValue("tasklinenum", $task["tasklinenum"])
                ->setValue("description", $task["description"])
                ->setValue("targstartdate", $enterdate)
                ->setValue("progress", 0)
                ->setValue("status", "SCHED")
                ->setValue("enterby", $enterby)
                ->setValue("enterdate", $enterdate)
                ->add();
        }
    }

    private static function _copyMaterialsToWo($wonum, $wtnum, $asset, $enterby, $enterdate) {
        $jobPlanMaterialSet = (new Set())->useTransactionManager()->MboSetRemote("wtplanmaterial")
                                         ->where("wtnum=?", $wtnum)->order("planlinenum ASC")->getMboSet();

        $gldebitacct = $asset->getString("glaccount");
        while ($material = $jobPlanMaterialSet->fetch()) {
            $warehouse = $material["warehouse"];
            $glcreditacct = Xbo::getwhgl($warehouse, "no-print");
            $linecost = floatval($material["quantity"]) * floatval($material["unitcost"]);

            (new Set())->useTransactionManager()->MboSetRemote("woplanmaterial")
                ->setValue("wonum", $wonum)
                ->setValue("planlinenum", $material["planlinenum"])
                ->setValue("itemnum", $material["itemnum"])
                ->setValue("quantity", $material["quantity"])
                ->setValue("unitcost", $material["unitcost"])
                ->setValue("linecost", $linecost)
                ->setValue("conditiontype", $material["conditiontype"])
                ->setValue("warehouse", $warehouse)
                ->setValue("racknum", $material["racknum"])
                ->setValue("enterby", $enterby)
                ->setValue("enterdate", $enterdate)
                ->setValue("gldebitacct", $gldebitacct)
                ->setValue("glcreditacct", $glcreditacct)
                ->add();
        }
    }

    public static function executeReservationForWo($wonum, $enterby, $enterdate, $reservedfor){
		$set = new Set();
        $woplanmaterialSet = $set->useTransactionManager()->MboSetRemote("woplanmaterial")->setWhere("wonum='$wonum'")
		->MboRemote("itemnum")
		->MboRemote("quantity")
		->MboRemote("conditiontype")
		->MboRemote("warehouse")
		->MboRemote("racknum")->getMboSet();
		
		while($material = $woplanmaterialSet->fetch()){
			$itemnum = $material["itemnum"];
			$quantity_needed = floatval($material["quantity"]); 
			$warehouse = $material["warehouse"];
			$conditiontype = $material["conditiontype"];
			$racknum = $material["racknum"];
			
			// Variabel untuk audit trail
			$changeby = $enterby;
			// START: BUSINESS LOGIC PENENTUAN STATUS RESERVATION
			// A. Buat klausa WHERE yang konsisten untuk query inventory & reservation
					
			$rackval = Xbo::RackWhereClause($racknum);
			$condval = Xbo::CondTypeWhereClause($conditiontype);
			$whereclauseItem = "itemnum='$itemnum' AND warehouse='$warehouse' $condval";
			// B. Hitung total stok fisik yang ada di gudang
			
			$inventorySet = (new Set())->useTransactionManager()->MboSetRemote("invfifolifocost")->setWhere($whereclauseItem)->MboRemote("SUM(quantity) as total_qty")->getMboSet();
			$inventory = $inventorySet->getMbo();				
			$total_qty_on_hand = $inventory ? floatval($inventory->getString("total_qty")) : 0;
			
			// C. Hitung total stok yang sudah direservasi oleh orang lain
			$setRes = new Set();
			$invreserveSet = $setRes->useTransactionManager()->MboSetRemote("invreserve")->setWhere($whereclauseItem . " AND status='RESERVED'")->MboRemote("SUM(quantity) as total_qty_reserved")->getMboSet();
			$invreserve = $invreserveSet->getMbo();
			$total_qty_reserved = $invreserve ? floatval($invreserve->getString("total_qty_reserved")) : 0;
			
			// D. Hitung stok yang benar-benar tersedia (Available Stock)
			$available_stock = $total_qty_on_hand - $total_qty_reserved;
			
			// E. Tentukan status berdasarkan ketersediaan stok
			if ($available_stock >= $quantity_needed) {
				$status = "RESERVED";
			} else {
				$status = "WAITING_LIST";
			}
			// END: BUSINESS LOGIC PENENTUAN STATUS
			
			// START: BUSINESS LOGIC PEMBUATAN INVRESERVENUM 
			
			// Metode: 
			// - PREFIX: 'RES-' agar mudah diidentifikasi.
			// - KONTEKS: Nomor Work Order ($wonum).
			// - KEUNIKAN: Bagian akhir dari uniqid() untuk mencegah duplikasi jika item yang sama ada 2 kali di satu WO.
			
			$unique_part = substr(uniqid(), -4); // Ambil 4 karakter terakhir dari ID unik berbasis microtime
			$invreservenum = "RES-" . $wonum . "-" . $unique_part;
			
			
			// END: BUSINESS LOGIC PEMBUATAN INVRESERVENUM
			
			
			// F. Simpan data reservasi ke database
			$setAdd = new Set();
			$setAdd->useTransactionManager()->MboSetRemote("invreserve")
				->setValue("wonum", $wonum)
				->setValue("invreservenum", $invreservenum) 
				->setValue("status", $status)
				->setValue("itemnum", $itemnum)
				->setValue("quantity", $quantity_needed)
				->setValue("warehouse", $warehouse)
				->setValue("conditiontype", $conditiontype)
				->setValue("racknum", $racknum) 
				->setValue("enterby", $enterby)
				->setValue("enterdate", $enterdate)
				->setValue("changeby", $changeby)
				->setValue("reservedfor", $reservedfor)
				->setValue("targstartdate", $enterdate)
				->add();

		}
    }

    public static function notifyTeamForNewCbmWo($wonum, $teamnum, $wo_desc, $enterby, $enterdate){
		
		$workorderid = (new Set())->MboSetRemote("workorder")->where("wonum=?", $wonum)
			->getMboSet()->getMbo()->getString("workorderid");

		$woSet = (new Set())->useTransactionManager()->MboSetRemote("workorder")->where("wonum=?", $wonum)
			->MboRemote("leader")->MboRemote("owner")->MboRemote("teamnum")->MboRemote("workorderid")
			->getMboSet();

		$wo     = $woSet->getMbo();
		$leader = $wo->getString("leader");
		$owner  = $wo->getString("owner");
		$teamnum = $wo->getString("teamnum");
		$workorderid =  $wo->getString("workorderid");
			
		$usersToNotified = [];
		$usersToNotified[] = Xbo::getUserName($leader);
		$usersToNotified[] = Xbo::getUserName($owner);

		$teamMemberSet = (new Set())->MboSetRemote("empteam")->where("teamnum=?", $teamnum)->MboRemote("personid")->getMboSet();
		if(!$teamMemberSet->isEmpty()){
			while($teamMember = $teamMemberSet->fetch()){
				$username = Xbo::getUserName($teamMember["personid"]);
				$usersToNotified[] = $username;
			}
		}

		// Hilangin duplikat username
		$usersToNotified = array_unique($usersToNotified);

		foreach ($usersToNotified as $username) {
			
			if(!empty($username)){
				$notificationData = [
					'username'    => $username, 
					'message'     => "CBM Work Order has arised. Please Follow up WO: $wonum , Description : $wo_desc",
					'objectname'  => 'workorder',
					'objectid'    => $workorderid, 
					'objectnum'   => $wonum, 
					'processname' => 'CBM_WO'
				];

				Super::sendNotification($notificationData);	
			}
		}
	}

	
	
	//END CREATE CBM WORK ORDER
	// CREATE Update Rule Status 
	  /**
     * Memperbarui atau membuat catatan status untuk sebuah aturan CBM.
     * Fungsi ini adalah untuk mencatat kapan aturan aktif atau di-reset. Semacem bagian dari otak CBM yang berfungsi untuk mengingat.
     */
    public static function updateRuleStatus($rule_id, $ruletype, $assetnum, $pmnum, $is_active, $new_wonum, $current_value) {
        
        // 1. Cek apakah sudah ada catatan status untuk aturan ini.
        $statusSet = (new Set())->useTransactionManager()
                                ->MboSetRemote("pmrulestatus")
                                ->where("ruleid = ? AND ruletype = ?", $rule_id, $ruletype)
                                ->getMboSet();

        $enterdate = Xbo::setEnterDate();
        if ($statusSet->isEmpty()) {
            // 2a. JIKA TIDAK ADA: (Ini hanya terjadi saat aturan terpicu untuk pertama kalinya) , Buat catatan baru (INSERT). 
            (new Set())->useTransactionManager()
                       ->MboSetRemote("pmrulestatus")
                       ->setValue("ruleid", $rule_id)
                       ->setValue("ruletype", $ruletype)
                       ->setValue("assetnum", $assetnum)
                       ->setValue("pmnum", $pmnum)
                       ->setValue("isactive", $is_active ? "Y" : "N") 
                       ->setValue("lasttriggerdate", $enterdate)
                       ->setValue("lasttriggerwonum", $new_wonum)
                       ->setValue("currentvalue", $current_value)
                       ->add();
        } else {
            // 2b. JIKA SUDAH ADA: Perbarui catatan yang ada (UPDATE).
            $updateSet = (new Set())->useTransactionManager()
                                  ->MboSetRemote("pmrulestatus")
                                  ->where("ruleid = ? AND ruletype = ?", $rule_id, $ruletype);
            
            $updateSet->setValue("isactive", $is_active ? "Y" : "N");
            $updateSet->setValue("currentvalue", $current_value);

            if ($is_active) {
                // Jika kita mengaktifkan aturan (memicu WO baru)
                $updateSet->setValue("lasttriggerdate", $enterdate);
                $updateSet->setValue("lasttriggerwonum", $new_wonum);
                $updateSet->setValue("lastresetdate", null); // Hapus tanggal reset sebelumnya
            } else {
                // Jika kita menonaktifkan aturan (meresetnya)
                $updateSet->setValue("lastresetdate", $enterdate);
            }
            $updateSet->change();
        }
    }
	
	// END Function CBM Helper

	public static function CheckSubRecord($node = null){
	
		$chksubrecord = $_POST['chksubrecord'];
		$chksubkey = $_POST['chksubkey'];
		$tablename = $_POST['ddtablename'];
		
		$setas = new Set();
		$count = 0;
		if($tablename == "assets"){
			$tablesubs = "assetsubs";
			$tablesubswhere = "subassetnum='$chksubrecord'";
			$tablesubcol =  "subassetnum";
			$tablecol =  "assetnum";
		}else{
			if($tablename == "workorder"){
				$tablesubs = "wosubs";
				$tablesubswhere = "subwonum='$chksubrecord'";
				$tablesubcol =  "subwonum";
				$tablecol =  "wonum";
			}
		}
		
		$subassetSet = $setas->MboSetRemote($tablesubs)->setWhere($tablesubswhere)->MboRemote($tablesubcol)->MboRemote($tablecol)->getMboSet();
		
		$subasset = $subassetSet->getMbo();		
		$newkey = $subasset->getString($tablecol);
		$newsubassetnum = $subasset->getString($tablesubcol);
		$count = $subassetSet->counts();
		
		if($count > 0){
			Set::throws(["msg"=>"exist", "parent"=>$newkey]);
		}else{
		
			$a = new XboSet();
			$isparentExist = $a->CheckAllParent($chksubkey, $chksubrecord, $tablename);
		}
	}
	
	public static function addClassifications()
	{
		
		$payload = Super::getParam("addClassifications");

		$classcode = $payload["classificationcode"];
		$keynum = $payload["assetnum"];
		$app = $payload["app"];
		
		$enterdate = Xbo::setEnterDate();
		$enterby = Xbo::getLoginUser();

		$classAttrSet = (new Set())->useTransactionManager()->MboSetRemote("classattribute")->where("classificationcode=?", $classcode)
		->MboRemote("attributename, type")->getMboSet();
		
		if(!$classAttrSet->isEmpty()){
			$numrows = $classAttrSet->counts();
			for ($i = 0; $i < $numrows; $i++) {
				$classAttr = $classAttrSet->getMbo();
				$attributename = $classAttr->getString("attributename");
				$type = $classAttr->getString("type");

				$tabSet = (new Set())->useTransactionManager()->MboSetRemote("assetclassification")
				->where("assetnum=? and attributename=? ", $keynum, $attributename)
				->MboRemote("attributename")->getMboSet();
			
				if ($tabSet->isEmpty()) {
					(new Set())->useTransactionManager()->MboSetRemote("assetclassification")
					->setValue("classificationcode", $classcode)
					->setValue("attributename", $attributename)
					->setValue("type", $type)
					->setValue("enterby", $enterby)
					->setValue("enterdate", $enterdate)
					->setValue("assetnum", $keynum)
					->add();
				}
				
			}
			Super::sendJsonResponse("error", "Attribute(s) of $classcode has been added.");	
		}else{
			Super::sendJsonResponse("error", "Attribute(s) of $classcode is null.");	
		}		
	}

	public static function addHealthAdvisor(){
		
		$payload = Super::getParam();
		$assetnum = $payload["assetnum"];
		$meters = $payload["addHealthAdvisor"];
		
		$enterby = Xbo::getLoginUser();
		$enterdate = Xbo::setenterdate();
		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();
		try{
			for($i=0; $i<count($meters); $i++){
				$meternum = $meters[$i]["meternum"];
				$whereclause = "meternum='$meternum' and assetnum='$assetnum'";
				$assetMeterSet = (new Set())->useTransactionManager()->MboSetRemote("assetmeter")
				->setWhere($whereclause)->MboRemote("meternum")->getMboSet();
				
				if($assetMeterSet->isEmpty()){
					$metertype = (new Set())->useTransactionManager()->MboSetRemote("meter")->setWhere("meternum='$meternum'")->MboRemote("metertype")->getMboSet()->getMbo()->getString("metertype");
					
					(new Set())->useTransactionManager()->MboSetRemote("assetmeter")
					->setValue("assetnum", $assetnum)
					->setValue("meternum", $meternum)
					->setValue("enterby", $enterby)
					->setValue("enterdate", $enterdate)
					->add();

				}
				$assetMeterConfigSet = (new Set())->useTransactionManager()->MboSetRemote("assetmeterconfig")->setWhere($whereclause)->MboRemote("meternum")->getMboSet();
				if($assetMeterConfigSet->isEmpty()){
					(new Set())->useTransactionManager()->MboSetRemote("assetmeterconfig")
					->setValue("assetnum", $assetnum)
					->setValue("meternum", $meternum)
					->setValue("enterby", $enterby)
					->setValue("enterdate", $enterdate)
					->add();
				}
				
			}
			$tm->commit();
			self::sendJsonResponse("success", "Asset Health Advisor has been added");
		}catch(Exception $e){
			$tm->rollback();
			throw $e;
		}
	}
	
	public static function AddExistingMeters(){
		
		$payload = Super::getParam();
		$assetnum = $payload["assetnum"];
		$meters = $payload["addmeters"];
		
		$enterby = Xbo::getLoginUser();
		$enterdate = Xbo::setenterdate();
		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();
		try{
			for($i=0; $i<count($meters); $i++){
				$meternum = $meters[$i]["meternum"];
				$whereclause = "meternum='$meternum' and assetnum='$assetnum'";
				$assetMeterSet = (new Set())->useTransactionManager()->MboSetRemote("assetmeter")
				->setWhere($whereclause)->MboRemote("meternum")->getMboSet();
				
				if($assetMeterSet->isEmpty()){
					$metertype = (new Set())->useTransactionManager()->MboSetRemote("meter")->setWhere("meternum='$meternum'")->MboRemote("metertype")->getMboSet()->getMbo()->getString("metertype");
					
					(new Set())->useTransactionManager()->MboSetRemote("assetmeter")
					->setValue("assetnum", $assetnum)
					->setValue("meternum", $meternum)
					->setValue("enterby", $enterby)
					->setValue("enterdate", $enterdate)
					->add();

				}
				$assetMeterConfigSet = (new Set())->useTransactionManager()->MboSetRemote("assetmeterconfig")->setWhere($whereclause)->MboRemote("meternum")->getMboSet();
				if($assetMeterConfigSet->isEmpty()){
					(new Set())->useTransactionManager()->MboSetRemote("assetmeterconfig")
					->setValue("assetnum", $assetnum)
					->setValue("meternum", $meternum)
					->setValue("enterby", $enterby)
					->setValue("enterdate", $enterdate)
					->add();
				}
				
			}
			$tm->commit();
			self::sendJsonResponse("success", "Asset Meter has been added");
		}catch(Exception $e){
			$tm->rollback();
			throw $e;
		}
	}


	public static function ytdcost()

	{
		$pts = explode("^", $_GET["ytdcost"]);
		$set = new Set();
		$assetSet = $set->MboSetRemote("assets")->setWhere("assetsid='" . $pts[1] . "'")->MboRemote("assetnum")->getMboSet();
		$asset = $assetSet->getMbo();
		$assetnum = $assetSet->getString("assetnum");
		$set = new Set();
		$sumactualcost = $set->MboSetRemote("workorder")->setWhere("assetnum='$assetnum'")->MboRemote("sum(actualcost) as sumactualcost")->getMboSet()->getMbo()->getString("sumactualcost");
		echo $sumactualcost;
	}
	
	
	
public static function callMoveAsset()
{
    $allMovedAssets = self::moveasset();

    // Ubah struktur data menjadi hierarkis
    $hierarchicalAssets = self::buildAssetHierarchy($allMovedAssets);

    $output = [
        "msg" => "ok",
        "assets" => $hierarchicalAssets
    ];

    // Set header content type ke json
    header('Content-Type: application/json');

    // Cetak JSON
    echo json_encode($output);
    exit();
}

private static function buildAssetHierarchy($assets, $parentAssetnum = null)
{
    $hierarchy = [];
    foreach ($assets as $asset) {
        // Cari sub-aset dari aset ini
        $set = new Set();
        $assetSubsSet = $set->MboSetRemote("assetsubs")->setWhere("assetnum='" . $asset['assetnum'] . "'")
            ->MboRemote("subassetnum")->getMboSet();
        $countChildren = $assetSubsSet->counts();

        $children = [];
        for ($i = 0; $i < $countChildren; $i++) {
            $assetSubs = $assetSubsSet->getMbo();
            $subassetnum = $assetSubs->getString("subassetnum");
            // Rekursif untuk membangun hirarki sub-aset
            $children = array_merge($children, self::buildAssetHierarchy([['assetnum' => $subassetnum, 'tolocation' => $asset['tolocation']]]));
        }

        // Tambahkan sub-aset ke aset saat ini
        if (!empty($children)) {
            $asset['children'] = $children;
        } else {
            $asset['children'] = [];
        }

        $hierarchy[] = $asset;

    }
    return $hierarchy;
}


public static function moveasset()
{

    $assetnum = $_POST["moveasset"];
    $fromloc = $_POST["fromloc"];
    $toloc = $_POST["toloc"];

    $enterdate = Xbo::setenterdate();
    $enterby = Xbo::getLoginUser();
    $changeby = $enterby;
    $changedate = $enterdate;

    // Move Asset Current Location to New Location
    $set = new Set();
    $set->MboSetRemote("assets")->setWhere("assetnum='$assetnum'")->setValue("locnum", $toloc)->change();

    //Add Move History
    $set = new Set();
    $set->MboSetRemote("assettrans")
        ->setValue("assetnum", $assetnum)
        ->setValue("fromlocation", $fromloc)
        ->setValue("tolocation", $toloc)
        ->setValue("enterby", $enterby)
        ->setValue("enterdate", $enterdate)
        ->setValue("changeby", $changeby)
        ->setValue("changedate", $changedate)
        ->add();

    //Check if Asset has children
    $set = new Set();
    $assetSubsSet = $set->MboSetRemote("assetsubs")->setWhere("assetnum='$assetnum'")
        ->MboRemote("subassetnum")->getMboSet();
    $countChildren = $assetSubsSet->counts();

    $movedAssets = []; // Array untuk mengumpulkan data aset yang dipindahkan

    for ($i = 0; $i < $countChildren; $i++) {
        $assetSubs = $assetSubsSet->getMbo();
        $subassetnum = $assetSubs->getString("subassetnum");

        // Panggil fungsi moveasset() secara rekursif untuk setiap sub-aset
        // Buat array $_POST yang sesuai untuk sub-aset
        $postData = [
            "moveasset" => $subassetnum,
            "fromloc" => $fromloc,  // Atau mungkin dapatkan lokasi saat ini dari sub-aset
            "toloc" => $toloc
        ];

        // Simpan $_POST saat ini
        $originalPost = $_POST;

        // Set $_POST dengan data untuk sub-aset
        $_POST = $postData;

        // Panggil fungsi moveasset() secara rekursif
        $movedAssets = array_merge($movedAssets, self::moveassetInternal()); // Memanggil fungsi internal untuk rekursi

        // Kembalikan $_POST ke nilai semula
        $_POST = $originalPost;
    }

    //Tambahkan aset saat ini ke array
    $movedAssets[] = ["assetnum" => $assetnum, "tolocation" => $toloc];

    return $movedAssets; //Kembalikan array of asset dari setiap fungsi
}

private static function moveassetInternal()
{
    $assetnum = $_POST["moveasset"];
    $fromloc = $_POST["fromloc"];
    $toloc = $_POST["toloc"];

    $enterdate = Xbo::setenterdate();
    $enterby = Xbo::getLoginUser();
    $changeby = $enterby;
    $changedate = $changedate;

    // Move Asset Current Location to New Location
    $set = new Set();
    $set->MboSetRemote("assets")->setWhere("assetnum='$assetnum'")->setValue("locnum", $toloc)->change();

    //Add Move History
    $set = new Set();
    $set->MboSetRemote("assettrans")
        ->setValue("assetnum", $assetnum)
        ->setValue("fromlocation", $fromloc)
        ->setValue("tolocation", $toloc)
        ->setValue("enterby", $enterby)
        ->setValue("enterdate", $enterdate)
        ->setValue("changeby", $changeby)
        ->setValue("changedate", $changedate)
        ->add();

    //Check if Asset has children
    $set = new Set();
    $assetSubsSet = $set->MboSetRemote("assetsubs")->setWhere("assetnum='$assetnum'")
        ->MboRemote("subassetnum")->getMboSet();
    $countChildren = $assetSubsSet->counts();

    $movedAssets = []; // Array untuk mengumpulkan data aset yang dipindahkan

    for ($i = 0; $i < $countChildren; $i++) {
        $assetSubs = $assetSubsSet->getMbo();
        $subassetnum = $assetSubs->getString("subassetnum");

        // Panggil fungsi moveasset() secara rekursif untuk setiap sub-aset
        // Buat array $_POST yang sesuai untuk sub-aset
        $postData = [
            "moveasset" => $subassetnum,
            "fromloc" => $fromloc,  // Atau mungkin dapatkan lokasi saat ini dari sub-aset
            "toloc" => $toloc
        ];

        // Simpan $_POST saat ini
        $originalPost = $_POST;

        // Set $_POST dengan data untuk sub-aset
        $_POST = $postData;

        // Panggil fungsi moveasset() secara rekursif
        $movedAssets = array_merge($movedAssets, self::moveassetInternal()); // Memanggil fungsi internal untuk rekursi

        // Kembalikan $_POST ke nilai semula
        $_POST = $originalPost;
    }

    //Tambahkan aset saat ini ke array
    $movedAssets[] = ["assetnum" => $assetnum, "tolocation" => $toloc];

    return $movedAssets; //Kembalikan array of asset dari setiap fungsi
}


	public static function updassetparent(){
		$recid = $_GET['updassetparent'];
		$set = new Set();
		$ts = explode("^", $recid);
		$table = $ts[0];
		$id = $ts[1];
		$tablenameid = $table."id";
		$tablenamekey = "assetnum";
		$colnamesub = "subassetnum";
		$tablenamesub = "assetsubs";
		if($table == "workorder"){
			$tablenamekey = "wonum";
			$colnamesub = "subwonum";
			$tablenamesub = "wosubs";
		}
		
		$assetSet = $set->MboSetRemote($table)->setWhere("$tablenameid='$id'")->MboRemote($tablenamekey)->getMboSet();		
		$assetnum = $assetSet->getMbo()->getString($tablenamekey);
		$set2 = new Set();
		$assetSubSet = $set2->MboSetRemote($tablenamesub)->setWhere("$tablenamekey='$assetnum'")->MboRemote($colnamesub)->getMboSet();
		$cnt = $assetSubSet->counts();
		for($i=0; $i<$cnt; $i++){
			$assetsub = $assetSubSet->getMbo();
			$subassetnum = $assetsub->getString($colnamesub);
			
			$set3 = new Set();
			$set3->MboSetRemote($table)->setWhere("$tablenamekey='$subassetnum'")->setValue("parent", $assetnum)->change();
		}
	}
	public static function calcdeprec()

	{
		
		
		$purchasedate = $_POST["purchasedate"];
		$purchaseprice = $_POST["purchaseprice"];
		$depuntil = $_POST["depreciateddate"];
		$residualval = $_POST["salvagevalue"];
		$depretype = $_POST["depretype"];
		$assetnum = $_POST["calcdeprec"];
		if (isset($_POST["rate"])) {
			$rate = $_POST["rate"];
		}
		$opurchasedate = strtotime($purchasedate);
		$odepuntil = strtotime($depuntil);
		$odate = $odepuntil - $opurchasedate;
		$set = new Set();
		
		if ($depretype == "straight-line") {
			$isExist = $set->MboSetRemote("assetdepreciation")->setWhere("assetnum='$assetnum'")->MboRemote("assetnum")->getMboSet()->counts();
			if ($isExist == 0) {
				if ($odate < 5256000) {
					$msg =  "musthigherthantwomonths";
				}
				else {
				
					$totalmonths = 0;
					$daypurdate = date("j", $opurchasedate);
					$monthpurdate = date("n", $opurchasedate);
					$yearpurdate = date("Y", $opurchasedate);
					$dayodepuntil = date("j", $odepuntil);
					$monthodepuntil = date("n", $odepuntil);
					$yearodepuntil = date("Y", $odepuntil);
					$dater = array();
					for ($i = $yearpurdate; $i <= $yearodepuntil; $i++) {
						$startdate = $daypurdate;
						$startyear = $i;
						$finishdate = date("t", $opurchasedate);
						for ($j = $monthpurdate; $j <= 12; $j++) {
							$startmonth = $monthpurdate;
							$finishmonth = $j;
							if ($j == 12) {
								$monthpurdate = 1;
								$daypurdate = 1;
								$finishdate = date("t", $monthpurdate);
							}
							if ($j == $monthodepuntil + 1 && $i == $yearodepuntil) {
								$finishdate = $dayodepuntil;
								$finishmonth = $monthodepuntil;
								break;
							}
							$totalmonths++;
						}
						$fromdate = $startyear . "-" . $startmonth . "-" . $startdate;
						$todate = $startyear . "-" . $finishmonth . "-" . $finishdate;
						$parts = $fromdate . ">" . $todate;
						array_push($dater, $parts);
					}
					$monthlydepcharge = round(($purchaseprice - $residualval) / $totalmonths, 2);
					$totaldeprecost = 0;
					$enterdate = Xbo::setenterdate();
					$enterby = Xbo::getLoginUser();
					$changeby = $enterby;
					$changedate = $enterdate;
					for ($i = 0; $i < count($dater); $i++) {
						$pts = explode(">", $dater[$i]);
						$Rfromdate = $pts[0];
						$Rtodate = $pts[1];
						
						$month_variance = (date("n", strtotime($Rtodate)) - date("n", strtotime($Rfromdate))) + 1;
						$deprecost = $monthlydepcharge * $month_variance;
						$monthlycost = $monthlydepcharge;
						$totaldeprecost = $totaldeprecost + $monthlycost;
						$assetvalue = $purchaseprice - $totaldeprecost;
						$set->MboSetRemote("assetdepreciation")->setValue("fromdate", $Rfromdate)->setValue("todate", $Rtodate)->setValue("assetnum", $assetnum)->setValue("assetvalue", $assetvalue)->setValue("deprecost", $deprecost)->setValue("monthlycost", $monthlycost)->setValue("totaldeprecost", $totaldeprecost)->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)->setValue("changeby", $changeby)->setValue("changedate", $changedate)->add();
					}
					
					$msg= "ok";
				}
			}
			else {
				$msg= "deprecexist";
			}
		}
		if ($depretype == "declining-balance") {
			$isExist = $set->MboSetRemote("assetdepreciation")->setWhere("assetnum='$assetnum'")->MboRemote("assetnum")->getMboSet()->counts();
			if ($isExist == 0) {
				if ($odate < 5256000) {
					// 2 months
					$msg = "musthigherthantwomonths";
				}
				else {
					$totalmonths = 0;
					$daypurdate = date("j", $opurchasedate);
					$monthpurdate = date("n", $opurchasedate);
					$yearpurdate = date("Y", $opurchasedate);
					$dayodepuntil = date("j", $odepuntil);
					$monthodepuntil = date("n", $odepuntil);
					$yearodepuntil = date("Y", $odepuntil);
					$dater = array();
					for ($i = $yearpurdate; $i <= $yearodepuntil; $i++) {
						$startdate = $daypurdate;
						$startyear = $i;
						$finishdate = date("t", $opurchasedate);
						for ($j = $monthpurdate; $j <= 12; $j++) {
							$startmonth = $monthpurdate;
							$finishmonth = $j;
							if ($j == 12) {
								$monthpurdate = 1;
								$daypurdate = 1;
								$finishdate = date("t", $monthpurdate);
							}
							if ($j == $monthodepuntil + 1 && $i == $yearodepuntil) {
								$finishdate = $dayodepuntil;
								$finishmonth = $monthodepuntil;
								break;
							}
							$totalmonths++;
						}
						$fromdate = $startyear . "-" . $startmonth . "-" . $startdate;
						$todate = $startyear . "-" . $finishmonth . "-" . $finishdate;
						$parts = $fromdate . ">" . $todate;
						array_push($dater, $parts);
					}
					$totaldeprecost = 0;
					$enterdate = Xbo::setenterdate();
					$enterby = Xbo::getLoginUser();
					$changeby = $enterby;
					$changedate = $enterdate;
					for ($i = 0; $i < count($dater); $i++) {
						$pts = explode(">", $dater[$i]);
						$Rfromdate = $pts[0];
						$Rtodate = $pts[1];
						$yearlydepcharge = round(($purchaseprice - $residualval) * ($rate / 100) , 2);
						$monthlycost = $yearlydepcharge / 12;
						$totaldeprecost = $totaldeprecost + $yearlydepcharge;
						$purchaseprice = $purchaseprice - $yearlydepcharge;
						$assetvalue = $purchaseprice;
						$set->MboSetRemote("assetdepreciation")->setValue("fromdate", $Rfromdate)->setValue("todate", $Rtodate)->setValue("assetnum", $assetnum)->setValue("assetvalue", $assetvalue)->setValue("deprecost", $yearlydepcharge)->setValue("monthlycost", $monthlycost)->setValue("totaldeprecost", $totaldeprecost)->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)->setValue("changeby", $changeby)->setValue("changedate", $changedate)->add();
					}
					
					$msg = "ok";
				}
			}
		}
		
		$as = new stdClass();
		$as->msg=  $msg;
		Set::throws($as);
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
		
		
		Super::sendJsonResponse("error", "You cannot delete the records for asset properties and historical transactions! Contact your Administrator for further actions." );
	}
}
	

?>