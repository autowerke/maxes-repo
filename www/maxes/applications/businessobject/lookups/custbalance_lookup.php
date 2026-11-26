<?php 
include ("../eat.php");

class CustBalanceLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$itemnum = Super::getParam("itemnum");
		$customercode = Super::getParam("customercode");
		if(!empty($itemnum) && !empty ($customercode)) $where  = "custbalance.itemnum ='".$itemnum."' and custbalance.customercode='".$customercode."' and curbal>0";		
		else $where = "curbal > 0";
		
		Xbo::select("Customer Code", "customercode");
		Xbo::select("Name", "customer.name");
		Xbo::select("Item", "itemnum");
		Xbo::select("Description", "item.description");
		Xbo::select("Expired Date", "expdate", "date");
		Xbo::select("WO", "wonum");
		Xbo::select("From Warehouse", "fromwarehouse");
		Xbo::select("From Rack", "fromracknum");
		Xbo::select("Condition Type", "conditiontype");
		Xbo::select("Current Stock", "curbal");
		Xbo::select("Returned Qty.", "_input_text_retqty", "decimal");
		Xbo::select("Warehouse", "_input_text_warehouse_warehouse");
		Xbo::select("Rack/Bin", "_input_text_racknum_rack@warehouse");
		Xbo::select("Tgl. Retur", "_input_text_actualdate", "date");
		XboSet::getXboSet("custbalance", $where);
		XboSet::displayData(["customer.customercode = custbalance.customercode", "custbalance.itemnum = item.itemnum"]);	
	}
	public static function init(){
		CustBalanceLookup::displayData();
	}
	
}
CustBalanceLookup::init();
?>