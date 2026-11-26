<?php 
include ("../eat.php");

class CustBalanceTransfer extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$itemnum = Super::getParam("itemnum");
		$customercode = Super::getParam("customercode");
		if(!empty($itemnum) && !empty ($customercode)) $where  = " custbalance.itemnum ='".$itemnum."' and custbalance.customercode='".$customercode."' and custbalance.curbal > 0";		
		else $where = "custbalance.curbal > 0";
		
		Xbo::select("WO", "wonum");
		Xbo::select("From Customer", "customer.name");
		Xbo::select("Customer Code", "customercode");
		Xbo::select("Item", "itemnum");
		Xbo::select("Description", "item.description");
		Xbo::select("Expired Date", "expdate", "date");
		Xbo::select("Status", "shipstatus");
		Xbo::select("Condition Type", "conditiontype");
		Xbo::select("Current Stock", "curbal");
		Xbo::select("Jml. Ditransfer", "_input_text_transferqty", "decimal");
		Xbo::select(To Customer, "_input_text_customercode_customer");
		Xbo::select("Status", "_input_text_shipstatus_shipstatuscustbal");
		Xbo::select("Transfered Date", "_input_text_actualdate", "date");
		XboSet::getXboSet("custbalance", $where);
		XboSet::displayData(["customer.customercode = custbalance.customercode", "custbalance.itemnum = item.itemnum"]);	
	}
	public static function init(){
		CustBalanceTransfer::displayData();
	}
	
}
CustBalanceTransfer::init();
?>