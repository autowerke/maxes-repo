<?php 
include ("../eat.php");

class TaxReceiptStatus extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
					
		Xbo::select("Status", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks", "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='taxreceipt'");
		XboSet::displayData();
	}
	public static function init(){
		TaxReceiptStatus::displayData();
	}
	
}
TaxReceiptStatus::init();
?>