<?php 
require_once ("../../eat.php");

class AppChildren{
	public static function Change(){
		
		
	}
}
class InventoryChildren {
	
	public static function init(){
		$ownerPayload = Super::getParam("ownership");
		$childPayload = Super::getParam("childPackage");

		$ownerTable = $ownerPayload["xboset"];
		$ownerid = $ownerPayload["xbosetid"];
		$itemnum = $ownerPayload["owner"][0]["itemnum"];
		$warehouse = $ownerPayload["owner"][1]["warehouse"];
		
		$childrenTable = Super::getParam("childrentable");

		if(!empty($childPayload)) {
			
			if($childrenTable=="invprice"){
				
				$pricecode = $childPayload["pricecode"];
				$defaultprice = $childPayload["defaultprice"];
				$sitecode = $childPayload["orgcode"];
				$orgcode = $childPayload["sitecode"];
				$unitprice = $childPayload["unitprice"];
				$pricerate = (new Set())->useTransactionManager()->MboSetRemote("pricegroup")->where("pricecode=?", $pricecode)->getMboValue("pricerate");
				
				$invpriceSet = (new Set())->useTransactionManager()->MboSetRemote("invprice")->where("itemnum=? and warehouse=?", $itemnum, $warehouse)->getMboSet();
				if($invpriceSet->isEmpty() && $pricerate == 100){
					XboSet::validateChildren();		
				}else{
					Super::sendJsonResponse("error", "You must enter price code for rate 100% first (NEW).");
				}
			}else{
				XboSet::validateChildren();	
			}
		}
		InventoryChildren::displayData();
	}
	public static function displayData(){
		
		global $getOwnerCols;
		$getOwner = XboSet::getOwner();	
		$tableid = Xbo::getTableID();
		if($tableid=="mxtable_invcart_child"){
			Xbo::select("Item", "itemnum");
			Xbo::select("Description", "item.description");
			Xbo::select("Replenish From", "replenishfrom");
			Xbo::select("Remarks", "remarks");
			Xbo::select("Condition", "conditiontype");
			Xbo::select("Warehouse", "warehouse");
			Xbo::select("Rack", "racknum");
			Xbo::select("Quantity", "quantity", "decimal");
			Xbo::select("Unit Cost", "unitcost", "decimal");
			Xbo::select("Line Cost", "linecost", "decimal");
			XboSet::getXboSet("invcart");
			XboSet::displayData(["invcart.itemnum = item.itemnum"]);
		}
		if($tableid=="mxtable_invbalances_child"){
			Xbo::select("Condition", "conditiontype");
			Xbo::select("Rack", "racknum");
			Xbo::select("Current Balance", "curbal", "decimal");
			Xbo::select("Phys. Count", "physcount", "decimal");
			Xbo::select("Average Cost", "avgcost", "decimal");
			XboSet::getXboSet("v_invbalance", $getOwner);
			XboSet::displayData();
		}
		if($tableid=="mxtable_invcost_child"){
			Xbo::select("Condition", "conditiontype");
			Xbo::select("Condition Rate (%)", "conditionrate");
			Xbo::select("Standar Cost", "stdcost", "decimal");
			Xbo::select("Average Cost", "avgcost", "decimal");
			Xbo::select("Last Cost", "lastcost", "decimal");
			XboSet::getXboSet("invcost", $getOwner);
			XboSet::displayData();
		}
		if($tableid=="mxtable_invfifolifocost_child"){
			Xbo::select("Cost Date", "costdate", "date");
			Xbo::select("Expired Date", "expdate", "date");
			Xbo::select("Quantity", "quantity", "decimal");
			Xbo::select("Rack", "racknum");
			Xbo::select("Batch.", "batchnum");
			Xbo::select("PO", "ponum");
			Xbo::select("Vendor", "vendorcode");
			Xbo::select("Vendor Name", "vendor.name");
			Xbo::select("WO#", "wonum");
			Xbo::select("Unit COst", "unitcost", "decimal");
			Xbo::select("Condition Rate", "conditionrate", "decimal");
			Xbo::select("Financial Period", "financialperiod");
			XboSet::getXboSet("invfifolifocost", $getOwner);
			XboSet::displayData(["vendor.vendorcode = invfifolifocost.vendorcode"]);
		}
		if($tableid=="mxtable_invprice_child"){
			Xbo::select("Condition", "conditiontype");
			Xbo::select("Price Category", "pricecode");
			Xbo::select("Unit Price", "unitprice", "decimal");
			Xbo::select("Discount #1", "primarydiscount", "decimal");
			Xbo::select("Discount #2", "secondarydiscount", "decimal");
			Xbo::select("Default Price", "defaultprice");
			Xbo::select("Is Lesser Price?", "pricegroup.islesser");
			XboSet::getXboSet("invprice", $getOwner);
			XboSet::displayData("invprice.pricecode = pricegroup.pricecode");
		}
		
		if($tableid=="mxtable_invvendor_child"){
			Xbo::select("Vendor", "vendorcode");
			Xbo::select("Vendor Name", "vendor.name");
			Xbo::select("Internal Lead Time(Days)", "intleadtime", "decimal");
			Xbo::select("External Lead Time (Days)", "extleadtime", "decimal");
			Xbo::select("Lead Time (Days)", "leadtime", "decimal");
			Xbo::select("Last PO", "lastponum");
			Xbo::select("Last Price", "lastprice", "decimal");
			Xbo::select("Last Order Date", "lastorderdate", "date");
			XboSet::getXboSet("invvendor", $getOwner);
			XboSet::displayData(["invvendor.vendorcode = vendor.vendorcode"]);
		}
		if($tableid=="mxtable_receipt_child"){
		
			Xbo::select("Transaction Type", "transtype");
			Xbo::select("Financial Period", "financialperiod");
			Xbo::select("PO", "ponum");
			Xbo::select("Line", "polinenum");
			Xbo::select("Rec. Qty", "quantity", "decimal");
			Xbo::select("Order Unit", "poline.orderunit");
			Xbo::select("Unit Cost", "unitcost", "decimal");
			Xbo::select("Total Cost", "linecost", "decimal");
			Xbo::select("Entered By", "enterby");
			Xbo::select("Entered Date", "enterdate", "date");
			XboSet::getXboSet("matrectrans", $getOwner." and transtype not in ('TRANSFER')");
			XboSet::displayData("matrectrans.ponum = poline.ponum and matrectrans.polinenum = poline.polinenum");
		}
		if($tableid=="mxtable_transfer_child"){
			
			Xbo::select("Financial Period", "financialperiod");
			Xbo::select("From Warehouse", "warehouse");
			Xbo::select("From Rack", "racknum");
			Xbo::select("To Warehouse", "towarehouse");
			Xbo::select("To Rack", "toracknum");
			Xbo::select("Qty", "quantity", "decimal");
			Xbo::select("Unit Cost", "unitcost", "decimal");
			Xbo::select("Line Cost", "linecost", "decimal");
			Xbo::select("Entered By", "enterby");
			Xbo::select("Entered Date", "enterdate", "date");
			XboSet::getXboSet("matrectrans", $getOwner." and transtype='TRANSFER'");
			XboSet::displayData();
		}
		if($tableid=="mxtable_matsaletrans_child"){
			Xbo::select("Transaction Type", "transtype");
			Xbo::select("Financial Period", "financialperiod");
			Xbo::select("Sales Order", "sonum");
			Xbo::select("Line", "solinenum");
			Xbo::select("Condition", "conditiontype");
			Xbo::select("Customer", "customercode");
			Xbo::select("Price Code", "pricecode");
			Xbo::select("Quantity", "quantity", "decimal");
			Xbo::select("Unit Price", "unitprice", "decimal");
			Xbo::select("Line Price", "lineprice", "decimal");
			Xbo::select("Cost Type", "costtype");
			Xbo::select("Unit Profit", "unitprofit", "decimal");
			Xbo::select("Line Profit", "lineprofit", "decimal");
			XboSet::getXboSet("matsaletrans", $getOwner);
			XboSet::displayData();
		}
		if($tableid=="mxtable_invtrans_child"){
			Xbo::select("Transaction Date", "enterdate", "date");
			Xbo::select("Transaction Type", "transtype");
			Xbo::select("Financial Period", "financialperiod");
			Xbo::select("Condition", "conditiontype");
			Xbo::select("Average Cost", "avgcost", "decimal");
			Xbo::select("Prev. Qty", "oldquantity", "decimal");
			Xbo::select("Variance Qty", "varianceqty", "decimal");
			Xbo::select("Qty", "quantity", "decimal");
			Xbo::select("Prev Cost", "oldcost", "decimal");
			Xbo::select("Variance Cost", "variancecost", "decimal");
			Xbo::select("Cost", "cost", "decimal");
			XboSet::getXboSet("invtrans", $getOwner);
			XboSet::displayData();
		}
		if($tableid=="mxtable_matusetrans_child"){
			Xbo::select("Transaction Type", "transtype");
			Xbo::select("Work Order#", "wonum");
			Xbo::select("Location", "location");
			Xbo::select("Quantity", "quantity", "decimal");
			Xbo::select("Unit Cost", "unitcost", "decimal");
			Xbo::select("Line Cost", "linecost", "decimal");
			Xbo::select("Entered By", "enterby");
			Xbo::select("Entered Date", "enterdate", "date");
			XboSet::getXboSet("matusetrans", $getOwner);
			XboSet::displayData();
		}
		if($tableid=="mxtable_invconversion_child"){
			Xbo::select("Issue Unit", "item.issueunit");
			Xbo::select("To Measure Unit", "tomeasureunit");
			Xbo::select("Curbal Conversion", "curbalconversion", "decimal");
			Xbo::select("Conversion", "conversion", "decimal");
			Xbo::select("Description", "measureunit.description");
			XboSet::getXboSet("invconversion", $getOwner);
			XboSet::displayData(["item.itemnum = invconversion.itemnum","invconversion.tomeasureunit = measureunit.measureunitcode"]);
		}
	}
}

InventoryChildren::init();

?>