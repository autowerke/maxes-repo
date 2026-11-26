<?php 
include ("../eat.php");

class VendorContact extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$vendorcode = Super::getParam("vendorcode");
		
		if(!empty($vendorcode)) $where  = "vendorcode!='".$vendorcode."' and disqualified='N'";		
		else $where = " disqualified='N'";
				
		Xbo::select("Kode Vendor", "vendorcode");
		Xbo::select("Name", "name");
		Xbo::select("Address", "address");
		Xbo::select("Overseas OEM", "manufacturer");
		Xbo::select("Authorized Agent.", "authorizedagent");
		Xbo::select("General Trading", "generaltrading");
		XboSet::getXboSet("vendor");
		XboSet::displayData();
	}
	public static function init(){
		VendorContact::displayData();
	}
	
}
VendorContact::init();
?>