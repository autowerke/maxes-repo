<?php 
include ("../eat.php");

class InvbalanceLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("item", "itemnum");
		Xbo::select("Description", "item.description");
		Xbo::select("Specification", "item.spectext");
		Xbo::select("Manufacturer", "item.manufacturer");
		Xbo::select("Grup", "item.itemgroup");
		Xbo::select("Warehouse", "warehouse");
		Xbo::select("Rack/Bin", "racknum");
		Xbo::select("Condition Type", "conditiontype");
		Xbo::select("Quantity", "curbal", "decimal");
		XboSet::getXboSet("v_invbalance");
		XboSet::displayData("v_invbalance.itemnum = item.itemnum");	
	}
	public static function init(){
		InvbalanceLookup::displayData();
	}
	
}
InvbalanceLookup::init();
?>