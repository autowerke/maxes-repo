<?php 
include ("../eat.php");

class InvreserveWaitingList extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$itemnum = Super::getParam("itemnum");
		if(!empty($itemnum)) $where = "itemnum='".$itemnum."'";
		else $where = null;
		
		if($where != null) {
			$where .= " and total_waiting_qty > 0";
		}
		
		Xbo::select("Reserv. Status", "status");
		Xbo::select("Item", "itemnum");
		Xbo::select("Item Desc.", "itemdesc");		
		Xbo::select("Waiting Qty", "total_waiting_qty");
		Xbo::select("Reserved For", "reservedfor");
		Xbo::select("WO", "wonum");
		Xbo::select("Priority", "priority");
		Xbo::select("Targ. Start Date", "targstartdate", "date");
		Xbo::select("Asset", "assetnum");
		Xbo::select("WO Desc.", "description");
		XboSet::getXboSet("invreserve_waitinglist", $where);
		XboSet::displayData();
	}
	public static function init(){
		InvreserveWaitingList::displayData();
	}
	
}
InvreserveWaitingList::init();
?>