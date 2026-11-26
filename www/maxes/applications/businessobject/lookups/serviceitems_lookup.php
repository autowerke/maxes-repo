<?php 
include ("../eat.php");

class ServiceItems extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("Service Item", "serviceitemnum");
		Xbo::select("Description", "description");
		Xbo::select("Type", "servicetype");
		Xbo::select("Group", "servicegroup"); 
		Xbo::select("Standard Cost", "stdcost", "decimal"); 
		Xbo::select("Unit", "serviceunit"); 
		Xbo::select("Est. Duration", "stdduration", "decimal"); 
		XboSet::getXboSet("serviceitems");
		XboSet::displayData();
	}
	public static function init(){
		ServiceItems::displayData();
	}
	
}
ServiceItems::init();
?>