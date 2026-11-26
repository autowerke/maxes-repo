<?php 
include ("../eat.php");

class CashReceiptStatus extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
					
		Xbo::select("Status", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks", "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='cashreceipt'");
		XboSet::displayData();
	}
	public static function init(){
		CashReceiptStatus::displayData();
	}
	
}
CashReceiptStatus::init();
?>