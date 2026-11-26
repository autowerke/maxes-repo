<?php 
include ("../eat.php");

class InvoicetransLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$key = Super::getParam("key");
		if(empty($key)) $key = "";
		else $key = "and ponum='".$key."'";
		$notin = "and invoicetransid not in (select transid from refundline)";
		
		$where = "ponum is not null and linetype ='item' $key $notin";		
		Xbo::select("PO", "ponum");
		Xbo::select("Invoice", "invoicenum");
		Xbo::select("Type", "linetype");
		Xbo::select("Item", "itemnum");
		Xbo::select("Description", "description");
		Xbo::select("Invoice Date", "invoicedate", "date");
		Xbo::select("Quantity", "Quantity", "decimal");
		Xbo::select("Unit Price", "unitcost", "decimal");
		Xbo::select("Total Price", "linecost", "decimal");
		
		XboSet::getXboSet("invoicetrans", $where);
		XboSet::displayData();
	
	}
	
	public static function init(){
		InvoicetransLookup::displayData();
	}
	
}

InvoicetransLookup::init();




?>