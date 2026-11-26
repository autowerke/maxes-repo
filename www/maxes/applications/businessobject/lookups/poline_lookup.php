<?php 
include ("../eat.php");

class POLineLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		$where =null;
		$notcancel = "and po.status not in('cancel', 'closed')";	
		$ponum = Super::getParam("ponum");
		if(!empty($ponum)) $where  = "poline.ponum ='".$ponum."' $notcancel and linetype='services'";
		else $where = null;
		
		
		Xbo::select("Line Num.", "polinenum");
		Xbo::select("Item", "itemnum");
		Xbo::select("Description", "description");
		Xbo::select("Order Qty.", "orderqty", "decimal");
		Xbo::select("Order Unit", "orderunit");
		Xbo::select("Receipt Status", "receiptstatus");
		Xbo::select("Status", "po.status");
		XboSet::getXboSet("poline", $where);
		XboSet::displayData("poline.ponum = po.ponum");
	
	}
	
	public static function init(){
		POLineLookup::displayData();
	}
	
}

POLineLookup::init();




?>