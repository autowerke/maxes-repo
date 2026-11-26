<?php
require_once ("../../../eat.php");

Class ActionSet {

	public static function updateLastRevisedNum(){
		$payload = Super::getParam("updateLastRevisedNum");
	}
	
	public static function validateStatusChange(){
		$payload = Super::getParam("validateStatusChange");
		$new_status = strtolower($payload["new_status"]);
		$ticketnum = $payload["ticketnum"];

		if($new_status == "cancel"){
			Super::sendJsonResponse("success", "Ticket changed to $new_status");
		}else{
			$ticketSet = (new Set())->MboSetRemote("ticket")->where("ticketnum=?", $ticketnum)->getMboSet();
			$tick = $ticketSet->getMbo();
			$locnum = $tick->getString("locnum");
			$assetnum = $tick->getString("assetnum");
			$customercode = $tick->getString("customercode");
			$reportedby = $tick->getString("reportedby");
			$reportdate = $tick->getString("reportdate");
		
			
			if(empty($locnum) && empty($assetnum) && empty($customercode)){
				Super::sendJsonResponse("error", "At least one of the following must be filled: Asset, Location, or Customer");
			}


			if(empty($reportedby)){
				Super::sendJsonResponse("error", "Reported By cannot be null");
			}
			if(empty($reportdate)){
				Super::sendJsonResponse("error", "Reported Date cannot be null");
			}

			Super::sendJsonResponse("success", "Validation Passed Success");
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
		
	}
}

?>