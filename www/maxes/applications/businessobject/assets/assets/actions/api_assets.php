<?php 
	header("Content-Type: application/json; charset=UTF-8");
	header("Access-Control-Allow-Origin: *");
	//require assets action
	require_once ("actions.php");
	
	
		
	$action = Super::getParam("action");
	switch ($action) {
		case 'getMeasureUnits': 
			API_Assets::getMeasureUnits();
			break;
		case 'deleteMeterReading':
			API_Assets::deleteMeterReading();
			break;
		case 'updateMeterReading': 
			API_Assets::updateMeterReading();
			break;
		case 'saveSelectedMeter' : 
			API_Assets::saveSelectedMeter();
			break;
		case 'getMeters' : 
			API_Assets::getMeters();
			break;
		case 'getGpsList':
			API_Assets::getGpsList();
			break;	
		case 'getAssetLocation':
			API_Assets::getAssetLocation();
			break;
		case 'getMeterReadings':
			API_Assets::getMeterReadings();
			break;
		case 'getAssetList':
			API_Assets::getAssetList();
			break;
			
		case 'getAssetDetail':
			API_Assets::getAssetDetail();
			break;
		case 'getMaintenanceHistory':
			API_Assets::getMaintenanceHistory();
			break;
		default:
			Super::sendJsonResponse("error", "Invalid or missing 'action' parameter.");
			break;
	}
	
	
	class API_Assets{
		public static function deleteMeterReading(){
			$assetnum = Super::getParam("assetnum");
			$meternum = Super::getParam("meterNum");
			
			if (empty($assetnum) || empty($meternum) ){
				Super::sendJsonResponse("error", "Missing required parameters: assetnum, meterNum");
				return;
			}
			
			(new Set())->MboSetRemote("assetmeter")->where("assetnum=? AND meternum=?", $assetnum, $meternum)->remove();
			Super::sendJsonResponse("success", "Meter $meternum  has been deleted");
			
		}
		public static function updateMeterReading(){
			
			$assetnum = Super::getParam("assetnum");
			$meternum = Super::getParam("meterNum");
			$newreading = Super::getParam("newReading");
			$newDate = Super::getParam("newDate");
			$enterby = Super::getParam("enterby");
			
			if (empty($assetnum) || empty($meternum) || empty($newreading) || empty($newDate)) {
				Super::sendJsonResponse("error", "Missing required parameters: assetnum, meterNum, newReading, or newDate.");
				return; // Hentikan eksekusi
			}
			 $structuredData = [
				"meternum" => $meternum,
				"value" => $newreading,
				"newreadingdate" => $newDate,
				"delta" => "false"
			];

			 $payloadForAction = [
				$structuredData
			];
			
			$_POST['assetnum'] = $assetnum;
			$_POST['entermeterreading'] = $payloadForAction;
			$_POST['enterby'] = $enterby;
		
			try {
				ActionSet::EnterMeterReading();
			} catch (Exception $e) {
				Super::sendJsonResponse("error", "An exception occurred: " . $e->getMessage());
			}
		}
		
		public static function getAssetDetail(){
			try{
				$assetnum = Super::getParam("assetnum");
				if (empty($assetnum)) {
					Super::sendJsonResponse("error", "Parameter 'assetnum' is required.");
				}
				$set = new Set();
				$asetSet = $set->MboSetRemote("assets")
                          ->where("assetnum = ?", $assetnum)
                          ->getMboSet();
				if ($asetSet->isEmpty()) {
					Super::sendJsonResponse("error", "Asset with number '$assetnum' not found.");
				}
				$assetDetail = $asetSet->fetch();
				Super::sendJsonResponse("success", "Asset detail loaded", $assetDetail);
				
			}catch (Exception $e) {
				//akan diteruskan oleh error handling yang lebih tinggi
			}
		}
		
		public static function getMeasureUnits()
		{
			try{
				if(Super::hasParam("page")){
				$page = Super::getParam("page");
				}else{
					$page = 1;
				}
				if(Super::hasParam("limit")){
					 $limit  = Super::getParam("limit");
				}else{
					$limit = 20;
				}

				$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
				 
			 // Hitung offset untuk query database
				$offset = ($page - 1) * $limit;
				$set = new Set();
				
				 $objectSet = $set->MboSetRemote("measureunit")
							  ->MboRemote("measureunitcode")
							  ->MboRemote("description") 
							  ->MboRemote("measureunitid")   
							  ->order("measureunitcode");
								
					if (!empty($searchTerm)) {
						// klausa WHERE yang mencari di beberapa kolom sekaligus
						$whereClause = "measureunitcode LIKE ? OR description LIKE ?";
						$likeTerm = '%' . $searchTerm . '%';
						$set->where($whereClause, $likeTerm, $likeTerm);
					}
					 $set->limit("$offset, $limit")->getMboSet();


				// 3. Ubah hasil query menjadi array
				$objects = [];
				if (!$objectSet->isEmpty()) {
					while ($object = $objectSet->fetch()) {
						$objects[] = $object;
					}
				}
				
				
				// 4. Kirim sebagai JSON
				// Kita buat struktur respons yang bagus, menyertakan informasi halaman
				$response = [
					'page' => $page,
					'data' => $objects
				];
			   Super::sendJsonResponse("success", "Data Loaded", $response);
			}catch(Exception $e){
				Super::sendJsonResponse("error", $e->getMessage());
			}
	
		}
		public static function getMeterReadings() {
			$meterReadings = [];
			try {
				$assetnum = Super::getParam("assetnum");
				if (empty($assetnum)) {
					throw new Exception("Parameter 'assetnum' is required.");
				}
				
				$set = new Set();
				$meterSet = $set->MboSetRemote("assetmeter")
							   ->where("assetnum = ?", $assetnum)
							   ->order("lastreadingdate DESC") 
							   ->getMboSet();

				$meterReadings = $meterSet->fetchData(); 
			
				Super::sendJsonResponse("success", "Meter readings loaded", $meterReadings);

			} catch (Exception $e) {
				Super::sendJsonResponse("error", $e->getMessage());
			}
		}
		
		
		public static function getAssetList(){
			try{
				if(Super::hasParam("page")){
				$page = Super::getParam("page");
				}else{
					$page = 1;
				}
				if(Super::hasParam("limit")){
					 $limit  = Super::getParam("limit");
				}else{
					$limit = 20;
				}

				$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
				 
			 // Hitung offset untuk query database
				$offset = ($page - 1) * $limit;
				$set = new Set();
				
				 $asetSet = $set->MboSetRemote("assets")
							  ->MboRemote("assetnum")      // Nomor Aset
							  ->MboRemote("description")  // Deskripsi
							  ->MboRemote("model")        // Model
							  ->MboRemote("status")       // Status
							  ->MboRemote("locnum")       // Lokasi
							  ->order("assetnum ASC");
								
					if (!empty($searchTerm)) {
						// klausa WHERE yang mencari di beberapa kolom sekaligus
						$whereClause = "assetnum LIKE ? OR description LIKE ? OR locnum LIKE ?";
						$likeTerm = '%' . $searchTerm . '%';
						$set->where($whereClause, $likeTerm, $likeTerm, $likeTerm);
					}
					 $set->limit("$offset, $limit")->getMboSet();


				// 3. Ubah hasil query menjadi array
				$assets = [];
				if (!$asetSet->isEmpty()) {
					while ($aset = $asetSet->fetch()) {
						$assets[] = $aset;
					}
				}
				
				
				// 4. Kirim sebagai JSON
				// Kita buat struktur respons yang bagus, menyertakan informasi halaman
				$response = [
					'page' => $page,
					'data' => $assets
				];
			   Super::sendJsonResponse("success", "Data Loaded", $response);
			}catch(Exception $e){
				Super::sendJsonResponse("error", $e->getMessage());
			}
	
		}
	
		
		public static function getMaintenanceHistory() {	
			try {
				
				$page = Super::getParam("page", 1);
				$limit = Super::getParam("limit", 20);
				$searchTerm = Super::getParam("search", '');
				$assetnum = Super::getParam("assetnum", '');

				// --- KOREKSI 2: Validasi parameter wajib ---
				if (empty($assetnum)) {
					throw new Exception("Parameter 'assetnum' is required.");
				}
				
				$offset = ($page - 1) * $limit;
				
				$set = new Set();
				$workorderSet = $set->useTransactionManager()->MboSetRemote("workorder")->order("targstartdate DESC");
					
				if (!empty($searchTerm)) {
					$searchClause = "(wonum LIKE ? OR description LIKE ? OR worktype LIKE ?)";
					$likeTerm = '%' . $searchTerm . '%';
					$workorderSet->where($searchClause, $likeTerm, $likeTerm, $likeTerm);
				}else{
					$workorderSet->where("assetnum = ?", $assetnum);;
				}

				
				// 1. Dapatkan data agregat SEBELUM pagination
				$aggregationSet = clone $workorderSet; // Duplikat objek Set agar tidak mengganggu query utama
				$aggregationSet->MboRemote("COUNT(*) as total_wo, SUM(actualcost) as total_cost");
				$aggregationResult = $aggregationSet->getMboSet()->fetch();

				$totalWo = $aggregationResult['total_wo'] ?? 0;
				$totalCost = $aggregationResult['total_cost'] ?? 0;
				
				// 2. Sekarang terapkan pagination dan ambil daftar datanya
				$offset = ($page - 1) * $limit;
				$workorderSet = $set->MboRemote("wonum, description, status, status, targstartdate, targcompdate, actstartdate, actfinishdate, worktype, plannedcost, actualcost")
									->limit("$offset, $limit")
									->getMboSet();
				$workorders = $workorderSet->fetchData();

				// 3. Sertakan data agregat di dalam respons JSON
				$response = [
					'page' => $page,
					'total_wo' => (int)$totalWo, // Kirim sebagai integer
					'total_cost' => (float)$totalCost, // Kirim sebagai float/double
					'data' => $workorders
				];
				Super::sendJsonResponse("success", "Data Loaded", $response);
				
			} catch(Exception $e) {
				Super::sendJsonResponse("error", $e.getMessage());
			}    
		}
		
		public static function getAssetLocation() {
			
			$assetnum = Super::getParam("assetnum");
			if (empty($assetnum)) {
				throw new Exception("Parameter 'assetnum' is required.");
			}

			$set = new Set();
			// Ambil data lokasi terakhir dari tabel assettracking
			$locationSet = $set->MboSetRemote("assettracking")
							   ->where("assetnum = ?", $assetnum)
							   ->order("timestamp DESC") // Ambil yang paling baru
							   ->limit(1) // Hanya butuh satu data terakhir
							   ->getMboSet();

			if ($locationSet->isEmpty()) {
				throw new Exception("Location data not found for asset '$assetnum'.");
			}
			
			$locationData = $locationSet->fetch();
			Super::sendJsonResponse("success", "Location data loaded", $locationData);

			
	}
		
	public static function getGpsList(){
		$searchTerm = Super::getParam("search");
		
		$gpsList = [];
		$set = new Set();
		$assetTrackingSet = $set->useTransactionManager()->MboSetRemote("assettracking at")->setRelationship("assets ass", "at.assetnum = ass.assetnum");
		
		if (!empty($searchTerm)) {
			$whereClause = "at.assetnum LIKE ? OR ass.description LIKE ? OR ass.locnum LIKE ? OR ass.model like ? OR ass.teamnum like?" ;
			$likeTerm = '%' . $searchTerm . '%';
			$set->where($whereClause, $likeTerm, $likeTerm, $likeTerm, $likeTerm, $likeTerm);
		}
		$assetTrackingSet->MboRemote("at.assetnum, at.latitude, at.longitude, at.speed, at.timestamp")->getMboSet();
		while($at = $assetTrackingSet->fetch()){
			$ss = new stdClass;
			$ss->assetnum = $at["assetnum"];
			$ss->latitude = $at["latitude"];
			$ss->longitude = $at["longitude"];
			$ss->speed = $at["speed"];
			$ss->timestamp = $at["timestamp"];
			$gpsList [] = $ss;
		}
		
		Super::sendJsonResponse("success", "Location data loaded", $gpsList);
	}	
	
			
	public static function getMeters(){
		try{
			if(Super::hasParam("page")){
			$page = Super::getParam("page");
			}else{
				$page = 1;
			}
			if(Super::hasParam("limit")){
				 $limit  = Super::getParam("limit");
			}else{
				$limit = 20;
			}

			$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
			 
		 // Hitung offset untuk query database
			$offset = ($page - 1) * $limit;
			$set = new Set();
			
			 $objectSet = $set->MboSetRemote("meter")
						  ->MboRemote("meternum") 
						  ->MboRemote("description")
						  ->MboRemote("meterid") 
						  ->order("meternum ASC");
							
				if (!empty($searchTerm)) {
					// klausa WHERE yang mencari di beberapa kolom sekaligus
					$whereClause = "meternum LIKE ? OR description LIKE ? ";
					$likeTerm = '%' . $searchTerm . '%';
					$set->where($whereClause, $likeTerm, $likeTerm);
				}
				 $set->limit("$offset, $limit")->getMboSet();


			// 3. Ubah hasil query menjadi array
			$objects = [];
			if (!$objectSet->isEmpty()) {
				while ($object = $objectSet->fetch()) {
					$objects[] = $object;
				}
			}
			// 4. Kirim sebagai JSON
			// Kita buat struktur respons yang bagus, menyertakan informasi halaman
			$response = [
				'page' => $page,
				'data' => $objects
			];
		   Super::sendJsonResponse("success", "Data Loaded", $response);
		}catch(Exception $e){
			Super::sendJsonResponse("error", $e->getMessage());
		}
	}
	
	public static function saveSelectedMeter(){
		$assetnum = Super::getParam("assetnum");
		$meterid = Super::getParam("meterid");
		$enterby = Super::getParam("enterby");
		$enterdate = Xbo::setEnterDate();
		
		$message = null;
		
		$meternum = (new Set())->useTransactionManager()->MboSetRemote("meter")->where("meterid=?", $meterid)->MboRemote("meternum")->getMboSet()->getMbo()->getString("meternum");
		
		$assetMeterSet = (new Set())->MboSetRemote("assetmeter")->where("assetnum=? AND meternum=?", $assetnum, $meternum)->getMboSet();
		if($assetMeterSet->isEmpty()){
			(new Set())->MboSetRemote("assetmeter")
			->setValue("assetnum", $assetnum)
			->setValue("meternum", $meternum)
			->setValue("enterby", $enterby)
			->setValue("enterdate", $enterdate)
			->add();
			$message = "Meter has been added";
		}else{
			$message = "Meter has been exist";
		}
	
		Super::sendJsonResponse("success", $message);
	}
	
	
	
}// END Class
	

?>