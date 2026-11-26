<?php 
include ("../eat.php");

class SOStatus extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
					
		Xbo::select("Status", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks", "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='so'");
		XboSet::displayData();
	}
	public static function init(){
		SOStatus::displayData();
	}
	
}
SOStatus::init();
?>