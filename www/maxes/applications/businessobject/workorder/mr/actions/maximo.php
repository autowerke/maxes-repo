<?php
require_once("run.php");
header('Access-Control-Allow-Origin: *');
Class MaximoRun  {
	public static function Run(){
		
		/*
		MAXES MAXIMO API REQUEST  v1.0 : ADD MR 
		URL https://maxes.id/maxes/applications/businessobject/workorder/mr/actions/maximo.php
		{
		  "maximo_add_mrnum": "MR-MAX-001",
		  "description": "API Request from Maximo",
		  "invreservenum": "INVRESERVE-001",
		  "status": "reviewed",
		  "statusdate": "2024-03-18 23:30",
		  "wonum": "WOMX-001",
		  "requestedby": "maxesadmin",
		  "requesteddate": "2024-03-18 23:30",
		  "enterby": "maxesadmin",
		  "enterdate": "2024-03-18 23:30",
		  "orgcode": "BPI",
		  "sitecode": "BTNG",
		  "mrid" : "4"
		}
		*/
		
		
		$json = file_get_contents('php://input');
		$data = json_decode($json);
		if(isset($data->maximo_add_mrnum)){
			$_POST["maximo_add_mrnum"] = $data->maximo_add_mrnum;
			
			if(isset($data->description)){
				$_POST["description"] = $data->description;
			}else{
				Set::throws(["msg"=>"Description of MR is required, Add 'description' tag to your JSON Format."]);
				return null;
			}
			
			if(isset($data->invreservenum)){
				$_POST["invreservenum"] = $data->description;
			}else{
				Set::throws(["msg"=>"Number of Inventory Reserve required, Add 'invreservenum' tag to your JSON Format."]);
				return null;
			}
			
			if(isset($data->status)){
				$_POST["status"] = $data->status;
			}else{
				Set::throws(["msg"=>"Status of MR is required, Add 'status' tag to your JSON Format."]);
				return null;
			}
			
			
			if(isset($data->statusdate)){
				$_POST["statusdate"] = $data->statusdate;
			}else{
				Set::throws(["msg"=>"Status Date of MR is required, Add 'statusdate' tag to your JSON Format."]);
				return null;
			}
			
			
			if(isset($data->wonum)){
				$_POST["wonum"] = $data->wonum;
			}else{
				Set::throws(["msg"=>"Work Order Number is required, Add 'wonum' tag to your JSON Format."]);
				return null;
			}
			
			
			if(isset($data->requestedby)){
				$_POST["requestedby"] = $data->requestedby;
			}else{
				Set::throws(["msg"=>"Requested By of MR is required, Add 'requestedby' tag to your JSON Format."]);
				return null;
			}
			
			
			if(isset($data->requesteddate)){
				$_POST["requesteddate"] = $data->requesteddate;
			}else{
				Set::throws(["msg"=>"Requested Date of MR is required, Add 'requesteddate' tag to your JSON Format."]);
				return null;
			}
			
			
			if(isset($data->enterby)){
				$_POST["enterby"] = $data->enterby;
			}else{
				Set::throws(["msg"=>"Enter By of MR is required, Add 'enterby' tag to your JSON Format."]);
				return null;
			}
			
			
			if(isset($data->enterdate)){
				$_POST["enterdate"] = $data->enterdate;
			}else{
				Set::throws(["msg"=>"Enter Date of MR is required, Add 'enterdate' tag to your JSON Format."]);
				return null;
			}
			
			
			if(isset($data->orgcode)){
				$_POST["orgcode"] = $data->orgcode;
			}else{
				Set::throws(["msg"=>"Organization Code is required, Add 'orgcode' tag to your JSON Format."]);
				return null;
			}
			
			
			if(isset($data->sitecode)){
				$_POST["sitecode"] = $data->sitecode;
			}else{
				Set::throws(["msg"=>"Site Code is required, Add 'sitecode' tag to your JSON Format."]);
				return null;
			}
			
			
			if(isset($data->mrid)){
				$_POST["mrid"] = $data->mrid;
			}else{
				Set::throws(["msg"=>"MR ID is required, Add 'mrid' tag to your JSON Format."]);
				return null;
			}
			
			
			Action::Run();
		}
		
		if(isset($data->maximo_add_mrmaterial)){
			
			
			/*
			MAXES MAXIMO API REQUEST : MR LINE
			URL : https://maxes.id/maxes/applications/businessobject/workorder/mr/actions/maximo.php
			{
				  "maximo_add_mrmaterial": "MR-MAX-001",
				  "wonum": "WOMX-001",
				  "invreservenum": "INVRESERVE-001",
				  "itemnum": "maximo_item_001_CT",
				  "quantity": 2,
				  "unitcost": 3300,
				  "linecost": 6600,
				  "conditiontype": "NEW",
				  "warehouse": "WH-STR02",
				  "racknum": "FLOOR",
				  "enterby": "maxesadmin",
				  "enterdate": "2024-03-18 22:30",
				  "orgcode" : "BPI",
				  "sitecode" : "BTNG"
				}
			*/
			$_POST["maximo_add_mrmaterial"] = $data->maximo_add_mrmaterial;
			
			if(isset($data->wonum)){
				$_POST["wonum"] = $data->wonum;
			}else{
				Set::throws(["msg"=>"Work Order Number is required, Add 'wonum' tag to your JSON Format."]);
				return null;
			}
			
			if(isset($data->invreservenum)){
				$_POST["invreservenum"] = $data->invreservenum;
			}else{
				Set::throws(["msg"=>"Number of Inventory Reserve required, Add 'invreservenum' tag to your JSON Format."]);
				return null;
			}
			
			if(isset($data->itemnum)){
				$_POST["itemnum"] = $data->itemnum;
			}else{
				Set::throws(["msg"=>"Item Number is required, Add 'itemnum' tag to your JSON Format."]);
				return null;
			}
			
			
			if(isset($data->quantity)){
				$_POST["quantity"] = $data->quantity;
			}else{
				Set::throws(["msg"=>"Quantity is required, Add 'quantity' tag to your JSON Format."]);
				return null;
			}
			
			
			if(isset($data->unitcost)){
				$_POST["unitcost"] = $data->unitcost;
			}else{
				Set::throws(["msg"=>"Unit Cost is required, Add 'unitcost' tag to your JSON Format."]);
				return null;
			}
			
			
			
			if(isset($data->linecost)){
				$_POST["linecost"] = $data->linecost;
			}else{
				Set::throws(["msg"=>"Line Cost is required, Add 'linecost' tag to your JSON Format."]);
				return null;
			}
			
			
			if(isset($data->conditiontype)){
				$_POST["conditiontype"] = $data->conditiontype;
			}else{
				Set::throws(["msg"=>"Condition Type is required, Add 'conditiontype' tag to your JSON Format."]);
				return null;
			}
			
			
			
			if(isset($data->warehouse)){
				$_POST["warehouse"] = $data->warehouse;
			}else{
				Set::throws(["msg"=>"Warehouse is required, Add 'warehouse' tag to your JSON Format."]);
				return null;
			}
			
			if(isset($data->racknum)){
				$_POST["racknum"] = $data->racknum;
			}else{
				Set::throws(["msg"=>"Rack Number is required, Add 'racknum' tag to your JSON Format."]);
				return null;
			}
			
			
			if(isset($data->enterby)){
				$_POST["enterby"] = $data->enterby;
			}else{
				Set::throws(["msg"=>"Enter By is required, Add 'enterby' tag to your JSON Format."]);
				return null;
			}
			
			if(isset($data->enterdate)){
				$_POST["enterdate"] = $data->enterdate;
			}else{
				Set::throws(["msg"=>"Enter Date is required, Add 'enterdate' tag to your JSON Format."]);
				return null;
			}
			
			Action::Run();
			
		}
	}
}
MaximoRun::Run();


		
		
?>