<?php 
include ("../eat.php");

class ShipStatusCustBal extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
					
		Xbo::select("Status", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks",  "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='shipstatus' and value not in('RETURNED')");
		XboSet::displayData();
	}
	public static function init(){
		ShipStatusCustBal::displayData();
	}
	
}
ShipStatusCustBal::init();
?>