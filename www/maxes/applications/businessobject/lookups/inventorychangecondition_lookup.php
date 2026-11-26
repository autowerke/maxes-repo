<?php 
include ("../eat.php");

class InventoryChangeCondition extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$whereClause = [];
		if(Super::hasParam('itemnum')) {
			$itemnum = Super::getParam("itemnum");
			$whereClause [] = "itemnum='$itemnum'";
		}
		if(Super::hasParam('warehouse')) {
			$warehouse = Super::getParam("warehouse");
			$whereClause [] = "warehouse='$warehouse'";
		}

		$owner = implode(" AND ", $whereClause);
		Xbo::select("Item", "itemnum");
		Xbo::select("Cost Date", "costdate", "date");
		Xbo::select("Expired Date", "expdate", "date");
		Xbo::select("Quantity", "quantity", "decimal");
		Xbo::select("Warehouse", "warehouse");
		Xbo::select("Rack/Bin", "racknum");
		Xbo::select("Batch Num.", "batchnum");
		Xbo::select("From Condition Type", "conditiontype");
		Xbo::select("Changed Qty.", "_input_text_changeqty", "decimal");
		Xbo::select("Changed Date", "_input_text_changedate", "date");
		Xbo::select("To Condition Type.", "_input_text_toconditiontype_itemcondition@itemnum");
		XboSet::getXboSet("invfifolifocost", $owner. " and quantity>0 and conditiontype is not null");
		XboSet::displayData();
	}
	public static function init(){
		InventoryChangeCondition::displayData();
	}
	
}
InventoryChangeCondition::init();
?>