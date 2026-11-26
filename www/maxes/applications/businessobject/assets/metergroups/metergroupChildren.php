<?php 
require_once ("../../eat.php");

class MetergroupChildren {

	public static function init(){
		XboSet::validateChildren();	
		MetergroupChildren::displayData();
	}
	public static function displayData(){
		global $getOwnerCols;
		$getOwner = XboSet::getOwner();		
		$tableid = Xbo::getTableID();
		
		if($tableid=="mxtable_relatedmeter_child"){
			Xbo::select("Line Num.", "meterlinenum");
			Xbo::select("Meter", "meternum");
			Xbo::select("Meter Name", "meter.description as meterdesc");			
			Xbo::select("Description", "description");
			XboSet::getXboSet("relatedmeter", $getOwner);
			XboSet::displayData(["meter.meternum = relatedmeter.meternum"]);
		}
		
	}
}

MetergroupChildren::init();

?>