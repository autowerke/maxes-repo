<?php 
include ("../eat.php");

class FleetUsageStatus extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
					
		Xbo::select("Status", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks", "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='fleetusage'");
		XboSet::displayData();
	}
	public static function init(){
		FleetUsageStatus::displayData();
	}
	
}
FleetUsageStatus::init();
?>