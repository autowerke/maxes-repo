<?php 
include ("../eat.php");

class measureUnitLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("Measure Unit", "measureunitcode");
		Xbo::select("Description", "description");
		XboSet::getXboSet("measureunit");
		XboSet::displayData();
	}
	public static function init(){
		measureUnitLookup::displayData();
	}
	
}
measureUnitLookup::init();
?>