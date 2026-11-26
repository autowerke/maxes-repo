<?php 
include ("../eat.php");

class InvFifoLifoStruct extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$whereclause = Super::getParam("whereclause");
		if(!empty($whereclause)) $where = "( $whereclause )";
		else $where = null;
		Xbo::select("Item", "itemnum");
		Xbo::select("Description", "item.description");
		Xbo::select("Cost Date", "costdate", "date");
		Xbo::select("Expired Date", "expdate", "date");
		Xbo::select("Warehouse", "warehouse");
		Xbo::select("Rack", "racknum");
		Xbo::select("Batch Num.", "batchnum");
		Xbo::select("Quantity", "quantity", "decimal");
		Xbo::select("Unit Cost", "unitcost", "decimal");
		Xbo::select("Disassmble Qty", "_input_text_dissassmbleqty", "decimal");

		XboSet::getXboSet("invfifolifocost", $where. " and quantity >0");
		XboSet::displayData(["invfifolifocost.itemnum=item.itemnum"]);
	}
	public static function init(){
		InvFifoLifoStruct::displayData();
	}
	
}
InvFifoLifoStruct::init();
?>