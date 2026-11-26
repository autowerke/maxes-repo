<?php 
include ("../eat.php");

class CustShipment extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$itemnum = Super::getParam("itemnum");
		$customercode = Super::getParam("customercode");
		$default = " quantity >0 ";
		if(!empty($itemnum) && !empty ($customercode)) $where  = " $default and custshipment.itemnum ='".$itemnum."' and custshipment.customercode='".$customercode."' ";		
		else $where = $default;
		
		Xbo::select("WO", "wonum");
		Xbo::select("From Customer", "customercode");
		Xbo::select("Item", "itemnum");
		Xbo::select("Description", "item.description");	
		Xbo::select(To Customer, "tocustomercode");
		Xbo::select("Tgl. Kirim", "statusdate", "date");
		Xbo::select("Jml. Dikirim", "quantity");
		Xbo::select("Received Qty", "_input_text_receivedqty", "decimal");
		Xbo::select("Tgl. Diterima", "_input_text_actualdate", "date");
		XboSet::getXboSet("custshipment", $where);
		XboSet::displayData(["custshipment.itemnum = item.itemnum"]);	
	}
	public static function init(){
		CustShipment::displayData();
	}
	
}
CustShipment::init();
?>