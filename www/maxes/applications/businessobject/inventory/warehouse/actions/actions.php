<?php
require_once ("../../../eat.php");

Class ActionSet {
	public static function CalculateSpace(){
		$warehouse = $_POST["calculatespace"];
		echo "$warehouse <hr>";
	}
	public static function SumTotalSpace(){
		$a = $_GET["sumtotalspace"];
		$pts = explode("|", $a);
		$pts2 = explode("`", $pts[1]);
		
		$whname = $pts2[1];
		
		$set = new Set();
		$warehouseLineSet = $set->MboSetRemote("warehouseline")->setWhere("warehouse='$whname'")->MboRemote("sum(volume) as totalspacevolume")->getMboSet();
		$warehouse = $warehouseLineSet->getMbo();
		$totalspacevolume = $warehouse->getString("totalspacevolume");
		
		
		
		$invbalanceSet = $set->MboSetRemote("invbalance")->setRelationship("item", "invbalance.itemnum = item.itemnum")
		->setWhere("invbalance.warehouse='$whname'")->MboRemote("invbalance.itemnum")->MboRemote("item.volume")
		->MboRemote("invbalance.curbal")->getMboSet();
		
		$cnt = $invbalanceSet->counts();
		
		$usedspacevolume = 0;
		for($i=0; $i<$cnt; $i++){
			$invbalance = $invbalanceSet->getMbo();
			$itemnum = $invbalance->getString("itemnum");
			$volume = $invbalance->getString("volume");
			$curbal = $invbalance->getString("curbal");
			
			$totalvolume = $volume*$curbal;
			$usedspacevolume = $usedspacevolume+$totalvolume;
		
		}
		
		$remainingvolume = $totalspacevolume-$usedspacevolume;
		if($remainingvolume<0) $remainingvolume = 0;
		Set::throws(["totalspacevolume" => $totalspacevolume, "usedspacevolume"=>$usedspacevolume, "remainingvolume"=>$remainingvolume]);
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


		if ($childTable == "warehouseline") {
			$_POST["delChildPackage"] = $dataPackage;
			Super::deleteChildren();
		}else{
			Super::sendJsonResponse("error", "Cannot Delete Transaction Table.");
		}
	}
}
	

?>