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
		else $key = "and sonum='".$key."'";
		$notin = "and invoicetransid not in (select transid from refundline)";
		$where = "sonum is not null and linetype='item' $key $notin";
		Xbo::select("SO", "sonum");
		Xbo::select("Invoice", "invoicenum");
		Xbo::select("Type", "linetype");
		Xbo::select("Item", "itemnum");
		Xbo::select("Condition Type", "conditiontype");
		Xbo::select("Description", "description");
		Xbo::select("Invoice Date", "invoicedate", "date");
		Xbo::select("Quantity", "quantity", "decimal");
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