<?php 
include ("../eat.php");

class InvoiceInputLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayCols(){
		$invoiceinput = Super::getParam("invoiceinput");
		if(!empty($invoiceinput)){
			XboSet::InvoiceInput();
		}
	}
	public static function init(){
		InvoiceInputLookup::displayCols();
	}
	
}
InvoiceInputLookup::init();
?>