<?php 
include ("../eat.php");

class ManufacturerLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$man = " and manufacturer='Y'";
		$vendorcode = Super::getParam("vendorcode");
		if(!empty($vendorcode))$where  = "vendorcode!='".$vendorcode."' and disqualified='N' $man ";		
		else $where = " disqualified='N' $man";
		
			
		Xbo::select("Vendor Code", "vendorcode");
		Xbo::select("Name", "name");
		Xbo::select("Address", "address");
		Xbo::select("Website", "website");
		Xbo::select("OEM manufacturer", "manufacturer");
		Xbo::select("General Trading", "generaltrading");
		XboSet::getXboSet("vendor", $where);
		XboSet::displayData();
	}
	public static function init(){
		ManufacturerLookup::displayData();
	}
	
}
ManufacturerLookup::init();
?>