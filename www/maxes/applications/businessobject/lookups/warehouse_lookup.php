<?php 
include ("../eat.php");

class WarehouseLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$sitecode = Super::getParam("sitecode");
		if(!empty($sitecode)) $where  = "sitecode ='".$sitecode."'";	
		else $where = null;
		Xbo::select("Warehouse", "warehouse");
		Xbo::select("Description", "description");
		Xbo::select("Account Code", "controlacc");
		XboSet::getXboSet("warehouse", $where);
		XboSet::displayData();
	}
	public static function init(){
		WarehouseLookup::displayData();
	}
	
}
WarehouseLookup::init();
?>