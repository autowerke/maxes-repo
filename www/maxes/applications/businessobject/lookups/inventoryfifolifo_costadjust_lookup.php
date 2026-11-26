<?php 
include ("../eat.php");

class InvFifoLifoCurbalAdjustment extends Xbo {

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
		Xbo::select("Item", "invfifolifocost.itemnum");
		Xbo::select("Cost Date", "costdate", "date");
		Xbo::select("Expired Date", "expdate", "date");
		Xbo::select("Quantity", "quantity", "decimal");
		Xbo::select("From Warehouse", "warehouse");
		Xbo::select("From Rack", "racknum");
		Xbo::select("Batch Num.", "batchnum");
		Xbo::select("New Current Cost", "_input_text_newcurrentcost", "decimal");
		Xbo::select("Cost Adjustment Date", "_input_text_newcostadjustdate", "date");
		XboSet::getXboSet("invfifolifocost", $owner);
		XboSet::displayData();
	}
	public static function init(){
		InvFifoLifoCurbalAdjustment::displayData();
	}
	
}
InvFifoLifoCurbalAdjustment::init();
?>