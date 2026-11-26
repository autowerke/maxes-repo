<?php 
include ("../eat.php");

class conversionLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("Data Type", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks",  "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='datatype'");
		XboSet::displayData();
	}
	public static function init(){
		conversionLookup::displayData();
	}
	
}
conversionLookup::init();
?>