<?php 
include ("../eat.php");

class InvreserveLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$whereclause = "invreserve.status='WAITING_LIST'";
		
		if(isset($_GET['requester'])) {
			$reservedfor = $_GET["requester"];
			$whereclause .= " and invreserve.reservedfor='$reservedfor'";
		}
		if(isset($_GET['itemnum'])) {
			$itemnum = $_GET["itemnum"];
			$whereclause .= " and invreserve.itemnum='$itemnum'";
		}
		
		
		
		
		Xbo::select("WO", "wonum");
		Xbo::select("Description", "workorder.description");
		Xbo::select("WO Owner", "workorder.owner as owner");
		Xbo::select("Item", "itemnum");
		Xbo::select("Item Desc", "item.description");
		Xbo::select("Reserved Qty", "quantity", "decimal");
		Xbo::select("Qty to Swap", "_input_text_requestedqty", "decimal");	
		Xbo::select("Unit", "item.issueunit");
		Xbo::select("Warehouse", "warehouse");
		Xbo::select("Rack/Bin", "racknum");	
		Xbo::select("Current Stock", "invbalance.curbal");
		Xbo::select("Condition Type", "conditiontype");
		Xbo::select("Reserve Num.", "invreservenum");
		Xbo::select("MR", "mrnum");
		Xbo::select("Status", "status");
		XboSet::getXboSet("invreserve", $whereclause);
		XboSet::displayData(["workorder.wonum = invreserve.wonum", 
							"invreserve.itemnum = item.itemnum", 
							"invbalance.itemnum = invreserve.itemnum and invbalance.warehouse=invreserve.warehouse and 
			(invbalance.racknum = invreserve.racknum
			or isnull(invreserve.racknum)) and 
			(invbalance.conditiontype = invreserve.conditiontype
			or isnull(invreserve.conditiontype))"]);	
	}
	public static function init(){
		InvreserveLookup::displayData();
	}
	
}
InvreserveLookup::init();
?>