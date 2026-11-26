<?php 
include ("../eat.php");

class PRLineLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		$prnum = Super::getParam("prnum");
		if(!empty($prnum)) $isprnum  = "prline.prnum ='$prnum' and prline.prlinenum not in ( select prlinenum from woplanservice where prnum='$prnum' ) and ";
		else $isprnum = "";
		
		$notcancel = "pr.status not in('closed', 'cancel')";
		$where  = "$isprnum $notcancel ";
		Xbo::select("Line Num.", "prlinenum");
		Xbo::select("PR", "pr.prnum");
		
		Xbo::select("Item", "itemnum");
		Xbo::select("Description", "description");
		Xbo::select("Order Qty.", "orderqty", "decimal");
		Xbo::select("Order Unit", "orderunit");
		Xbo::select("Requested Date", "pr.requesteddate", "date");
		Xbo::select("Requested By", "pr.requestedby");
		Xbo::select("Warehouse", "warehouse");
		Xbo::select("Work Order", "wonum");
		XboSet::getXboSet("prline", $where);
		XboSet::displayData("prline.prnum = pr.prnum");
	
	}
	
	public static function init(){
		PRLineLookup::displayData();
	}
	
}

PRLineLookup::init();




?>