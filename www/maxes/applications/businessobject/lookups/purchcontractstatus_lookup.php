<?php 
include ("../eat.php");

class PurchContract extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
					
		Xbo::select("Status", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks", "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='purchcontract'");
		XboSet::displayData();
	}
	public static function init(){
		PurchContract::displayData();
	}
	
}
PurchContract::init();
?>