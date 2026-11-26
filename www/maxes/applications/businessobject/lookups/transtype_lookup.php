<?php 
include ("../eat.php");

class TransTypeLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("Transaction Type", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks",  "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='transtype'");
		XboSet::displayData();
	}
	public static function init(){
		TransTypeLookup::displayData();
	}
	
}
TransTypeLookup::init();
?>