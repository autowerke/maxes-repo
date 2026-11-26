<?php 
include ("../eat.php");

class RackType extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("Rack Type", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks",  "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='racktype'");
		XboSet::displayData();
	}
	public static function init(){
		RackType::displayData();
	}
	
}
RackType::init();
?>