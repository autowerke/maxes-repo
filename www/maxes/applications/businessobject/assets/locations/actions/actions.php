<?php
require_once ("../../../eat.php");
require_once ("dataloader.php");
Class ActionSet {
	
	public static function getAllLocations(){
		$locationSet = (new Set())->useTransactionManager()->MboSetRemote("locations l")->MboRemote("l.locnum, l.description, l.shape_coordinates, l.locationsid")
		->where("l.status = ? and l.shape_coordinates IS NOT NULL", "active")->getMboSet();
		
		$locations = [];
		if($locationSet->isEmpty()){
			Super::sendJsonResponse("success", "No Map to Display");
		}else{
			while($loc = $locationSet->fetch()){
				$locnum = $loc["locnum"];
				$description = $loc["description"];
				$shape_coordinates = $loc["shape_coordinates"];
				$locationsid = $loc["locationsid"];
				
				$locationMeterSet = (new Set())->useTransactionManager()->MboSetRemote("locationmeter")->where("unit=? and locnum=?", "ph", $locnum)->MboRemote("lastreading")->getMboSet();
				$locMeter = $locationMeterSet->getMbo();
				$lastreading = $locMeter->getString("lastreading");
				
			
				$locations [] = [
					"locnum" => $locnum,
					"description" => $description,
					"shape_coordinates" => $shape_coordinates,
					'fertility_value' => (float) $lastreading,
					'locationsid' =>$locationsid
				];
			}
			Super::sendJsonResponse("success", $locations , $locations);
		
		}
	}

	public static function getGeoLocation(){
		
		$locnum = Super::getParam("getGeoLocation");

		if (empty($locnum)) {
			Super::sendJsonResponse("error", "Location Number cannot be empty");
			return; // Hentikan eksekusi
		}
		
		$set = new Set();
		
		$locationSet = $set->useTransactionManager()->MboSetRemote("locations l")->MboRemote("l.locnum, l.description, l.latitude, l.longitude, l.shape_coordinates")
		->where("l.locnum= ? AND l.status = ? ", $locnum, "active")->getMboSet();
			
		if (!$locationSet->isEmpty()) {

			$result = $locationSet->getMbo(); 
			$geometry = json_decode($result->getString('shape_coordinates')); 

			if (json_last_error() !== JSON_ERROR_NONE) {
				Super::sendJsonResponse("error", "Data: shape_coordinates for locnum " . $locnum . " is not valid.");
				return;
			}
			
			$locationMeterSet = (new Set())->useTransactionManager()->MboSetRemote("locationmeter")->where("unit=? and locnum=?", "ph", $locnum)->MboRemote("lastreading")->getMboSet();
			$locMeter = $locationMeterSet->getMbo();
			$lastreading = $locMeter->getString("lastreading");
			
			$feature = [
				'type' => 'Feature',
				'properties' => [
					'locnum'          => $result->getString('locnum'),
					'description'     => $result->getString('description'),
					'fertility_value' => (float) $lastreading
				],
				'geometry' => $geometry
			];
			
			$geoJsonData = [
				'type' => 'FeatureCollection',
				'features' => [$feature] // Masukkan $feature ke dalam sebuah array
			];
			
			Super::sendJsonResponse("success", $geoJsonData);

		} else {
			Super::sendJsonResponse("error", "Location Data for " . $locnum . " is not found or it is not active.");
		}
	}
	
	
	public static function EnterMeterReading(){
    
		$payload = Super::getParam();
		$mtrd = $payload["entermeterreading"];
		$locnum = $payload["locnum"];

		if(empty($locnum)){
			Super::sendJsonResponse("error", "Asset Number is required");
		}	
		
		if(empty($mtrd)){
			Super::sendJsonResponse("error", "Cannot find value of the reading");
		}
		
		$countmtrdata = count($mtrd);
		$enterdate = Xbo::setEnterDate();
		$enterby = Xbo::getLoginUser();
		
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
				$historySet = (new Set())->useTransactionManager()->MboSetRemote("locationmeterhistory")
					->setWhere("locnum='$locnum' and meternum='$meternum' and readingdate = (select max(readingdate) from locationmeterhistory where locnum='$locnum' and meternum='$meternum')")
					->MboRemote("value")->MboRemote("readingdate")->getMboSet();
				
				$lastreadingdate = null;
				$lastreadingValue = 0;

				if(!$historySet->isEmpty()){
					$history = $historySet->getMbo();
					$lastreadingdate = $history->getString("readingdate");
					$lastreadingValue = $history->getString("value");
				}
				
				if($delta == "true" && $historySet->isEmpty()){
					Super::sendJsonResponse("error", "Delta reading mode is selected, but no prior meter reading was found for $meternum. <br> Please enter an initial value before proceeding.");
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
					(new Set())->useTransactionManager()->MboSetRemote("locationmeterhistory")
						->setWhere("locnum='$locnum' and meternum='$meternum' and readingdate='$lastreadingdate'")
						->delete();
				}
				
				// Baik itu koreksi atau data baru, kita selalu INSERT record history yang baru
				(new Set())->useTransactionManager()->MboSetRemote("locationmeterhistory")
					->setValue("locnum", $locnum)
					->setValue("meternum", $meternum)
					->setValue("metertype", $metertype)
					->setValue("delta", $isDelta)
					->setValue("deltavalue", $variance)
					->setValue("value", $new_value)
					->setValue("readingdate", $readingdate)
					->setValue("enterby", $enterby)
					->setValue("enterdate", $enterdate)
					->add();
				
				// Selalu update tabel master 'locationmeter' dengan data terbaru
				(new Set())->useTransactionManager()->MboSetRemote("locationmeter")
					->setWhere("locnum='$locnum' and meternum='$meternum'")
					->setValue("lastreading", $new_value)
					->setValue("lastreadingdate", $readingdate) // Selalu update dengan tanggal baru
					->setValue("enterby", $enterby)
					->setValue("enterdate", $enterdate)
					->change();
				
			}
			
			// 2. Commit transaksi SETELAH semua loop selesai
			$tm->commit();
			self::sendJsonResponse("success", "Location Meter Reading has been updated.");

		} catch(Exception $e) {
			// 3. Rollback
			$tm->rollback();
			throw $e;
		}
	}
	
	
	
	public static function AddExistingMeters(){
		
		$payload = Super::getParam();
		$locnum = $payload["locnum"];
		$meters = $payload["addmeters"];
		
		$enterby = Xbo::getLoginUser();
		$enterdate = Xbo::setenterdate();
		$tm = TransactionManager::getInstance();
		$tm->beginTransaction();
		try{
			for($i=0; $i<count($meters); $i++){
				$meternum = $meters[$i]["meternum"];
				$whereclause = "meternum='$meternum' and locnum='$locnum'";
				$assetMeterSet = (new Set())->useTransactionManager()->MboSetRemote("locationmeter")
				->setWhere($whereclause)->MboRemote("meternum")->getMboSet();
				
				if($assetMeterSet->isEmpty()){
					$metertype = (new Set())->useTransactionManager()->MboSetRemote("meter")->setWhere("meternum='$meternum'")->MboRemote("metertype")->getMboSet()->getMbo()->getString("metertype");
					(new Set())->useTransactionManager()->MboSetRemote("locationmeter")
					->setValue("locnum", $locnum)
					->setValue("meternum", $meternum)
					->setValue("enterby", $enterby)
					->setValue("enterdate", $enterdate)
					->add();

				}
			
			}
			$tm->commit();
			Super::sendJsonResponse("success", "Location Meter has been added");
		}catch(Exception $e){
			$tm->rollback();
			throw $e;
		}
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
		
		
		Super::sendJsonResponse("error", "You cannot delete the records for Location properties and historical transactions! Contact your Administrator for further actions." );
	}
}
	

?>