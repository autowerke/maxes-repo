<?php 
include ("../eat.php");

class RFQVendor extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$rfqnum = Super::getParam("rfqnum");
	
		if(!empty($rfqnum)) $where  = "rfqnum ='".$rfqnum."'";		
		else $where = null;
				
		Xbo::select("Vendor", "vendor");
		Xbo::select("Name", "vendor.name");
		Xbo::select("Address", "vendor.address");
        Xbo::select("Contact", "vendor.contact");
		Xbo::select("City", "vendor.city");
		Xbo::select("Province.", "vendor.stateprovince");
		XboSet::getXboSet("rfqvendor", $where);
		XboSet::displayData(["rfqvendor.vendor = vendor.vendorcode"]);
	}
	public static function init(){
		RFQVendor::displayData();
	}
	
}
RFQVendor::init();
?>