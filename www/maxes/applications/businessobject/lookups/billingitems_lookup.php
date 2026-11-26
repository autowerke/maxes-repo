<?php 
include ("../eat.php");

class BillingItems extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$itemnum = Super::getParam("itemnum");
		$customercode = Super::getParam("customercode");
		
		$wccurbal = "curbal>0";
		if(!empty ($customercode)) $where  = "$wccurbal and custbalanceprice.customercode='".$customercode."'";		
		else $where = $wccurbal;
		
		Xbo::select("Customer Code", "customercode");
		Xbo::select("Name", "customer.name");
		Xbo::select("Item", "itemnum");
		Xbo::select("Description", "item.description");
		Xbo::select("Expired Date", "expdate", "date");
		Xbo::select("Tgl. Masuk FIFO/LIFO", "costdate", "date");
		Xbo::select("WO", "wonum");
		Xbo::select("Condition Type", "conditiontype");
		Xbo::select("Stok Sekarang", "curbal", "decimal");
		Xbo::select("Unit Cost", "unitcost", "decimal");
		Xbo::select("Unit Price", "unitprice", "decimal");
		Xbo::select("Discount per Unit", "unitpricediscount", "decimal");
		Xbo::select("Jumlah Terjual", "_input_text_soldqty", "decimal");
		Xbo::select("Tgl. Terjual", "_input_text_actualdate", "date");
		XboSet::getXboSet("custbalanceprice", $where);
		XboSet::displayData(["customer.customercode = custbalanceprice.customercode", "custbalanceprice.itemnum = item.itemnum"]);	
	}
	public static function init(){
		BillingItems::displayData();
	}
	
}
BillingItems::init();
?>