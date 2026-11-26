<?php 
include ("../eat.php");

class PhysicalCountLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayCols(){
		$physcountui = Super::getParam("physcountui");
		if(!empty($physcountui)){
			XboSet::PhysicalCountUI();
		}
	}
	public static function init(){
		PhysicalCountLookup::displayCols();
	}
	
}
PhysicalCountLookup::init();
?>