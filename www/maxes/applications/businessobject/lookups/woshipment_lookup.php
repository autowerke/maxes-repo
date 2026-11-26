<?php 
include ("../eat.php");

class WOShipmentLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		
		$wonum = Super::getParam("wonum");
		if(!empty($wonum)) $where = "wonum ='".$wonum."'";	
		else  $where =null;
		
		Xbo::select("Item", "itemnum");
		Xbo::select("Name", "item.description");
		Xbo::select("Status", "status");		
		Xbo::select("Target Start Date", "statusdate", "date");
		Xbo::select("Remarks ", "remarks");
		Xbo::select("Map", "longlat");
		Xbo::select("Remarks",  "_input_text_remarks");
		Xbo::select("Long/Lat", "_input_text_longlat");	
		XboSet::getXboSet("woshipment", $where);
		XboSet::displayData(["woshipment.itemnum = item.itemnum"]);
	
	}
	
	public static function init(){
		WOShipmentLookup::displayData();
	}
	
}

WOShipmentLookup::init();




?>