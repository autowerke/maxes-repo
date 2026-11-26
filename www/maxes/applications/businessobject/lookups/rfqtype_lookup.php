<?php 
include ("../eat.php");

class RFQType extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
					
		Xbo::select("RFQ Type", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks",  "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='rfqtype'");
		XboSet::displayData();
	}
	public static function init(){
		RFQType::displayData();
	}
	
}
RFQType::init();
?>