<?php 
include ("../eat.php");

class ClasificationLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("Classification Code", "classificationcode");
		Xbo::select("Description", "description");
		XboSet::getXboSet("classification");
		XboSet::displayData();
	}
	public static function init(){
		ClasificationLookup::displayData();
	}
	
}
ClasificationLookup::init();
?>