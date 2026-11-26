<?php 
include ("../eat.php");

class InvreserveReserved extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$itemnum = Super::getParam("itemnum");
		if(!empty($itemnum)) $where = "itemnum='".$itemnum."'";
		else $where = null;
		
		if($where != null) {
			$where .= " and total_reserved_qty > 0";
		}
		
		Xbo::select("Reserv. Status", "status");
		Xbo::select("Item", "itemnum");
		Xbo::select("Item Desc.", "itemdesc");		
		Xbo::select("Reserved Qty", "total_reserved_qty");
		Xbo::select("Reserved For", "reservedfor");
		Xbo::select("WO", "wonum");
		Xbo::select("Priority", "priority");
		Xbo::select("Targ. Start Date", "targstartdate", "date");
		Xbo::select("Asset", "assetnum");
		Xbo::select("WO Desc.", "description");
		XboSet::getXboSet("invreserve_reserved", $where);
		XboSet::displayData();
	}
	public static function init(){
		InvreserveReserved::displayData();
	}
	
}
InvreserveReserved::init();
?>