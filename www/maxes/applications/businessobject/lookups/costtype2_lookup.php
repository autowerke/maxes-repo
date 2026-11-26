<?php 
include ("../eat.php");

class CostType2 extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
					
		Xbo::select("Cost Type", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks",  "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='costtype2'");
		XboSet::displayData();
	}
	public static function init(){
		CostType2::displayData();
	}
	
}
CostType2::init();
?>