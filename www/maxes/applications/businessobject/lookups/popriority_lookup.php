<?php 
include ("../eat.php");

class poPriority extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("Type", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks",  "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='popriority'");
		XboSet::displayData();
	}
	public static function init(){
		poPriority::displayData();
	}
	
}
poPriority::init();
?>