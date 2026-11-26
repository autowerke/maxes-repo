<?php 
include ("../eat.php");

class InvoiceStatus extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
					
		Xbo::select("Status", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks", "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='invoice'");
		XboSet::displayData();
	}
	public static function init(){
		InvoiceStatus::displayData();
	}
	
}
InvoiceStatus::init();
?>