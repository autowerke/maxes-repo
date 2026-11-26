<?php 
include ("../eat.php");

class Locationsstatus extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
					
		Xbo::select("Status", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks", "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='locations'");
		XboSet::displayData();
	}
	public static function init(){
		Locationsstatus::displayData();
	}
	
}
Locationsstatus::init();
?>