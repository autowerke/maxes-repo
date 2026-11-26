<?php 
include ("../eat.php");

class InvreserveLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("WO", "wonum");
		Xbo::select("Description", "workorder.description");
		Xbo::select("WO Owner", "workorder.owner as owner");
		Xbo::select("Reservation Status", "status");
		Xbo::select("Item", "itemnum");
		Xbo::select("Item Desc", "item.description");
		Xbo::select("Requested Qty", "quantity", "decimal");
		Xbo::select("Issue Qty", "_input_text_issuedqty", "decimal");
		Xbo::select("Price Code", "_input_text_pricecode_invprice@itemnum,warehouse,conditiontype");
		Xbo::select("Unit", "item.issueunit");
		Xbo::select("Warehouse", "warehouse");
		Xbo::select("Rack/Bin", "racknum");
		Xbo::select("Issue Date", "_input_text_issuedate", "date");
		Xbo::select("Current Stock", "invbalance.curbal");
		Xbo::select("Condition Type", "conditiontype");
		Xbo::select("Reserve Num.", "invreservenum");
		Xbo::select("MR", "mrnum");
		XboSet::getXboSet("invreserve");
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