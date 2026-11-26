<?php 
include ("../eat.php");

class ProfitFromLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
					
		Xbo::select("Cost Type", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks",  "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='costtype'");
		XboSet::displayData();
	}
	public static function init(){
		ProfitFromLookup::displayData();
	}
	
}
ProfitFromLookup::init();
?>