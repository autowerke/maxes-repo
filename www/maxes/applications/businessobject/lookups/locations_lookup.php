<?php 
include ("../eat.php");

class Locations extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
					
		Xbo::select("Location", "locnum");
		Xbo::select("Description", "description");
		Xbo::select("Status", "status");
		XboSet::getXboSet("locations");
		XboSet::displayData();
	}
	public static function init(){
		Locations::displayData();
	}
	
}
Locations::init();
?>