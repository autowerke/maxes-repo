<?php 
include ("../eat.php");

class custbalanceCurbalAdj extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$itemnum = Super::getParam("itemnum");
		$customercode = Super::getParam("customercode");
		if(!empty($itemnum) && !empty ($customercode)) $where  = "custbalanceprice.itemnum ='".$itemnum."' and custbalanceprice.customercode='".$customercode."'";		
		else $where = null;
		
		Xbo::select("Customer Code", "customercode");
		Xbo::select("Name", "customer.name");
		Xbo::select("Item", "itemnum");
		Xbo::select("Description", "item.description");
		Xbo::select("Expired Date", "expdate", "date");
		Xbo::select("WO", "wonum");
		Xbo::select("Condition Type", "conditiontype");
		Xbo::select("Current Stock", "curbal", "decimal");
		Xbo::select("Jml Baru", "_input_text_newqty", "decimal");
		XboSet::getXboSet("custbalanceprice", $where);
		XboSet::displayData(["customer.customercode = custbalanceprice.customercode", "custbalanceprice.itemnum = item.itemnum"]);	
	}
	public static function init(){
		custbalanceCurbalAdj::displayData();
	}
	
}
custbalanceCurbalAdj::init();
?>