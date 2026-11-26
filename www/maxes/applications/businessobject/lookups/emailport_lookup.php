<?php 
include ("../eat.php");

class EmailPortLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("Email Port", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks",  "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='emailport'");
		XboSet::displayData();
	}
	public static function init(){
		EmailPortLookup::displayData();
	}
	
}
EmailPortLookup::init();
?>