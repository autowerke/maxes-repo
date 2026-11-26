<?php 
require_once ("../../eat.php");

class BillingChildren {

	public static function init(){
		XboSet::validateChildren();	
		BillingChildren::displayData();
	}
	public static function displayData(){
		global $getOwnerCols;
		$getOwner = XboSet::getOwner();		
		$tableid = Xbo::getTableID();
		if($tableid=="mxtable_billingline_child"){
			Xbo::select("Line Num.", "billinglinenum");
			Xbo::select("Item", "itemnum");
			Xbo::select("Description", "description");
			Xbo::select("Condition", "conditiontype");
			Xbo::select("Quantity", "quantity", "decimal");
			Xbo::select("Unit Cost", "unitcost", "decimal");
			Xbo::select("Line Cost", "linecost", "decimal");
			Xbo::select("Unit Price", "unitprice", "decimal");
			Xbo::select("Line  Price", "lineprice", "decimal");
			Xbo::select("Unit Price Discount", "unitpricediscount", "decimal");
			Xbo::select("Line Price Discount", "linepricediscount", "decimal");		
			Xbo::select("Issue Unit", "issueunit");
			
			Xbo::select("Remarks", "Remarks");
			XboSet::getXboSet("billingline", $getOwner);
			XboSet::displayData();
		}
		
		if($tableid=="mxtable_billingpayment_child"){
			Xbo::select("Line Num.", "paymentlinenum");
			Xbo::select("Invoice", "Invoicenum");
			Xbo::select("Invoice Line Num.", "invoicelinenum");
			Xbo::select("Payment Date", "paymentdate", "date");
			Xbo::select("Discount", "discountprice", "decimal");
			Xbo::select("Paid", "paidprice", "decimal");
			Xbo::select("Payment Through", "paymentvia");
			Xbo::select("Invoice Date", "invoicedate", "date");
			Xbo::select("Item", "enterby");
			XboSet::getXboSet("billingpayment", $getOwner);
			XboSet::displayData();
		}
		if($tableid=="mxtable_custusetrans_child"){
			Xbo::select("Paid Date", "actualdate", "date");
			Xbo::select("Item", "itemnum");
			Xbo::select("Description", "description");
			Xbo::select("Refund", "Refund");
			Xbo::select("Jumlah", "quantity", "decimal");
			Xbo::select("Billing", "billingnum");
			Xbo::select("Trans. Type", "transtype");
			Xbo::select("Line Num.", "billinglinenum");
			Xbo::select("Discount", "discountprice", "decimal");
			XboSet::getXboSet("custusetrans", $getOwner);
			XboSet::displayData();
		}
		
		
	}
}

BillingChildren::init();

?>