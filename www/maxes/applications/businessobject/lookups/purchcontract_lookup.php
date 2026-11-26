<?php 
include ("../eat.php");

class PurchContractLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$where = "purchcontract.status IN ('appr')"; // Nilai default
		Xbo::select("Contract", "contractnum");
		Xbo::select("Description", "description");
		Xbo::select("Vendor", "vendorcode");
		Xbo::select("Name", "vendor.name");
		Xbo::select("Status", "status");
		XboSet::getXboSet("purchcontract", $where);
		XboSet::displayData("purchcontract.vendorcode = vendor.vendorcode");
	
	}
	
	public static function init(){
		PurchContractLookup::displayData();
	}
	
}

PurchContractLookup::init();




?>