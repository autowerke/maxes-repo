<?php 
include ("../eat.php");

class ShipStatus2 extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
					
		Xbo::select("Status", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks",  "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='shipstatus' and value = 'DELIVERED'");
		XboSet::displayData();
	}
	public static function init(){
		ShipStatus2::displayData();
	}
	
}
ShipStatus2::init();
?>