<?php 
include ("../eat.php");

class WorkInstrStatus extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
					
		Xbo::select("Status", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks", "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='workinstr'");
		XboSet::displayData();
	}
	public static function init(){
		WorkInstrStatus::displayData();
	}
	
}
WorkInstrStatus::init();
?>