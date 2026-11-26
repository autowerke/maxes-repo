<?php 
include ("../eat.php");

class TrackTypeLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
					
		Xbo::select("Tracking Type", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks",  "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='tracktype'");
		XboSet::displayData();
	}
	public static function init(){
		TrackTypeLookup::displayData();
	}
	
}
TrackTypeLookup::init();
?>