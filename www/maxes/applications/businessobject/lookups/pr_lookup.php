<?php 
include ("../eat.php");

class PRLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		
		$notcancel = "AND pr.status NOT IN ('cancel', 'closed')";
		$orgcode = Super::getParam("orgcode");
		$sitecode = Super::getParam("sitecode");

		$where = "pr.status NOT IN ('cancel')"; // Nilai default

		if (!empty($orgcode) && !empty($sitecode)) {
			$where = "pr.orgcode = '$orgcode' AND pr.sitecode = '$sitecode' $notcancel";
		} elseif (!empty($orgcode)) {
			$where = "pr.orgcode = '$orgcode' $notcancel";
		} elseif (!empty($sitecode)) {
			$where = "pr.sitecode = '$sitecode' $notcancel";
		}
		
		Xbo::select("PR", "prnum");
		Xbo::select("Description", "description");
		Xbo::select("Vendor", "vendor");
		Xbo::select("Name", "vendor.name");
		Xbo::select("Status", "status");
		Xbo::select("Line Cost", "totalcost", "decimal");
		XboSet::getXboSet("pr", $where);
		XboSet::displayData("pr.vendor = vendor.vendorcode");
	
	}
	
	public static function init(){
		PRLookup::displayData();
	}
	
}

PRLookup::init();




?>