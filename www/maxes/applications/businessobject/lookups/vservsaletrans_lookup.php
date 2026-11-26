<?php 
include ("../eat.php");

class ViewServSaleTrans extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$where = null;
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
        Xbo::select("serviceitemnum", "serviceitemnum");
		Xbo::select("Description", "description");
		Xbo::select("Customer", "customercode");
		Xbo::select("Sold Qty" , "sellqty", "decimal");
		Xbo::select("Measure Unit", "orderunit");
		Xbo::select("Received Qty", "receivedqty", "decimal");
		Xbo::select("Remaining Qty", "remainingqty", "decimal");
		Xbo::select($column_title, "_input_text_deliveredqty", "decimal");
		Xbo::select("Status", "fulfillment_status");
		XboSet::getXboSet("v_servsaletrans", $where);
		XboSet::displayData();
	}
	public static function init(){
		ViewServSaleTrans::displayData();
	}
	
}
ViewServSaleTrans::init();
?>