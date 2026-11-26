<?php 
include ("../eat.php");

class WOClassLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("Classification Code", "classcode");
		Xbo::select("Description", "description");
		XboSet::getXboSet("woclass");
		XboSet::displayData();
	}
	public static function init(){
		WOClassLookup::displayData();
	}
	
}
WOClassLookup::init();
?>