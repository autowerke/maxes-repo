<?php 
include ("../eat.php");

class PettyCashLineLookup extends Xbo {
	public function __destruct()
    {
       return null;
    }
	public static function displayData(){
		Xbo::select("E Faktur", "taxreceiptnum");
		Xbo::select("Description", "description");
		Xbo::select("Quantity", "quantity", "decimal");
		Xbo::select("Unit Cost", "unitprice", "decimal");
		Xbo::select("Line Cost", "lineprice", "decimal");
		Xbo::select("Financial Period", "financialperiod");
		Xbo::select("Invoice", "invoicenum");
		Xbo::select("Status", "status");
		Xbo::select("Status Date", "statusdate");
		Xbo::select("Transaction Type", "transtype");
		XboSet::getXboSet("taxreceipt", "taxgroupnum is null");
		XboSet::displayData();
	
	}
	
	public static function init(){
		PettyCashLineLookup::displayData();
	}
	
}

PettyCashLineLookup::init();




?>