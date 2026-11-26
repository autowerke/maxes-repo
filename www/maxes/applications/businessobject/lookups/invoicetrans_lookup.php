<?php 
include ("../eat.php");

class InvoicetransLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$where = "transtype like '%SALES%' and linetype = 'item'";
		Xbo::select("Invoice", "invoicenum");
		Xbo::select("Item", "itemnum");
		Xbo::select("Description", "description");
		Xbo::select("Invoice Date", "invoicedate", "date");
		Xbo::select("Quantity", "Quantity", "decimal");
		Xbo::select("Unit Price", "unitprice", "decimal");
		Xbo::select("Total Price", "lineprice", "decimal");
		Xbo::select("Unit Price Discount", "unitpricediscount", "decimal");
		Xbo::select("Total Price Discount", "linepricediscount", "decimal");
		
		XboSet::getXboSet("invoicetrans", $where);
		XboSet::displayData();
	
	}
	
	public static function init(){
		InvoicetransLookup::displayData();
	}
	
}

InvoicetransLookup::init();




?>