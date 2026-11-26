<?php 
include ("../eat.php");

class InvoiceLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		
		$notcancel = "and invoice.status not in('cancel')";
		$orgcode = Super::getParam("orgcode");
		if(!empty($orgcode)) $where = "orgcode ='".$orgcode."' $notcancel";	
		
		$sitecode = Super::getParam("sitecode");
		if(!empty($sitecode)) $where  = "sitecode ='".$_POST['sitecode']."' $notcancel";	
		
		if(!empty($sitecode) && !empty($orgcode)) $where  = "orgcode='".$orgcode."' and sitecode ='".$sitecode."' $notcancel";
		
		else{
			$where = " invoice.status not in ('cancel')";
		}	
		
		Xbo::select("Invoice", "invoicenum");
		Xbo::select("Description", "description");
		Xbo::select("Invoice Date", "invoicedate", "date");
		Xbo::select("Status", "status");
		Xbo::select("Sub Total ", "subtotalcost", "decimal");
		Xbo::select("Total Tax", "totaltax", "decimal");
		Xbo::select("Line Cost", "totalcost", "decimal");
		XboSet::getXboSet("invoice", $where);
		XboSet::displayData();
	
	}
	
	public static function init(){
		InvoiceLookup::displayData();
	}
	
}

InvoiceLookup::init();




?>