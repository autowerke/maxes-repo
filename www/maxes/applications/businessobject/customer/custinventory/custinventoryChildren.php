<?php 
require_once ("../../eat.php");

class CustInventoryChildren {

	public static function init(){
		XboSet::validateChildren();	
		CustInventoryChildren::displayData();
	}
	
	
	public static function displayData(){
		$getOwner = XboSet::getOwner();
		$tableid = Xbo::getTableID();
		
		if($tableid=="mxtable_custbalance_child"){
			Xbo::select("WO", "wonum");
			Xbo::select("Arrived Date", "arriveddate", "date");		
			Xbo::select("Condition Type", "conditiontype");		
			Xbo::select("Current Stock", "curbal", "decimal");
			Xbo::select("From Warehouse", "fromwarehouse");
			Xbo::select("From Rack", "fromracknum");
			Xbo::select("Expired Date", "expdate", "date");
			Xbo::select("Batch Num.", "batchnum");
			Xbo::select("Issue Type.", "issuetype");
			Xbo::select("Unit Price", "unitprice", "decimal");
			Xbo::select("Shipment Status", "shipstatus");
			Xbo::select("Unit Price Discount", "unitpricediscount", "decimal");
			XboSet::getXboSet("custbalanceprice", "custbalanceprice.".$getOwner);
			XboSet::displayData(["custbalanceprice.itemnum = item.itemnum"]);
		}
		if($tableid=="mxtable_custinvtrans_child"){
			Xbo::select("From Customer", "customercode");
			Xbo::select(To Customer, "tocustomercode");
			Xbo::select("WO", "wonum");			
			Xbo::select("Quantity", "quantity", "decimal");
			Xbo::select("Expired Date", "expdate", "date");
			Xbo::select("Cost Date", "costdate", "date");
			Xbo::select("Transfered Date", "transferdate", "date");
			Xbo::select("Batch Num.", "batchnum");
			Xbo::select("Shipment Status", "shipstatus");
			XboSet::getXboSet("custinvtrans", $getOwner. " and transtype = 'TRANSFER'");
			XboSet::displayData();
		}
		
		if($tableid=="mxtable_custinvtransreturn_child"){
			Xbo::select("From Customer", "customercode");
			Xbo::select("To Warehouse", "towarehouse");
			Xbo::select("To Rack/Bin", "toracknum");
			Xbo::select("Work Order", "wonum");
			Xbo::select("Condition Type", "conditiontype");
			Xbo::select("Quantity", "quantity", "decimal");
			Xbo::select("Line Discount Price", "linepricediscount", "decimal");
			Xbo::select("Line Profit Discount", "lineprofitdiscount", "decimal");
			XboSet::getXboSet("custinvtrans", $getOwner. " and transtype = 'RETURN'");
			XboSet::displayData();
		}
		if($tableid=="mxtable_custinvtransqtyadjust_child"){
			Xbo::select("WO", "wonum");				
			Xbo::select("Stock Before", "oldqty", "decimal");
			Xbo::select("Variance Stock", "varianceqty", "decimal");
			Xbo::select("New Stock", "quantity", "decimal");
			Xbo::select("Variance Price", "varianceprice", "decimal");
			Xbo::select("Variance Discount Price", "variancepricediscount", "decimal");
			Xbo::select("Variance Profit", "varianceprofit", "decimal");
			Xbo::select("Variance Discount Profit", "varianceprofitdiscount", "decimal");
			XboSet::getXboSet("custinvtrans", $getOwner. " and transtype = 'CURBALADJ'");
			XboSet::displayData();
		}
		
		if($tableid=="mxtable_custinvtranscostadjust_child"){
			Xbo::select("WO", "wonum");			
			Xbo::select("Quantity", "quantity", "decimal");
			Xbo::select("Previous Unit Price Discount", "oldunitpricediscount", "decimal");
			Xbo::select("Previous Unit Profit Discount", "oldunitprofitdiscount", "decimal");
			Xbo::select("New Unit Price Discount", "unitpricediscount", "decimal");
			Xbo::select("New Unit Profit Discount", "unitprofitdiscount", "decimal");
			Xbo::select("Variance Discount Price", "variancepricediscount", "decimal");
			Xbo::select("Variance Discount Profit", "varianceprofitdiscount", "decimal");
			XboSet::getXboSet("custinvtrans", $getOwner. " and transtype = 'PRICEADJ'");
			XboSet::displayData();
		}
		
		
	}
}

CustInventoryChildren::init();

?>