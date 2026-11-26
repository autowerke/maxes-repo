<?php 
include ("../eat.php");

class InvFifoLifoConversion extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		if(Super::hasParam("itemnum")){
            $itemnum = Super::getParam("itemnum");
            $getOwner = "invfifolifocost.itemnum='" . $itemnum . "' and quantity>0";
        } else {
            $getOwner = "";
        }

		Xbo::select("Item", "invfifolifocost.itemnum");
		Xbo::select("Cost Date", "costdate", "date");
		Xbo::select("Expired Date", "expdate", "date");
		Xbo::select("Quantity", "quantity", "decimal");
		Xbo::select("From Warehouse", "warehouse");
		Xbo::select("From Rack", "racknum");
		Xbo::select("Batch Num.", "batchnum");
		Xbo::select("Conversion Qty", "_input_text_conversionqty", "decimal");
		Xbo::select("Conversion Date", "_input_text_conversiondate", "date");
		Xbo::select("To Location.", "_input_text_tolocation_locations");
		XboSet::getXboSet("invfifolifocost", $getOwner);
		XboSet::displayData();
	}
	public static function init(){
		InvFifoLifoConversion::displayData();
	}
	
}
InvFifoLifoConversion::init();
?>