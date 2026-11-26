<?php 

include ("../eat.php");

class conversionLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("Dari Satuan", "frommeasureunit");
		Xbo::select("To Measure Unit", "tomeasureunit");
		Xbo::select("Conversion", "conversion", "decimal");
		Xbo::select("Item", "itemnum");
		XboSet::getXboSet("conversion");
		XboSet::displayData();
	}
	public static function init(){
		conversionLookup::displayData();
	}
	
}
conversionLookup::init();
?>