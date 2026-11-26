<?php 
include ("../eat.php");

class SalesQuotationStatus extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
					
		Xbo::select("Status", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks", "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='salesquotation'");
		XboSet::displayData();
	}
	public static function init(){
		SalesQuotationStatus::displayData();
	}
	
}
SalesQuotationStatus::init();
?>