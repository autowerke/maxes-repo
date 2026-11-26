<?php 
include ("../eat.php");

class DurationUnitLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("Duration", "value");
		Xbo::select("Description", "valuetext");
		XboSet::getXboSet("mxsattrvalue", "name='durationunit'");
		XboSet::displayData();
	}
	public static function init(){
		DurationUnitLookup::displayData();
	}
	
}
DurationUnitLookup::init();
?>