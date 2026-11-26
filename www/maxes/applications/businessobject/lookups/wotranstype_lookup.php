<?php 
include ("../eat.php");

class WOTransTypeLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("Transaction Type", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks",  "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='wotranstype'");
		XboSet::displayData();
	}
	public static function init(){
		WOTransTypeLookup::displayData();
	}
	
}
WOTransTypeLookup::init();
?>