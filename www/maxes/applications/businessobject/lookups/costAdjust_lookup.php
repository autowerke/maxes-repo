<?php 
include ("../eat.php");

class costAdjustLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayCols(){
		$costAdjustUI = Super::getParam("costAdjustUI");
		if(!empty($costAdjustUI)){
			XboSet::costAdjustUI();
		}
	}
	public static function init(){
		costAdjustLookup::displayCols();
	}
	
}
costAdjustLookup::init();
?>