<?php 
include ("../eat.php");

class SalesOrderLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$sitecode = Super::getParam("sitecode");
		$belumlunas = "unpaidprice<0";
		if(!empty($sitecode)) $where  = "sitecode ='".$sitecode."' and $belumlunas";	
		else $where = $belumlunas;
					
		Xbo::select("SO", "sonum");
		Xbo::select("Description", "description");
		Xbo::select("Customer Code", "customercode");
		Xbo::select("Name", "customer.name");
		Xbo::select("Discount(%)", "discountpercentage", "decimal");
		Xbo::select("Discount", "discountprice", "decimal");
		Xbo::select("Tax After Discount", "discountedtotaltax", "decimal");
		Xbo::select("Price after Discount", "discountedtotalprice", "decimal");
		Xbo::select("Paid Price", "paidprice", "decimal");
		Xbo::select("Unpaid Price", "unpaidprice", "decimal");
		XboSet::getXboSet("so", $where);
		XboSet::displayData(["so.customercode = customer.customercode"]);
	}
	public static function init(){
		SalesOrderLookup::displayData();
	}
	
}
SalesOrderLookup::init();
?>