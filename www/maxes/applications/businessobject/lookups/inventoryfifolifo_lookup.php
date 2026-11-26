<?php 
include ("../eat.php");

class InvFifoLifoTransfer extends Xbo {

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
		Xbo::select("Transfered Qty", "_input_text_transferqty", "decimal");
		Xbo::select("Transfered Date", "_input_text_transferdate", "date");
		Xbo::select("To Warehouse.", "_input_text_towarehouse_warehouse");
		Xbo::select("To Rack/Bin", "_input_text_toracknum_rack@towarehouse");
		XboSet::getXboSet("invfifolifocost", $owner. " and quantity>0");
		XboSet::displayData();
	}
	public static function init(){
		InvFifoLifoTransfer::displayData();
	}
	
}
InvFifoLifoTransfer::init();
?>