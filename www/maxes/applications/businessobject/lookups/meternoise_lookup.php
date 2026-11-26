<?php 
include ("../eat.php");

class MeterNoise extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("Kebisingan", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks",  "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='noisemeter'");
		XboSet::displayData();
	}
	public static function init(){
		MeterNoise::displayData();
	}
	
}
MeterNoise::init();
?>