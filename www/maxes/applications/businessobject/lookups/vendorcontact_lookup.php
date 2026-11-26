<?php 
include ("../eat.php");

class VendorContact extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$vendorcode = Super::getParam("vendorcode");
		if(!empty($vendorcode)) $where = "vendorcode='".$vendorcode."'";
		else $where = null;
		Xbo::select("Contact", "contact");
		Xbo::select("Email", "email");
		Xbo::select("Office Phone", "officephone");
		Xbo::select("Blackberry PIN", "blackberrypin");
		Xbo::select("WA Num.", "wanum");
		Xbo::select("Line ID", "lineid");
		XboSet::getXboSet("vendorcontact", $where);
		XboSet::displayData();
	}
	public static function init(){
		VendorContact::displayData();
	}
	
}
VendorContact::init();
?>