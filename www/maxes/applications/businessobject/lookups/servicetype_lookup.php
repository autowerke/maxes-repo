<?php 
include ("../eat.php");

class ServiceType extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("Type", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks",  "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='SERVICETYPE'");
		XboSet::displayData();
	}
	public static function init(){
		ServiceType::displayData();
	}
	
}
ServiceType::init();
?>