<?php
require_once ("../../../eat.php");

Class ActionSet {
	public static function getAutoNum(){
		$app = Super::getParam("getAutoNum");
		$ticketnum = Super::getParam("ticketnum");
		$ticketDes = (new Set())->MboSetRemote("ticket")->where("ticketnum=?", $ticketnum)->getMboValue("description");
		$getAutoNum = new Super();
		$wonum = $getAutoNum->getAutoNumber($app, "update_next_value");

		$wodescription = "Follow Up Work Order for Ticket [$ticketnum] - $ticketDes";



		Super::sendJsonResponse("success", "Follow Up Work Order for $ticketDes", ["newwonum"=>$wonum, "newdescription" => $wodescription]);
	}
	public static function CreateWOfromTicket(){
		$payload = Super::getParam("createwofromticket");
		$ticketnum = $payload["ticketnum"];
		$newwonum = $payload["newwonum"];
		$newdescription = $payload["newdescription"];

		if(empty($ticketnum)){
			Super::sendJsonResponse("error", "Ticket Number is required.");
		}

		if(empty($newwonum) || empty($newdescription)){
			Super::sendJsonResponse("error", "WO Number AND WO Description is required.");
		}

		$ticketSet = (new Set())->useTransactionManager()->MboSetRemote("ticket")->where("ticketnum=?", $ticketnum)->getMboSet();

		if($ticketSet->isEmpty()){
			Super::sendJsonResponse("error", "Ticket not found or it has been deleted or canceled.");
		}

		$ticket = $ticketSet->fetch();
 

		$assetnum = $ticket["assetnum"];
		$locnum = $ticket["locnum"];
		$customercode = $ticket["customercode"];
		$reportedby = $ticket["reportedby"];
		$reportdate = $ticket["reportdate"];
		$priority = $ticket["priority"];
		$type = $ticket["type"];
		$orgcode = $ticket["orgcode"];
		$sitecode = $ticket["sitecode"];
		$status = $ticket["status"];
		$changedate = Xbo::setEnterDate();
		$changeby = Xbo::getLoginUser();

		if ($status == "cancel" || $status == "completed" || $status == "inprogress") {
			Super::sendJsonResponse("error", "Statuses Cancel, Completed, and In Progress are not allowed for creating a work order.");
		}

		if (empty($reportedby) || empty($reportdate) || empty($priority) || empty($type)) {
			Super::sendJsonResponse("error", "Type, Priority, Reported By, and Reported Date are required.");
		}

		if (empty($assetnum) && empty($locnum) && empty($customercode)) {
			Super::sendJsonResponse("error", "Asset Number, Location, and Customer cannot all be empty. Please select at least one.");
		}
		
		$wostatus = (new Set())->useTransactionManager()->MboSetRemote("mxsattrvalue")->where("name=? AND statuslevel=1", 'workorder')->getMboValue("rootvalue");
		$assetglaccount = (new Set())->useTransactionManager()->MboSetRemote("assets")->where("assetnum=? and orgcode=? and sitecode=?", $assetnum, $orgcode, $sitecode)->getMboValue("glaccount");
		
		(new Set())->useTransactionManager()->MboSetRemote("workorder")
			->setValue("wonum", $newwonum)
			->setValue("description", $newdescription)
			->setValue("assetnum", $assetnum)
			->setValue("location", $locnum)
			->setValue("customercode", $customercode)
			->setValue("priority", $priority)
			->setValue("reportdate", $reportdate)
			->setValue("reportedby", $reportedby)
			->setValue("origrecordid", $ticketnum)
			->setValue("origrecordclass", $type)
			->setValue("orgcode", $orgcode)
			->setValue("glaccount", $assetglaccount)
			->setValue("sitecode", $sitecode)
			->setValue("status", $wostatus)
			->add();		
		
		(new Set())->useTransactionManager()
			->MboSetRemote("workorderstatus")
			->setValue("wonum", $newwonum)
			->setValue("status", $wostatus)
			->setValue("changeby", $changeby)
			->setValue("changedate", $changedate)
			->setValue("remarks", "created from ticket: $ticketnum")
			->add();
		
		(new Set())->useTransactionManager()->MboSetRemote("relatedwoticket")
			->setValue("ticketnum", $ticketnum)
			->setValue("relatedwonum", $newwonum)
			->setValue("enterby", $changeby)
			->setValue("enterdate", $changedate)
			->add();

		//update ticket status to QUEUE
		(new Set())->useTransactionManager()->MboSetRemote("ticket")
			->where("ticketnum=?", $ticketnum)
			->setValue("status", "QUEUE")
			->setValue("statusdate", $changedate)
			->change();

		Super::sendJsonResponse("success", "Work Order $newwonum has been created");
		
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
		
		if($childTable == "relatedwoticket"){
			Super::sendJsonResponse("error", "Cannot Delete followed up Work Order");
		}
	}
}

?>