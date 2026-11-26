<?php 
include ("../eat.php");

class MyAllowanceStatus extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
					
		Xbo::select("Status", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks", "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='myallowance'");
		XboSet::displayData();
	}
	public static function init(){
		MyAllowanceStatus::displayData();
	}
	
}
MyAllowanceStatus::init();
?>