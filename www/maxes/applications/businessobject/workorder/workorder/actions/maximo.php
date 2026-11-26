<?php
require_once("run.php");
header('Access-Control-Allow-Origin: *');
Class MaximoRun  {
	public static function Run(){
		
		//URL ENDPOINT : https://maxes.id/maxes/applications/businessobject/workorder/workorder/actions/maximo.php
		$json = file_get_contents('php://input');
		// Converts it into a PHP object
		$data = json_decode($json);
		
		
	
		if(isset($data->maximo_add_wonum)){
			$_POST["maximo_add_wonum"] = $data->maximo_add_wonum;
			/*
			{
			  "maximo_add_wonum": "WOMX-001",
			  "description": "Repairing some equipment",
			  "assetnum": "MAXIMO_ASSETNUM",
			  "location": "MAXIMO_LOCATION",
			  "owner": "MAXADMIN",
			  "leader": "MAXADMIN",
			  "targstartdate": "2024-03-08",
			  "targcompdate": "2023-11-12",
			  "priority": "1",
			  "orgcode": "BPI",
			  "sitecode": "BTNG",
			  "status": "WO-RAISE",
			  "workorderid" : "76"
			}
			*/
			
			if(isset($data->description)){
				$_POST["description"] = $data->description;
			}else{
				Set::throws(["msg"=>"Description of Work Order is required, Add 'description' tag to your JSON Format."]);
				return null;
			}
			
			if(isset($data->assetnum)){
				$_POST["assetnum"] = $data->assetnum;
			}else{
				Set::throws(["msg"=>"Asset Num. is required, Add 'assetnum' tag to your JSON Format."]);
				return null;
			}
			
			
			if(isset($data->location)){
				$_POST["location"] = $data->location;
			}else{
				Set::throws(["msg"=>"Location is required, Add 'location' tag to your JSON Format."]);
				return null;
			}
			
			if(isset($data->owner)){
				$_POST["owner"] = $data->owner;
			}else{
				Set::throws(["msg"=>"Owner is required, Add 'owner' tag to your JSON Format."]);
				return null;
			}
			
			if(isset($data->leader)){
				$_POST["leader"] = $data->leader;
			}else{
				Set::throws(["msg"=>"Leader is required, Add 'leader' tag to your JSON Format."]);
				return null;
			}
			
			if(isset($data->targstartdate)){
				$_POST["targstartdate"] = $data->targstartdate;
			}else{
				Set::throws(["msg"=>"Target Start Date is required, Add 'targstartdate' tag to your JSON Format."]);
				return null;
			}
			
			if(isset($data->targcompdate)){
				$_POST["targcompdate"] = $data->targcompdate;
			}else{
				Set::throws(["msg"=>"Target Completion Date is required, Add 'targcompdate' tag to your JSON Format."]);
				return null;
			}
			
			if(isset($data->priority)){
				$_POST["priority"] = $data->priority;
			}else{
				Set::throws(["msg"=>"Priority of Work Order is required, Add 'priority' tag to your JSON Format."]);
				return null;
			}
			
			if(isset($data->status)){
				$_POST["status"] = $data->status;
			}else{
				Set::throws(["msg"=>"Status of Work Order is required, Add 'status' tag to your JSON Format."]);
				return null;
			}
			
			
			if(isset($data->status)){
				$_POST["status"] = $data->status;
			}else{
				Set::throws(["msg"=>"Status of Work Order is required, Add 'status' tag to your JSON Format."]);
				return null;
			}
			
			
			if(isset($data->orgcode)){
				$_POST["orgcode"] = $data->orgcode;
			}else{
				Set::throws(["msg"=>"Organization is required, Add 'orgcode' tag to your JSON Format."]);
				return null;
			}
			
			
			if(isset($data->sitecode)){
				$_POST["sitecode"] = $data->sitecode;
			}else{
				Set::throws(["msg"=>"Site is required, Add 'sitecode' tag to your JSON Format."]);
				return null;
			}
			
			if(isset($data->workorderid)){
				$_POST["maximo_workorderid"] = $data->workorderid;
			}else{
				Set::throws(["msg"=>"WORKORDERID is required, Add 'workorderid' tag to your JSON Format."]);
				return null;
			}
			
			Action::Run();
		}
		
		
		if(isset($data->maximo_add_woplanmaterial)){
			/*
					
				  {
					"maximo_add_woplanmaterial": "WOMX-001",
					"invreservenum": "1001-01",
					"itemnum": "maximo_item_002_CT",
					"warehouse": "WH-STR02",
					"racknum": "FLOOR",
					"conditiontype": "NEW",
					"quantity": "10",
					"unitcost": "1000",
					"linecost": "10000",
					"enterby": "MXINTADM",
					"remarks": "Remarks"
				  }
			*/
			
			$_POST["maximo_add_woplanmaterial"] = $data->maximo_add_woplanmaterial;
			$_POST["wonum"] = $data->maximo_add_woplanmaterial;
			
			if(isset($data->invreservenum)){
				$_POST["invreservenum"] = $data->invreservenum;
			}else{
				Set::throws(["msg"=>"Inventory Reserve Number is required, Add 'invreservenum' tag to your JSON Format."]);
				return null;
			}
			
			if(isset($data->itemnum)){
				$_POST["itemnum"] = $data->itemnum;
			}else{
				Set::throws(["msg"=>"Item Number is required, Add 'itemnum' tag to your JSON Format."]);
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
				Set::throws(["msg"=>"Rack / Shelves / Bin is required, Add 'racknum' tag to your JSON Format."]);
				return null;
			}
			
			if(isset($data->conditiontype)){
				$_POST["conditiontype"] = $data->conditiontype;
			}else{
				Set::throws(["msg"=>"Condition Type is required, Add 'conditiontype' tag to your JSON Format."]);
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
			
			if(isset($data->enterby)){
				$_POST["enterby"] = $data->enterby;
			}else{
				Set::throws(["msg"=>"Enter By is required, Add 'enterby' tag to your JSON Format."]);
				return null;
			}
			
			
			if(isset($data->remarks)){
				$_POST["remarks"] = $data->enterby;
			}else{
				Set::throws(["msg"=>"Remarks is required, Add 'remarks' tag to your JSON Format."]);
				return null;
			}
			
			
			$_POST["glcreditacct"] = "888";
			$_POST["gldebitacct"] = "999";
			
			Action::Run();
			
		}
		
		
	
	}
}


MaximoRun::Run();


		
		
?>