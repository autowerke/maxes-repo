<?php 
include ("../eat.php");

class ReceiptInputLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayCols(){
		$receiptInput = Super::getParam("receiptInput");
		if(!empty($receiptInput)){
			XboSet::receiptInput();
		}
	}
	public static function init(){
		ReceiptInputLookup::displayCols();
	}
	
}
ReceiptInputLookup::init();
?>