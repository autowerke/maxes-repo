<?php 
	header("Content-Type: application/json; charset=UTF-8");
	header("Access-Control-Allow-Origin: *");
	//require objects action
	require_once ("actions.php");
	
	class API_People{
		public static function getPeopleList(){
			try {
				$page  = Super::hasParam("page") ? Super::getParam("page") : 1;
				$limit = Super::hasParam("limit") ? Super::getParam("limit") : 20;
				$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

				$offset = ($page - 1) * $limit;
				$set = new Set();

				$objectSet = $set->MboSetRemote("employees em")
				->setRelationship("fileinfo fi", "fi.ownerid = em.employeesid AND fi.ownertable='employees' AND fi.filedefault='Y'")
				->MboRemote("em.personid")
				->MboRemote("em.firstname")
				->MboRemote("em.lastname")
				->MboRemote("em.fullname")
				->MboRemote("em.citizenid")
				->MboRemote("em.primaryphone")
				->MboRemote("em.healthrate")
				->MboRemote("fi.filename")
				->order("em.personid ASC");

				//$baseWhere = "fi.ownertable='employees' AND fi.filedefault='Y'";

				if (!empty($searchTerm)) {
					// gabungkan dengan kondisi pencarian
					$whereClause = "$baseWhere AND (em.personid LIKE ? OR em.fullname LIKE ? OR em.lastname LIKE ? OR em.primaryphone LIKE ? OR em.citizenid LIKE ?)";
					$likeTerm = "%" . $searchTerm . "%";
					$set->where($whereClause, $likeTerm, $likeTerm, $likeTerm, $likeTerm, $likeTerm);
				} else {
					//$set->where($baseWhere);
				}
				$objects = [];	
				$set->limit("$offset, $limit")->getMboSet();
				$fsname = ContextPath::getFullHostName();
				$thumbPath = $fsname . "/attachments/thumbs/";
				$prefix = "thumb_";
				if (!$objectSet->isEmpty()) {
					while ($object = $objectSet->fetch()) {
						// buat salinan object (biar aman)
						$obj = $object;

						// ambil filename asli
						$filename = $object["filename"];

						if (!empty($filename)) {
							// update filename jadi full path
							$obj["filename"] = $thumbPath . $prefix . $filename;
						}

						$objects[] = $obj;
					}
				}

				$response = [
					'page' => $page,
					'data' => $objects
				];
				Super::sendJsonResponse("success", "Data Loaded", $response);
			} catch (Exception $e) {
				Super::sendJsonResponse("error", $e->getMessage());
			}
		}

	
		
		public static function getPeopleGpsList() {
			$fsname = ContextPath::getFullHostName();
			$thumbPath = $fsname . "/attachments/thumbs/";
			$prefix = "thumb_";

			$searchTerm = Super::getParam("search");

			$gpsList = [];
			
			$set = new Set();
			$persontrackingSet = $set->useTransactionManager()->MboSetRemote("persontracking pt")
								   ->setRelationship("employees em", "pt.personid = em.personid");

			if (!empty($searchTerm)) {
				// Cari berdasarkan personid (di tabel persontracking) atau nama (di tabel employees)
				$whereClause = "pt.personid LIKE ? OR em.fullname LIKE ?";
				$likeTerm = '%' . $searchTerm . '%';
				$set->where($whereClause, $likeTerm, $likeTerm);
			}

			// Eksekusi query setelah semua kondisi diterapkan
			$persontrackingSet->MboRemote("pt.personid, pt.latitude, pt.longitude, pt.speed, pt.timestamp, em.employeesid as ownerid, em.fullname, em.healthrate, em.primaryphone, em.primaryemail")
							  ->getMboSet();
			
			while($pt = $persontrackingSet->fetch()){
					
				$ownerid = $pt["ownerid"];
				$filename = (new Set())->useTransactionManager()->MboSetRemote("fileinfo")->setWhere("ownerid = '$ownerid' and ownertable = 'employees' and filedefault='Y'")->MboRemote("filename")->getMboSet()->getMbo()->getString("filename");
				
				if($filename){
					$thumbnail_path = $thumbPath.$prefix.$filename;
				}else{
					$thumbnail_path = null;
				}
				
				$ss = new stdClass;
				$ss->personid = $pt["personid"];
				$ss->latitude = $pt["latitude"];
				$ss->longitude = $pt["longitude"];
				$ss->speed = $pt["speed"];
				$ss->timestamp = $pt["timestamp"];
				$ss->healthrate = $pt["healthrate"];
				$ss->primaryphone = $pt["primaryphone"];
				$ss->primaryemail = $pt["primaryemail"];
				$ss->thumbnail_path = $thumbnail_path;
				$gpsList [] = $ss;
			}
			
			Super::sendJsonResponse("success", "Location data loaded", $gpsList);
		}
		/*  Reference Function */
		public static function getPeopleMarker(){
			$fsname = ContextPath::getFullHostName();
			$thumbPath = $fsname . "/attachments/thumbs/";
			$prefix = "thumb_";
			
			$peopleSet = (new Set())->useTransactionManager()->MboSetRemote("employees")->MboRemote("personid, employeesid")->getMboSet();
			
			$people_image = [];
			while ($people = $peopleSet->fetch()){
				$personid = $people["personid"];
				$ownerid = $people["employeesid"];
				$filename = (new Set())->useTransactionManager()->MboSetRemote("fileinfo")->setWhere("ownerid = '$ownerid' and ownertable = 'employees'")->MboRemote("filename")->getMboSet()->getMbo()->getString("filename");
				$thumbnail_path = $thumbPath.".".$prefix.".".$imgid;
				
				$person = new stdClass;
				$person->personid = $personid;
				$person->ownerid = $ownerid;
				$person->thumbnail_path = $thumbnail_path; 
				
				$people_image[] = $person;
				
			}
			
			Super::sendJsonResponse("success", "Location data loaded", $people_image);
		}	
		
	public static function getPeopleDetail()
	{
		try {
			$fsname = ContextPath::getFullHostName();
			$thumbPath = $fsname . "/attachments/thumbs/";
			$prefix = "thumb_";
			
			$personid = Super::getParam("personid");
			if (empty($personid)) {
				throw new InvalidArgumentException("Person ID tidak diberikan.");
			}

			$personSet = (new Set())->MboSetRemote("employees")
								   ->where("personid = ?", $personid)
								   ->MboRemote("personid, employeesid, fullname, citizenid, primaryphone, primaryemail, dateofbirth, address, city, stateprovince, zippostalcode, country, healthrate")
								   ->getMboSet();

			$personData = $personSet->fetch();
			
			if ($personData) {
				$person = (object)$personData;
				$person->ownerid = $person->employeesid;
				unset($person->employeesid); 

				$fileInfoSet = (new Set())->MboSetRemote("fileinfo")
										  ->where("ownerid = ? AND ownertable = 'employees'", $person->ownerid)
										  ->MboRemote("filename")
										  ->getMboSet();
				
				$fileInfo = $fileInfoSet->fetch();
				
				if ($fileInfo && !empty($fileInfo['filename'])) {
					$person->thumbnail_path = $thumbPath . $prefix . $fileInfo['filename'];
				} else {
					
					$person->thumbnail_path = null;
				}

				Super::sendJsonResponse("success", "Detail person successfully retrieved", $person);

			} else {
				
				Super::sendJsonResponse("error", "Person dengan ID '$personid' tidak ditemukan.", null);
			}

		} catch (Exception $e) {
			Super::sendJsonResponse("error", "Terjadi kesalahan: " . $e->getMessage(), null);
		}
	}
	
	
	public static function getHealthReading(){
		$meterReadings = [];
			try {
				$personid = Super::getParam("personid");
				if (empty($personid)) {
					throw new Exception("Parameter 'personid' is required.");
				}
				
				$set = new Set();
				$meterSet = $set->MboSetRemote("personmeter")
							   ->where("personid = ?", $personid)
							   ->order("lastreadingdate DESC") // Urutkan dari yang terbaru
							   ->getMboSet();

				// fetchAll() adalah fungsi yang sangat berguna dari kitab sucimu
				$meterReadings = $meterSet->fetchData(); 
			
				Super::sendJsonResponse("success", "Health readings loaded", $meterReadings);

			} catch (Exception $e) {
				Super::sendJsonResponse("error", $e->getMessage());
			}
		
	}
	
	public static function updatePersonLocation(){
		$personid = Super::getParam("personid");
		$latitude = Super::getParam("latitude");
		$longitude = Super::getParam("longitude");
		
		if (empty($personid)) {
			Super::sendJsonResponse("error", "Person ID is required.");
			return;
		}
		
		 $set = new Set();
		 $persontrackingSet = $set->MboSetRemote("persontracking")->useTransactionManager()->where("personid = ?", $personid)->getMboSet();
		 
		 if($persontrackingSet->isEmpty()){
			(new Set())->useTransactionManager()->MboSetRemote("persontracking")
			->setValue("personid", $personid)
			->setValue("latitude", $latitude)
			->setValue("longitude", $longitude)
			->setValue("timestamp", date('Y-m-d H:i:s'))
			->add();
		 }else{
			(new Set())->useTransactionManager()->MboSetRemote("persontracking")->where("personid = ?", $personid)
			->setValue("latitude", $latitude)
			->setValue("longitude", $longitude)
			->setValue("timestamp", date('Y-m-d H:i:s'))
			->change();
		 }
		
		Super::sendJsonResponse("success", "Location updated for $personid");
		
		
	}
	
	
}// END Class
	
	
	
	$action = Super::getParam("action");
	switch ($action) {
	case 'updatePersonLocation':
		API_People::updatePersonLocation();
		break;	
	case 'getHealthReading':
		API_People::getHealthReading();
		break;
	case 'getPeopleDetail': 
		API_People::getPeopleDetail();
		break;
	case 'getPeopleMarker': 
		API_People::getPeopleMarker();
		break;
    case 'getPeopleList':
        API_People::getPeopleList();
        break;
   case 'getPeopleGpsList':
		API_People::getPeopleGpsList();
		break;
   
    default:
        Super::sendJsonResponse("error", "Invalid or missing 'action' parameter.");
        break;
}
?>