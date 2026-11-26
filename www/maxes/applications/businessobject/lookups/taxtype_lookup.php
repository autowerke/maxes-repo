<?php 
include ("../eat.php");

class TaxType extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		
		Xbo::select("Tax Type", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks",  "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='taxtype'");
		XboSet::displayData();
	}
	public static function init(){
		TaxType::displayData();
	}
	
}
TaxType::init();
?>