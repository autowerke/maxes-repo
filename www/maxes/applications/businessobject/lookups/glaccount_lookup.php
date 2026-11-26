<?php 
include ("../eat.php");

class CurrencyLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$orgcode = Super::getParam("orgcode");
		if(!empty($orgcode)) $where = "orgcode='".$orgcode."'";
		else $where = null;
		Xbo::select("Account Code", "glaccount");
		Xbo::select("Description", "description");
		XboSet::getXboSet("chartofaccount", $where);
		XboSet::displayData();
	}
	public static function init(){
		CurrencyLookup::displayData();
	}
	
}
CurrencyLookup::init();
?>