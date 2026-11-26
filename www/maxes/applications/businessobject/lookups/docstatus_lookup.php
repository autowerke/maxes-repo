<?php 
include ("../eat.php");

class DocStatus extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("Tipe Attribute", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks",  "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='docstatus'");
		XboSet::displayData();
	}
	public static function init(){
		DocStatus::displayData();
	}
	
}
DocStatus::init();
?>