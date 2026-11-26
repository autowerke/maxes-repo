<?php 
include ("../eat.php");

class HazardLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
					
		Xbo::select("Hazard", "hazardnum");
		Xbo::select("Description", "description");
		XboSet::getXboSet("hazard");
		XboSet::displayData();
	}
	public static function init(){
		HazardLookup::displayData();
	}
	
}
HazardLookup::init();
?>