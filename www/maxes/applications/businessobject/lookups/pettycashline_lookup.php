<?php 
include ("../eat.php");

class PettyCashLineLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		Xbo::select("Invoice", "invoicenum");
		Xbo::select("Line Num.", "invoicelinenum");
		Xbo::select("Description", "description");
		Xbo::select("Order Qty.", "quantity", "decimal");
		Xbo::select("Order Unit", "orderunit");
		Xbo::select("Unit Cost", "unitcost", "decimal");
		Xbo::select("Line Cost", "linecost", "decimal");
		Xbo::select("Financial Period", "financialperiod");
		Xbo::select("Transaction Type", "transtype");
		XboSet::getXboSet("pettycashline", "isimported!='Y'");
		XboSet::displayData();
	
	}
	
	public static function init(){
		PettyCashLineLookup::displayData();
	}
	
}

PettyCashLineLookup::init();




?>