<?php 
include ("../eat.php");

class ItemPartNumberLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$whereArr=array();
		$itemnum = Super::getParam("itemnum");
		if(!empty($itemnum)) array_push($whereArr, "itemnum ='".$itemnum."'");
		else $where = null;
		
		$where = implode(" and ", $whereArr);
		
		Xbo::select("Part Number", "partnum");
		Xbo::select("Item", "itemnum");
		Xbo::select("Vendor", "vendor");
		Xbo::select("Vendor Name", "vendor.name");
		Xbo::select("Country", "country");
		Xbo::select("Manufacturer", "manufacturer");
		Xbo::select("OEM", "oem");
		Xbo::select("itempartnumid", "itempartnumid");
		XboSet::getXboSet("itempartnum", $where);
		XboSet::displayData("itempartnum.vendor = vendor.vendorcode");
	}
	public static function init(){
		ItemPartNumberLookup::displayData();
	}
	
}
ItemPartNumberLookup::init();
?>