<?php 
include ("../eat.php");

class EmailServerLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("Email Server", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks",  "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='emailserver'");
		XboSet::displayData();
	}
	public static function init(){
		EmailServerLookup::displayData();
	}
	
}
EmailServerLookup::init();
?>