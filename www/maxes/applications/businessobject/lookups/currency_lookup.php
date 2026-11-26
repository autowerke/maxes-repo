<?php 
include ("../eat.php");

class CurrencyLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
					
		Xbo::select("Currency", "currencycode");
		Xbo::select("Description", "description");
		XboSet::getXboSet("currency");
		XboSet::displayData();
	}
	public static function init(){
		CurrencyLookup::displayData();
	}
	
}
CurrencyLookup::init();
?>