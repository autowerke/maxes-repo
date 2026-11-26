<?php
require_once("run.php");
header('Access-Control-Allow-Origin: *');
Class MaximoRun  {
	public static function Run(){
		$json = file_get_contents('php://input');
		$data = json_decode($json);
		if(isset($data->mapAddToInventory)){
			$_POST["mapAddToInventory"] = $data->mapAddToInventory;
			
			if(isset($data->conditiontype)) $_POST["conditiontype"] = $data->conditiontype; 
			else $_POST["conditiontype"] = null;
			
			if(isset($data->conditionrate)) $_POST["conditionrate"] = $data->conditionrate; 
			else $_POST["conditionrate"] = 100;
			
			
			if(isset($data->defrack)) $_POST["defrack"] = $data->defrack; 
			else $_POST["defrack"] = null;
			
			if(isset($data->racknum)) {
				$_POST["racknum"] = $data->racknum; 
				$_POST["defrack"] = $data->racknum; 
			}
			else {
				$_POST["racknum"] = null;
				$_POST["defrack"] = "";
			}
			
			$_POST["sitecode"] = $data->sitecode;
			$_POST["orgcode"] = $data->orgcode; 
			$_POST["orderunit"] = $data->orderunit; 
			$_POST["issueunit"] = $data->issueunit; 
			$_POST["description"] = $data->description; 
				
			if(isset($data->brand))  $_POST["brand"] = $data->brand; 
			else $_POST["brand"] = null;
			
			if(isset($data->description)) $_POST["description"] = $data->description; 
			else $_POST["description"] = null;
			
			if(isset($data->spectext)) $_POST["spectext"] = $data->spectext; 
			else $_POST["spectext"] = null;
			
			$_POST["warehouse"] = $data->warehouse;
			$_POST["curbal"] = $data->curbal;
			$_POST["unitcost"] = $data->unitcost;
			$_POST["issuetype"] = "AVG";
			$_POST["batchnum"] = Xbo::GetBatchNum();
			Action::Run();
		}
	}
}
MaximoRun::Run();


		
		
?>