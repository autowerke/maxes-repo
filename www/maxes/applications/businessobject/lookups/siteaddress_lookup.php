<?php 
include ("../eat.php");

class SiteAddressLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$sitecode = Super::getParam("sitecode");
		if(!empty($sitecode)) $where  = "sitecode ='".$sitecode."'";
		else $where = null;
		
		
		Xbo::select("Site Addr. Code", "siteaddresscode");
		Xbo::select("Description", "address.address");		
		Xbo::select("Bill Contact", "billcontact");
		Xbo::select("Ship Contact", "shipcontact");
		Xbo::select("Address Code", "addresscode");
		Xbo::select("Company", "orgcode");
		XboSet::getXboSet("siteaddress", $where);
		XboSet::displayData(["siteaddress.siteaddresscode = address.addresscode"]);
	}
	public static function init(){
		SiteAddressLookup::displayData();
	}
	
}
SiteAddressLookup::init();
?>