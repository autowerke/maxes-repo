<?php 
	header("Content-Type: application/json; charset=UTF-8");
	header("Access-Control-Allow-Origin: *");
	//require objects action
	require_once ("actions.php");
	
	class API_Locations{
		public static function getLocationList(){
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
				
				 $objectSet = $set->useTransactionManager()->MboSetRemote("locations")
								->MboRemote("locnum") 
							  ->MboRemote("description")     
							  ->MboRemote("status") 
							  ->MboRemote("type")  
							  ->MboRemote("shape_coordinates")
							  ->MboRemote("longitude")
							  ->MboRemote("latitude");
					if (!empty($searchTerm)) {
						// klausa WHERE yang mencari di beberapa kolom sekaligus
						$whereClause = "locnum LIKE ? OR status LIKE ? OR type LIKE ?";
						$likeTerm = '%' . $searchTerm . '%';
						$set->where($whereClause, $likeTerm, $likeTerm, $likeTerm);
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
		
		public static function getLocationGpsList(){
		$searchTerm = Super::getParam("search");

		$gpsList = [];
		$set = new Set();
		$locationSet = $set->useTransactionManager()
			->MboSetRemote("locations");

		// default where (ada koordinat atau shape)
		$baseWhere = "shape_coordinates IS NOT NULL OR (longitude IS NOT NULL OR latitude IS NOT NULL)";

		if (!empty($searchTerm)) {
			// tambahin filter searching
			$whereClause = "($baseWhere) AND (locnum LIKE ? OR description LIKE ?)";
			$likeTerm = "%" . $searchTerm . "%";
			$set->where($whereClause, $likeTerm, $likeTerm);
		} else {
			$set->where($baseWhere);
		}

		$locationSet->MboRemote("locnum, description, longitude, latitude, shape_coordinates")->getMboSet();

		while ($pt = $locationSet->fetch()) {
			$ss = new stdClass;
			$ss->locnum = $pt["locnum"];
			$ss->description = $pt["description"];
			$ss->latitude = $pt["latitude"];
			$ss->longitude = $pt["longitude"];
			$ss->shape_coordinates = $pt["shape_coordinates"];
			$gpsList[] = $ss;
		}

		Super::sendJsonResponse("success", "Location data loaded", $gpsList);
	}

		
	}// END Class
	
	
	
	$action = Super::getParam("action");
	switch ($action) {
	
    case 'getLocationGpsList':
		API_Locations::getLocationGpsList();
		break;
		
    case 'getLocationList':
        API_Locations::getLocationList();
        break;
    default:
        Super::sendJsonResponse("error", "Invalid or missing 'action' parameter.");
        break;
}
?>