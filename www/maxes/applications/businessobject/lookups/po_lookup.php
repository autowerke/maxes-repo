<?php 
include ("../eat.php");

class POLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		//$belumlunas = " and unpaidcost<0 ";
		$belumlunas = "";
		$notcancel = "and po.status not in('cancel', 'closed')";
		$orgcode = Super::getParam("orgcode");
		if(!empty($orgcode)) $where = "orgcode ='".$orgcode."' $notcancel $belumlunas";	
		
		$sitecode = Super::getParam("sitecode");
		if(!empty($sitecode)) $where  = "sitecode ='".$_POST['sitecode']."' $notcancel $belumlunas";	
		
		if(!empty($sitecode) && !empty($orgcode)) $where  = "orgcode='".$orgcode."' and sitecode ='".$sitecode."' $notcancel";
		
		else{
			$where = " po.status not in ('cancel') $belumlunas";
		}	
		
		Xbo::select("PO", "ponum");
		Xbo::select("Description", "description");
		Xbo::select("Vendor", "vendor");
		Xbo::select("Vendor Name", "vendor.name");
		Xbo::select("Status", "status");
		Xbo::select("Discount(%)", "discountpercentage", "decimal");
		Xbo::select("Discount", "discountprice", "decimal");
		Xbo::select("Line Cost", "totalcost", "decimal");
		Xbo::select("Currency", "currencycode");
		Xbo::select("Line Cost (Basis)", "totalbasecost", "decimal");
		Xbo::select("Paid Price", "paidcost", "decimal");
		Xbo::select("Unpaid", "unpaidcost", "decimal");
		XboSet::getXboSet("po", $where);
		XboSet::displayData("po.vendor = vendor.vendorcode");
	
	}
	
	public static function init(){
		POLookup::displayData();
	}
	
}

POLookup::init();




?>