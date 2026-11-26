<?php 
include ("../eat.php");

class PRStatus extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		Xbo::select("Status", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks", "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='pr'");
		XboSet::displayData();
	}
	public static function init(){
		PRStatus::displayData();
	}
	
}
PRStatus::init();
?>