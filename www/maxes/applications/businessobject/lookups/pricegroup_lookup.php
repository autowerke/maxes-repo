<?php 
include ("../eat.php");

class PriceGroupLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("Price Code", "pricecode");		
		Xbo::select("Description", "description");
		Xbo::select("Price Rate(%)", "pricerate");
		Xbo::select("Lower Price?", "islesser");
		XboSet::getXboSet("pricegroup");
		XboSet::displayData();
	}
	public static function init(){
		PriceGroupLookup::displayData();
	}
	
}
PriceGroupLookup::init();
?>