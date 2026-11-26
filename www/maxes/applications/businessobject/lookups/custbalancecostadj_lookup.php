<?php 
include ("../eat.php");

class custbalanceCostAdj extends Xbo {

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
		Xbo::select("Biaya Unit Skrg", "unitcost", "decimal");
		Xbo::select("Harga Unit Skrg", "unitprice", "decimal");
		Xbo::select("Harga Discount Skrg", "unitpricediscount", "decimal");
		Xbo::select("Harga Baru", "_input_text_newunitprice", "decimal");	
		Xbo::select("Discount 1", "_input_text_primarydiscount", "decimal");	
		Xbo::select("Discount 2", "_input_text_secondarydiscount", "decimal");
		Xbo::select("Commission (%)", "_input_text_unitcommissionpercent", "decimal");
		XboSet::getXboSet("custbalanceprice", $where);
		XboSet::displayData(["customer.customercode = custbalanceprice.customercode", "custbalanceprice.itemnum = item.itemnum"]);	
	}
	public static function init(){
		custbalanceCostAdj::displayData();
	}
	
}
custbalanceCostAdj::init();
?>