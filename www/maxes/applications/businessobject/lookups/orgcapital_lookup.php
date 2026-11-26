<?php 
include ("../eat.php");

class OrgCapital extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$orgcode = Super::getParam("orgcode");
		if(!empty($orgcode)) $where = "orgcode = '$orgcode'";
		else $where = null;
		Xbo::select("Site", "sitecode");
		Xbo::select("Capital", "capital", "decimal");
		Xbo::select("Debit", "gldebitacct");
		Xbo::select("Credit", "glcreditacct");
		Xbo::select("Remarks",  "remarks");
		Xbo::select("Entered By", "enterby");
		Xbo::select("Entered Date", "enterdate", "date");
		Xbo::select("Changed By", "changeby");
		Xbo::select("Changed Date", "changedate", "date");
		XboSet::getXboSet("orgcapital", $where);
		XboSet::displayData();
	}
	public static function init(){
		OrgCapital::displayData();
	}
	
}
OrgCapital::init();
?>