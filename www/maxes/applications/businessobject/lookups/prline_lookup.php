<?php 
include ("../eat.php");

class PRLineLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		$defaultjoin = ["pr.prnum = prline.prnum"];
		$where  = "pr.status in('approved')";
		
		Xbo::select("PR", "prnum");
		Xbo::select("Line Num.", "prlinenum");
		Xbo::select("Item", "itemnum");
		Xbo::select("Description", "description");
		Xbo::select("Order Qty.", "orderqty", "decimal");
		Xbo::select("Order Unit", "orderunit");
		Xbo::select("Warehouse", "warehouse");
		Xbo::select("WO", "wonum");
		Xbo::select("Requested Date", "pr.requesteddate", "date");
		Xbo::select("Requested By", "pr.requestedby");
		XboSet::getXboSet("prline", $where);
		XboSet::displayData($defaultjoin);

	}
	
	public static function init(){
		PRLineLookup::displayData();
	}
	
}

PRLineLookup::init();




?>