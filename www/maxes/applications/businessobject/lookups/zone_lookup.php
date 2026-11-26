<?php 
include ("../eat.php");

class Zone extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
	
		$where =null;
		
		Xbo::select("Zone Name", "zonename");
		Xbo::select("Description", "description");
		Xbo::select("Remarks", "remarks");
		XboSet::getXboSet("zone", $where);
		XboSet::displayData();
	}
	
	public static function init(){
		Zone::displayData();
	}
	
}

Zone::init();




?>