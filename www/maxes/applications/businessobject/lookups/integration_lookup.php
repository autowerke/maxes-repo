<?php 
include ("../eat.php");

class IntegrationLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("Integrasi dengan", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks",  "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='integration'");
		XboSet::displayData();
	}
	public static function init(){
		IntegrationLookup::displayData();
	}
	
}
IntegrationLookup::init();
?>