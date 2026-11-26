<?php 
include ("../eat.php");

class Assetsstatus extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
					
		Xbo::select("Status", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks", "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='assets'");
		XboSet::displayData();
	}
	public static function init(){
		Assetsstatus::displayData();
	}
	
}
Assetsstatus::init();
?>