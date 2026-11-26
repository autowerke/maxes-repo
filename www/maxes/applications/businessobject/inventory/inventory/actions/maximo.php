<?php
require_once("run.php");
header('Access-Control-Allow-Origin: *');
Class MaximoRun  {
	public static function Run(){
		$json = file_get_contents('php://input');
		$data = json_decode($json);
		
		if(isset($data->updateFifoLifoPhysQty)){
			$_POST["updateFifoLifoPhysQty"] = $data->updateFifoLifoPhysQty;
			$_POST["orgcode"] = $data->orgcode;
			$_POST["sitecode"] = $data->sitecode;
			
			if(isset($_POST["changeby"])) $_POST["changeby"] = $data->changeby; 
			else $_POST["changeby"] = null;
			
			Action::Run();
		}
		
		if(isset($data->maximoUpdateCurrentBalance)){
			$_POST["maximoUpdateCurrentBalance"] = $data->maximoUpdateCurrentBalance;
			$_POST["warehouse"] = $data->warehouse;
			
			if(isset($data->racknum)) $_POST["racknum"] = $data->racknum;
			else $_POST["racknum"] = null;
						
			if(isset($data->expireddate)) $_POST["expireddate"] = $data->expireddate;
			else $_POST["expireddate"] = null;
			
			if(isset($data->conditiontype)) $_POST["conditiontype"] = $data->conditiontype;
			else $_POST["conditiontype"] = null;
			$_POST["orgcode"] = $data->orgcode;
			$_POST["sitecode"] = $data->sitecode;
			$_POST["newcurbal"] = $data->newcurbal;
			$_POST["synfifolifoqty"] = "Y";
			Action::Run();
		}
		
		if(isset($data->updateInvReserveOnItemnum)){
			$_POST["updateInvReserveOnItemnum"] = $data->updateInvReserveOnItemnum;
			$_POST["warehouse"] = $data->warehouse;
			if(isset($data->racknum)) $_POST["racknum"] = $data->racknum;
			else $_POST["racknum"] = null;
			if(isset($data->conditiontype)) $_POST["conditiontype"] = $data->conditiontype;
			else $_POST["conditiontype"] = null;
			if(isset($data->expireddate)) $_POST["expireddate"] = $data->expireddate;
			else $_POST["expireddate"] = null;
			$_POST["mrnum"] = $data->mrnum;
			$_POST["wonum"] = $data->wonum;
			$_POST["issuedqty"] = $data->issuedqty;
			$_POST["invreservenum"] = $data->invreservenum;
			Action::Run();
			
		}
	}
}
MaximoRun::Run();


		
		
?>