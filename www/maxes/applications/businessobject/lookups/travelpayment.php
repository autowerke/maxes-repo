<?php 
include ("../eat.php");

class TravelPayment extends Xbo {

	public function __destruct()
    {
       return null;
    }
	public static function displayData(){
		
			
		Xbo::select("Address Code", "addresscode");
		Xbo::select("Address", "address");
		Xbo::select("City", "city");
		Xbo::select("Province", "stateprovince");
		Xbo::select("Postal Code", "zippostalcode");
		Xbo::select("Country", "country");
		Xbo::select("Bill Default", "siteaddress.billtodefault");
		Xbo::select("Ship Default", "siteaddress.shiptodefault");
		XboSet::getXboSet("address",$where);
		XboSet::displayData("siteaddress.siteaddresscode = address.addresscode");
	}
	public static function init(){
		TravelPayment::displayData();
	}
	
}
TravelPayment::init();
?>