<?php 
include ("../eat.php");

class ChartTypeLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("Tipe Chart", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks",  "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='charttype'");
		XboSet::displayData();
	}
	public static function init(){
		ChartTypeLookup::displayData();
	}
	
}
ChartTypeLookup::init();
?>