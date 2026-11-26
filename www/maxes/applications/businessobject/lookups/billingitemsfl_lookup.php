<?php 
include ("../eat.php");

class BillingItemsFL extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$itemnum = Super::getParam("itemnum");
		$customercode = Super::getParam("customercode");
		if(!empty ($customercode)) $where  = "custbalancepricefl.customercode='".$customercode."'";		
		else $where = null;
		
		Xbo::select("Customer Code", "customercode");
		Xbo::select("Name", "customer.name");
		Xbo::select("Item", "itemnum");
		Xbo::select("Description", "description");
		Xbo::select("Specification", "spectext");
		Xbo::select("Condition Type", "conditiontype");
		Xbo::select("Stok Sekarang", "curbal", "decimal");
		Xbo::select("Issue Unit", "issueunit");
		Xbo::select("Harga/unit", "unitprice", "decimal");
		Xbo::select("Discount/unit", "unitpricediscount", "decimal");
		Xbo::select("Sold Qty", "_input_text_soldqty", "decimal");
		Xbo::select("Tgl. Terjual", "_input_text_actualdate", "date");
		Xbo::select("Issue Type", "_input_text_issuetype_issuetype");
		XboSet::getXboSet("custbalancepricefl", $where);
		XboSet::displayData(["customer.customercode = custbalancepricefl.customercode"]);	
	}
	public static function init(){
		BillingItemsFL::displayData();
	}
	
}
BillingItemsFL::init();
?>