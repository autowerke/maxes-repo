<?php 
include ("../eat.php");

class WPType extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("Tax Payer Type", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks",  "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='wptype'");
		XboSet::displayData();
	}
	public static function init(){
		WPType::displayData();
	}
	
}
WPType::init();
?>