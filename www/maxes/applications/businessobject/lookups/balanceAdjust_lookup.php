<?php 
include ("../eat.php");

class balanceAdjustLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayCols(){
		$balanceAdjustUI = Super::getParam("balanceAdjustUI");
		if(!empty($balanceAdjustUI)){
			XboSet::balanceAdjustUI();
		}
	}
	public static function init(){
		balanceAdjustLookup::displayCols();
	}
	
}
balanceAdjustLookup::init();
?>