<?php 
include ("../eat.php");

class PettyCashStatus extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
					
		Xbo::select("Status", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks", "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='pettycash'");
		XboSet::displayData();
	}
	public static function init(){
		PettyCashStatus::displayData();
	}
	
}
PettyCashStatus::init();
?>