<?php 
include ("../eat.php");

class WorkTypeLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("Work Type", "worktype");
		Xbo::select("Description", "description");
		XboSet::getXboSet("wotype");
		XboSet::displayData();
	}
	public static function init(){
		WorkTypeLookup::displayData();
	}
	
}
WorkTypeLookup::init();
?>