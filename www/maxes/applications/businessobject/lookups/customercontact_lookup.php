<?php 
include ("../eat.php");

class customercontactLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$customercode = Super::getParam("customercode");
		if(!empty($customercode))$where  = "customer.customercode ='".$customercode."'";			
		else $where = null;
		
				
		Xbo::select("Contact Name", "contactname");
		Xbo::select("Title", "function");
		Xbo::select("Phone", "phone");
		Xbo::select("WA", "wanum");
		Xbo::select("Email", "email");
		Xbo::select("Customer Code", "customercode");
		Xbo::select("Customer Name", "customer.name");
		XboSet::getXboSet("customercontact", $where);
		XboSet::displayData(["customercontact.customercode = customer.customercode"]);
	}
	public static function init(){
		customercontactLookup::displayData();
	}
	
}
customercontactLookup::init();
?>e