<?php 
include ("../eat.php");

class WOStatus extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
					
		Xbo::select("Status", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks", "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='workorder'");
		XboSet::displayData();
	}
	public static function init(){
		WOStatus::displayData();
	}
	
}
WOStatus::init();
?>