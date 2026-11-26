<?php 
include ("../eat.php");

class MatsaleLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		$customercode = Super::getParam("customercode");
		if(!empty($customercode)) $where = "customercode ='".$customercode."' ";	
		else  $where = null;
		
		Xbo::select("Kode Transaksi", "transnum");
		Xbo::select("Customer Code", "customercode");
		Xbo::select("Type", "linetype");
		Xbo::select("Item", "itemnum");
		Xbo::select("Issue Type", "costtype");
		Xbo::select("Sold Qty", "quantity", "decimal");
		Xbo::select("Unit Price", "unitprice", "decimal");
		Xbo::select("Total Price", "lineprice", "decimal");
		Xbo::select("Unit Price Discount", "unitpricediscount", "decimal");
		Xbo::select("Total Price Discount", "linepricediscount", "decimal");
		Xbo::select("Tax Code", "_input_text_taxcode_taxpurchasing");
		Xbo::select("Include Tax?", "_input_checkbox_hastaxed");
		XboSet::getXboSet("matsaleimport", $where);
		XboSet::displayData();
	
	}
	
	public static function init(){
		MatsaleLookup::displayData();
	}
	
}

MatsaleLookup::init();




?>