<?php 
include ("../eat.php");

class MeterType extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("Tipe Meteran", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks",  "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='metertype'");
		XboSet::displayData();
	}
	public static function init(){
		MeterType::displayData();
	}
	
}
MeterType::init();
?>