<?php 
include ("../eat.php");

class ViewMatSaleTrans extends Xbo {

	public function __destruct()
    {
        return null;
    }


	public static function displayData(){
		$where = null;
		$context = null;
		$column_title = "Deliv'd Qty";
		if(Super::hasParam("sonum")) {
			$sonum = Super::getParam("sonum");
			$where = "sonum = '$sonum'";
		} 
		if(Super::hasParam("context")){
			$context = Super::getParam("context");
			if($context == "return") $column_title = "Returned Qty";
		}

		Xbo::select("SO", "sonum");
		Xbo::select("Line", "solinenum");
		Xbo::select("Item", "itemnum");
		Xbo::select("Description", "description");
		Xbo::select("Warehouse", "warehouse");
		Xbo::select("Sold Qty" , "sellqty", "decimal");
		Xbo::select("Measure Unit", "orderunit");
		Xbo::select("Received. Qty", "receivedqty", "decimal");
		Xbo::select("Remaining Qty", "remainingqty", "decimal");
		Xbo::select($column_title, "_input_text_deliveredqty", "decimal");
		
		if($context == "return"){
			Xbo::select("To Warehouse", "_input_text_towarehouse_warehouse");
			Xbo::select("To Rack", "_input_text_toracknum_rack@towarehouse");
		}
		Xbo::select("Status", "fulfillment_status");
		
		XboSet::getXboSet("v_matsaletrans", $where);
		XboSet::displayData();
	}
	public static function init(){
		ViewMatSaleTrans::displayData();
	}
	
}
ViewMatSaleTrans::init();
?>