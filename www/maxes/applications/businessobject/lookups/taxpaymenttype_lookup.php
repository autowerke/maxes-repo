<?php 
include ("../eat.php");

class ConsigmentTypeLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
					
		Xbo::select("Tax Payment Type", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks",  "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='taxpaymenttype'");
		XboSet::displayData();
	}
	public static function init(){
		ConsigmentTypeLookup::displayData();
	}
	
}
ConsigmentTypeLookup::init();
?>