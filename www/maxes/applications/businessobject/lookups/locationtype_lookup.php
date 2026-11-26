<?php 
include ("../eat.php");

class LocationType extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("Location Type", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks",  "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='locationtype'");
		XboSet::displayData();
	}
	public static function init(){
		LocationType::displayData();
	}
	
}
LocationType::init();
?>