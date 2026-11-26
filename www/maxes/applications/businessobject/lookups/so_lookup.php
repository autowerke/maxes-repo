<?php 
include ("../eat.php");

class SOLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		
		$notcancel = "and so.status not in('cancel')";
		$orgcode = Super::getParam("orgcode");
		if(!empty($orgcode)) $where = "orgcode ='".$orgcode."' $notcancel";	
		
		$sitecode = Super::getParam("sitecode");
		if(!empty($sitecode)) $where  = "sitecode ='".$_POST['sitecode']."' $notcancel";	
		
		if(!empty($sitecode) && !empty($orgcode)) $where  = "orgcode='".$orgcode."' and sitecode ='".$sitecode."' $notcancel";
		
		else{
			$where = " so.status not in ('cancel')";
		}	
		
		Xbo::select("SO", "sonum");
		Xbo::select("Description", "description");
		Xbo::select("Customer Code", "customer.name");
		Xbo::select("Warranty until", "warrantydate", "date");
		Xbo::select("Contact", "contactname");
		Xbo::select("Status", "status");
		Xbo::select("Discount", "discountprice", "decimal");
		Xbo::select("Total Price after Discount", "discountedtotalprice", "decimal");
		XboSet::getXboSet("so", $where);
		XboSet::displayData(["customer.customercode = so.customercode"]);
	
	}
	
	public static function init(){
		SOLookup::displayData();
	}
	
}

SOLookup::init();




?>