<?php 
include ("../eat.php");

class BillingStatus extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
					
		Xbo::select("Status", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks", "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='billing'");
		XboSet::displayData();
	}
	public static function init(){
		BillingStatus::displayData();
	}
	
}
BillingStatus::init();
?>