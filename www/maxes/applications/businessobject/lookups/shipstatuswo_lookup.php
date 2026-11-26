<?php 
include ("../eat.php");

class ShipStatusWO extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
					
		Xbo::select("Status", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks",  "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='shipstatus' and value in('DELIVERED', 'SHIPPED')");
		XboSet::displayData();
	}
	public static function init(){
		ShipStatusWO::displayData();
	}
	
}
ShipStatusWO::init();
?>