<?php 
include ("../eat.php");

class DepreType extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("Depreciation Type", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks",  "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='depretype'");
		XboSet::displayData();
	}
	public static function init(){
		DepreType::displayData();
	}
	
}
DepreType::init();
?>