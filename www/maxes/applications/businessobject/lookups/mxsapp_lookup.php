<?php 
include ("../eat.php");

class customerLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
					
		Xbo::select("Application", "appname");
		Xbo::select("Description", "description");
		
		XboSet::getXboSet("mxsapp");
		XboSet::displayData();
	}
	public static function init(){
		customerLookup::displayData();
	}
	
}
customerLookup::init();
?>