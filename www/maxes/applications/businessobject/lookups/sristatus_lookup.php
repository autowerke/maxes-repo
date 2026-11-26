<?php 
include ("../eat.php");

class SriStatus extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
					
		Xbo::select("Status", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks", "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='sri'");
		XboSet::displayData();
	}
	public static function init(){
		SriStatus::displayData();
	}
	
}
SriStatus::init();
?>