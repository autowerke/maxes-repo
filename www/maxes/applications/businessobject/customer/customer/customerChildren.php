<?php 
require_once ("../../eat.php");

class customerChildren {

	public static function init(){
		XboSet::validateChildren();	
		customerChildren::displayData();
	}
	
	
	public static function displayData(){
		$getOwner = XboSet::getOwner();
		$tableid = Xbo::getTableID();
		if($tableid=="mxtable_customercontacts_child"){
			Xbo::select("Sec. Contact Name", "contactname");
			Xbo::select("Function", "function");
			Xbo::select("WA", "wanum");
			Xbo::select("BB PIN", "blackberrypin");
			Xbo::select("Phone", "phone");
			Xbo::select("Email", "email");
			Xbo::select("Changed By", "changeby");
			Xbo::select("Changed Date", "changedate", "date");
			XboSet::getXboSet("customercontact", $getOwner);
			XboSet::displayData();
		}
		if($tableid=="mxtable_relatedcustomer_child"){
			Xbo::select("Related Customer", "relatedcustomercode");
			Xbo::select("Name", "customer.name");
			XboSet::getXboSet("relatedcustomer", $getOwner);
			XboSet::displayData("customer.customercode = relatedcustomer.relatedcustomercode");
		}
		
		if($tableid=="mxtable_customerbank_child"){ 
			Xbo::select("Bank Name", "bankname");
			Xbo::select("Account Number", "bankaccount");
			Xbo::select("Account Holder", "banktoaccountname");
			Xbo::select("Default Account? ", "isdefault");
			XboSet::getXboSet("customerbank", $getOwner);
			XboSet::displayData();
		}
		if($tableid=="mxtable_matsaletrans_child"){
			Xbo::select("Transaction Type", "transtype");
			Xbo::select("SO", "sonum");
			Xbo::select("Line Num.", "solinenum");
			Xbo::select("Item", "itemnum");
			Xbo::select("Description", "description");
			Xbo::select("Sold Qty", "quantity", "decimal");
			Xbo::select("Unit Price", "unitprice", "decimal");
			Xbo::select("Total  Price", "lineprice", "decimal");
			Xbo::select("Unit Profit", "unitprofit", "decimal");
			Xbo::select("Total Profit", "lineprofit", "decimal");
			Xbo::select("Entered By", "enterby");
			Xbo::select("Transaction Date", "enterdate", "date");
			XboSet::getXboSet("matsaletrans", $getOwner);
			XboSet::displayData();
		}
		if($tableid=="mxtable_servsaletrans_child"){
			Xbo::select("Transaction Type", "transtype");
			Xbo::select("SO", "sonum");
			Xbo::select("Line Num.", "solinenum");
			Xbo::select("Sold Qty", "quantity", "decimal");
			Xbo::select("Unit Price", "unitprice", "decimal");
			Xbo::select("Total  Price", "lineprice", "decimal");
			Xbo::select("Unit Profit", "unitprofit", "decimal");
			Xbo::select("Total Profit", "lineprofit", "decimal");
			Xbo::select("Entered By", "enterby");
			Xbo::select("Transaction Date", "enterdate", "date");
			XboSet::getXboSet("servsaletrans", $getOwner);
			XboSet::displayData();
		}
		if($tableid=="mxtable_soshipment_child"){
			
			Xbo::select("SO", "sonum");
			Xbo::select("Sold Qty", "couriername");
			Xbo::select("Tracking Num.", "trackingnum");
			Xbo::select("Shipping Cost", "shippingcost", "decimal");
			Xbo::select("Address", "address");
			Xbo::select("Received By", "receivedby");
			Xbo::select("Tgl. Diterima", "delivereddate", "date");
			Xbo::select("Remarks", "remarks");
			XboSet::getXboSet("soshipment", $getOwner);
			XboSet::displayData();
		}
		
		if($tableid=="mxtable_sotracking_child"){
			
			Xbo::select("SO", "sonum");
			Xbo::select("Tracking Line", "tracklinenum");
			Xbo::select("Type", "tracktype");
			Xbo::select("Description", "description");
			Xbo::select("Biaya", "cost", "decimal");
			XboSet::getXboSet("sotracking", $getOwner);
			XboSet::displayData();
		}
		if($tableid=="mxtable_matusetrans_child"){
			Xbo::select("Work Order", "wonum");
			Xbo::select("Item", "itemnum");
			Xbo::select("Description", "description");
			Xbo::select("Expired Date", "expireddate", "date");	
			Xbo::select("Quantity", "quantity", "decimal");
			Xbo::select("Unit Cost", "unitcost", "decimal");
			Xbo::select("Line Cost", "linecost", "decimal");
			Xbo::select("Transaction Type", "transtype");
			Xbo::select("Entered Date", "enterdate", "date");
			$output = str_replace("matusetrans", "workorder", $getOwner);
			XboSet::getXboSet("matusetrans", $output);
			XboSet::displayData(["workorder.wonum = matusetrans.wonum", "customer.customercode = workorder.customercode"]);
		}
		if($tableid=="mxtable_custbalance_child"){
			Xbo::select("Item", "itemnum");
			Xbo::select("Description", "item.description");
			Xbo::select("Condition Type", "conditiontype");	
			Xbo::select("Dropped Qty", "droppedqty", "decimal");
			Xbo::select("Issue Qty", "issuedqty", "decimal");		
			Xbo::select("Current Stock", "curbal", "decimal");
			Xbo::select("Issue Unit", "item.issueunit");
			Xbo::select("From Warehouse", "fromwarehouse");
			Xbo::select("From Rack", "fromracknum");
			Xbo::select("Expired Date", "expdate", "date");
			Xbo::select("WO", "wonum");
			Xbo::select("Batch Num.", "batchnum");
			Xbo::select("Shipment Status", "shipstatus");
			$output = str_replace("tocustomercode", "customercode", $getOwner);
			
			XboSet::getXboSet("custbalance", $output);
			XboSet::displayData(["custbalance.itemnum = item.itemnum"]);
		}
		if($tableid=="mxtable_custshipment_child"){			
			Xbo::select("WO", "wonum");
			Xbo::select("Item", "itemnum");
			Xbo::select("Description", "item.description");
			Xbo::select("From Customer", "customercode");
			Xbo::select("Customer Name", "customer.Name");	
			Xbo::select("Shipped Qty", "quantity", "decimal");
			Xbo::select("Issue Unit", "item.issueunit");
			Xbo::select("GPS Position", "longlat");
			Xbo::select("Status", "status");		
			Xbo::select("Status Date", "statusdate", "date");
			$getOwner = str_replace("tocustomercode", "customercode", $getOwner);
			$getOwner = str_replace("custbalance", "custshipment", $getOwner);
			XboSet::getXboSet("custshipment", $getOwner);
			XboSet::displayData(["custshipment.itemnum = item.itemnum", "custshipment.customercode = customer.customercode"]);
		}
	}
}

customerChildren::init();

?>