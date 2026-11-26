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
		else $key = "and transnum='".$key."'";
		$notin = "and invoicetransid not in (select transid from refundline)";
		
		$where = "transnum is not null $key $notin";
		Xbo::select("No. Transaksi", "transnum");
		Xbo::select("Invoice", "invoicenum");
		Xbo::select("Item", "itemnum");
		Xbo::select("Condition Type", "conditiontype");
		Xbo::select("Description", "description");
		Xbo::select("Invoice Date", "invoicedate", "date");
		Xbo::select("Quantity", "Quantity", "decimal");
		Xbo::select("Unit Price", "unitpricediscount", "decimal");
		Xbo::select("Total Price", "linepricediscount", "decimal");
		
		
		XboSet::getXboSet("invoicetrans", $where);
		XboSet::displayData();
	
	}
	
	public static function init(){
		InvoicetransLookup::displayData();
	}
	
}

InvoicetransLookup::init();




?>