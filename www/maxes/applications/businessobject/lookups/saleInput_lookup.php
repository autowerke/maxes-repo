<?php 
include ("../eat.php");

class SaleInputLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayCols(){
		$saleInput = Super::getParam("saleInput");
		if(!empty($saleInput)){
			XboSet::saleInput();
		}
	}
	public static function init(){
		SaleInputLookup::displayCols();
	}
	
}
SaleInputLookup::init();
?>