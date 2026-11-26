<?php 
include ("../eat.php");

class ServiceUnit extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("Type", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks",  "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='SERVICEUNIT'");
		XboSet::displayData();
	}
	public static function init(){
		ServiceUnit::displayData();
	}
	
}
ServiceUnit::init();
?>