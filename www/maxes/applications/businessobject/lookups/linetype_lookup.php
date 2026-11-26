<?php 
include ("../eat.php");

class conversionLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("Type", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks",  "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='linetype'");
		XboSet::displayData();
	}
	public static function init(){
		conversionLookup::displayData();
	}
	
}
conversionLookup::init();
?>