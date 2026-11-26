<?php 
include ("../eat.php");

class MXSWFATTRIBUTE extends Xbo {

	public function __destruct()
    {
		return null;
    }
	public static function displayData(){
		
		Xbo::select("Role", "action");
		Xbo::select("Application", "appname");
		Xbo::select("Type", "type");
		Xbo::select("Value", "value");
		XboSet::getXboSet("mxswfattribute");
		XboSet::displayData();
	}
	public static function init(){
		MXSWFATTRIBUTE::displayData();
	}
	
}
MXSWFATTRIBUTE::init();
?>