<?php 
include ("../eat.php");

class ConsignmentInvoiceLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$sitecode = Super::getParam("sitecode");
		if(!empty($sitecode)) $where  = "invoice.sitecode ='".$sitecode."' and invoice.isconsignment='Y'";
		else $where = null;
		
					
		Xbo::select("Invoice", "invoicenum");
		Xbo::select("Description", "description");
		Xbo::select("Customer Code", "customercode");
		Xbo::select("Name", "customer.name");
		Xbo::select("Invoice Date", "invoicedate");
		XboSet::getXboSet("invoice", $where);
		XboSet::displayData("invoice.customercode = customer.customercode");
	}
	public static function init(){
		ConsignmentInvoiceLookup::displayData();
	}
	
}
ConsignmentInvoiceLookup::init();
?>