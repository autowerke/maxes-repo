<?php 
include ("../eat.php");

class InvFifoLifoStockOpname extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$getOwner = XboSet::getOwner();
		Xbo::select("Item", "itemnum");
		Xbo::select("Cost Date", "costdate", "date");
		Xbo::select("Expired Date", "expdate", "date");
		Xbo::select("Warehouse", "warehouse");
		Xbo::select("Rack/Bin", "racknum");
		Xbo::select("Batch Num.", "batchnum");
		Xbo::select("Stock Before", "quantity", "decimal");
		Xbo::select("New Stock", "_input_text_newqty", "decimal");
		XboSet::getXboSet("invfifolifocost", $getOwner);
		XboSet::displayData();
	}
	public static function init(){
		InvFifoLifoStockOpname::displayData();
	}
	
}
InvFifoLifoStockOpname::init();
?>