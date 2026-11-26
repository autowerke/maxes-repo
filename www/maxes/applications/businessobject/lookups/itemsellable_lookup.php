<?php 
include ("../eat.php");

class ItemSellable extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		Xbo::select("item", "itemnum");
		Xbo::select("Description", "item.description");
		Xbo::select("Warehouse", "warehouse");
		Xbo::select("Rack/Bin", "racknum");
		Xbo::select("Condition Type", "conditiontype");
		Xbo::select("Quantity", "curbal", "decimal");
		Xbo::select("Unit Cost", "avgcost", "decimal");
		XboSet::getXboSet("v_invbalance");
		XboSet::displayData(["v_invbalance.itemnum = item.itemnum"]);	
	}
	public static function init(){
		ItemSellable::displayData();
	}
	
}
ItemSellable::init();
?>