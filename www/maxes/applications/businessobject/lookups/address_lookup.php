<?php 
include ("../eat.php");

class addressLookup extends Xbo {

	public function __destruct()
    {
       return null;
    }
	public static function displayData(){
		$whereArr=array();
		$sitecode = Super::getParam("sitecode");
		if(!empty($sitecode)) array_push($whereArr, "sitecode ='".$sitecode."'");
		else $where = null;
		$where = implode(" and ", $whereArr);
			
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
		addressLookup::displayData();
	}
	
}
addressLookup::init();
?>