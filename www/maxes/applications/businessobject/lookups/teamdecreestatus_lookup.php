<?php 
include ("../eat.php");

class TeamDecreeStatus extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
					
		Xbo::select("Status", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks", "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='teamdecree'");
		XboSet::displayData();
	}
	public static function init(){
		TeamDecreeStatus::displayData();
	}
	
}
TeamDecreeStatus::init();
?>