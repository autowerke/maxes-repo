<?php 
include ("../eat.php");

class customerLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
	
		$orgcode = Super::getParam("orgcode");
		if(!empty($orgcode)) $where =  "orgcode ='".$orgcode."'";	
		else $where = null;
		
		Xbo::select("Customer Code", "customercode");
		Xbo::select("Name", "name");
		Xbo::select("Distributor", "distributor");
		Xbo::select("Reseller", "reseller");
		Xbo::select("Current Stock", "balance", "decimal");
		XboSet::getXboSet("customer");
		XboSet::displayData();
	}
	public static function init(){
		customerLookup::displayData();
	}
	
}
customerLookup::init();
?>