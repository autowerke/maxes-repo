<?php 
include ("../eat.php");

class CustomerStatus extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
					
		Xbo::select("Status", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks", "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='customer'");
		XboSet::displayData();
	}
	public static function init(){
		CustomerStatus::displayData();
	}
	
}
CustomerStatus::init();
?>