<?php 
require_once ("../../eat.php");

class addCustomerCode extends SOChildren{
	public static function updateCustomerCode(){
		global $ownerCols;
		global $childTable;
		$_GET['package'] = $ownerCols;
		$sonum = Xbo::getString("sonum");
		if(!empty($sonum)){
			$set  = new Set();
			$customercode = $set->MboSetRemote("so")->setWhere("sonum='$sonum'")->MboRemote("customercode")->getMboSet()->getMbo()->getString("customercode");
			$set->MboSetRemote($childTable)->setWhere("sonum='$sonum' and customercode is null")->setValue("customercode", $customercode)->change();
		}
	}
}
class soChildren {

	public static function init(){
		XboSet::validateChildren();	
		soChildren::displayData();
		addCustomerCode::updateCustomerCode();
		
		if (isset($_POST['childPackage'])) {
			$childPackage = urldecode($_POST['childPackage']);
			$packageParts = explode("|", $childPackage);
			$ownerTable = $packageParts[0];
			$ownerCols = $packageParts[1];
			$childTable = $packageParts[2];
			$childCols = explode("^", $packageParts[3]);
			if($childTable=="soline"){
				
				$sonum = explode("`", $ownerCols)[1];
				$wonum = Super::getStringArray($childCols, "wonum");
				
				$set = new Set();
				$counts= $set->MboSetRemote("soline")->setWhere("sonum='$sonum' and wonum='$wonum'")->MboRemote("sonum")->getMboSet()->counts();
				
				
				$actualcost = $set->MboSetRemote("relatedworkorder")->setRelationship("workorder", "relatedworkorder.relatedwonum = workorder.wonum")
				->setWhere("relatedworkorder.relatedwonum='$wonum' and relatedworkorder.sonum='$sonum'")->MboRemote("workorder.actualcost")->getMboSet()->getMbo()
				->getString("actualcost");
				$distributedCost = $actualcost/$counts;
				
				$up1 = "update soline set unitcost='$distributedCost', unitprice='$distributedCost' where sonum='$sonum' and wonum='$wonum'";
				DB::exec($up1);
				$up1_ = "update soline set lineprice=unitprice*sellqty where sonum='$sonum' and wonum='$wonum'";
				DB::exec($up1_);
				
				$up1 = "update soline set unitprofit=unitprice-unitcost where sonum='$sonum' and wonum='$wonum'";
				DB::exec($up1);
				$up1=  "update soline set lineprofit = unitprofit*sellqty where sonum='$sonum' and wonum='$wonum'";
				DB::exec($up1);
				
				
			}
		}
	}
	public static function displayData(){
		$getOwner = XboSet::getOwner();
		$tableid = Xbo::getTableID();
		
		if($tableid=="mxtable_workorder_child"){
			Xbo::select("Work Order", "wonum");
			Xbo::select("Description", "description");
			Xbo::select("Work Type", "worktype");
			Xbo::select("Work Classification", "workclass");
			Xbo::select("Actual Start Date", "actstartdate", "date");
			Xbo::select("Actual Finish Date", "actfinishdate", "date");
			Xbo::select("Actual Cost", "actualcost", "decimal");
			XboSet::getXboSet("workorder", $getOwner. " and status not in('cancel')");
			XboSet::displayData();
			exit();
		}
		
		if($tableid=="mxtable_solinewo_child"){
			Xbo::select("SO Distribution Line Num.", "sodistlinenum");
			Xbo::select("For SO Line", "solinenum");
			Xbo::select("Description", "description");
			Xbo::select("Location", "location");
			Xbo::select("Budget", "budgetnum");
			Xbo::select("Budget Line", "budgetlinenum");
			Xbo::select("Budget Line", "budgetline.budgetlinecode");
			Xbo::select("Unit Cost", "unitcost", "decimal");
			Xbo::select("Line Cost", "linecost", "decimal");
			Xbo::select("WO#", "wonum");
			XboSet::getXboSet("solinewo", $getOwner);
			XboSet::displayData(["solinewo.budgetnum = budgetline.budgetnum AND solinewo.budgetlinenum = budgetline.budgetlinenum"]);
			exit();
		}
		
		
		if($tableid=="mxtable_solines_child"){
			Xbo::select("Line Num.", "solinenum");
			Xbo::select("Line Type", "linetype");
			Xbo::select("Item", "itemnum");
			Xbo::select("Description", "description");
			Xbo::select("Condition Code", "conditiontype");
			Xbo::select("Quantity", "sellqty", "decimal");
			Xbo::select("Unit Price", "unitprice", "decimal");
			Xbo::select("Line Price", "lineprice", "decimal");
			Xbo::select("Delivery Status", "sentstatus");
			XboSet::getXboSet("soline", $getOwner);
			XboSet::displayData();
			exit();
		}
		if($tableid=="mxtable_relatedworkorder_child"){
			Xbo::select("Work Order", "relatedwonum");
			Xbo::select("Customer Code", "workorder.customercode");
			Xbo::select("Description", "workorder.description");
			Xbo::select("Status", "workorder.status");
			Xbo::select("Target Start Date", "workorder.targstartdate", "date");
			Xbo::select("Target Finish Date", "workorder.targcompdate", "date");
			Xbo::select("Work Type", "workorder.worktype");
			Xbo::select("Work Classification", "workorder.workclass");
			Xbo::select("Planning Cost", "workorder.plannedcost", "decimal");
			Xbo::select("Actual Cost", "workorder.actualcost", "decimal");
			XboSet::getXboSet("relatedworkorder", $getOwner);
			XboSet::displayData("relatedworkorder.relatedwonum = workorder.wonum");
			exit();
		}
		if($tableid=="mxtable_shipment_child"){
			Xbo::select("Courier", "couriername");
			Xbo::select("Tracking Num.", "trackingnum");
			Xbo::select("Ship Date", "shippingdate");
			Xbo::select("Address", "address");
			Xbo::select("Received By", "receivedby");
			Xbo::select("Phone", "phone");
			Xbo::select("Delivered Date", "delivereddate", "date");
			Xbo::select("Remarks", "remarks");
			Xbo::select("Shipping Cost", "shippingcost", "decimal");
			XboSet::getXboSet("soshipment", $getOwner);
			XboSet::displayData();
			exit();
		}
		if($tableid=="mxtable_paymentlines_child"){
			Xbo::select("Payment Line", "paymentlinenum");
			Xbo::select("Payment Date", "paymentdate", "date");
			Xbo::select("Remarks", "remarks");
			Xbo::select("Invoice", "invoicenum");
			Xbo::select("Invoice Line", "Invoicelinenum");
			Xbo::select("Discount Invoice", "invoicediscount", "decimal");
			Xbo::select("Paid", "paidprice", "decimal");
			XboSet::getXboSet("sopayment", $getOwner);
			XboSet::displayData();
			exit();
		}
		if($tableid=="mxtable_sotracking_child"){
			Xbo::select("Tracking Type", "tracktype");
			Xbo::select("Description", "description");
			Xbo::select("Cost", "cost", "decimal");
			Xbo::select("Enter By", "enterby");
			Xbo::select("Entered Date", "enterdate");
			Xbo::select("Changed By", "changeby");
			Xbo::select("Changed Date", "changedate", "date");
			XboSet::getXboSet("sotracking", $getOwner);
			XboSet::displayData();
			exit();
		}
		if($tableid=="mxtable_matsaletrans_child"){
			Xbo::select("Transaction Type", "transtype");
			Xbo::select("Line Num.", "solinenum");
			Xbo::select("Item", "itemnum");
			Xbo::select("Condition Type", "conditiontype");
			Xbo::select("Issue Type", "costtype");
			Xbo::select("Cost Date", "costdate", "date");
			Xbo::select("Batch Num.", "batchnum");
			Xbo::select("Price Code", "pricecode");
			Xbo::select("Sold Qty", "quantity", "decimal");
			Xbo::select("Unit Price", "unitprice", "decimal");
			Xbo::select("Total Price", "lineprice", "decimal");
			Xbo::select("Total Profit", "lineprofit", "decimal");
			Xbo::select("Expired Date", "expdate", "date");
			
			Xbo::select("Transaction Date", "enterdate", "date");
			XboSet::getXboSet("matsaletrans", $getOwner);
			XboSet::displayData();
			exit();
		}
		if($tableid=="mxtable_soshipment_child"){
			Xbo::select("Line Num.", "shiplinenum");
			Xbo::select("Description", "description");
			Xbo::select("Courier Name", "couriername");
			Xbo::select("Tracking Num.", "trackingnum");
			Xbo::select("Shipping Date", "shippingdate", "date");
			Xbo::select("Shipping Cost", "shippingcost", "decimal");
			Xbo::select("Received By", "receivedby");
			Xbo::select("Phone", "phone");
			XboSet::getXboSet("soshipment", $getOwner);
			XboSet::displayData();
			exit();
		}

		if($tableid=="mxtable_servsaletrans_child"){
			Xbo::select("Transaction Type", "transtype");
			Xbo::select("SO", "sonum");
			Xbo::select("Line Num.", "solinenum");
			Xbo::select("Customer Code", "customercode");
			Xbo::select("Sold Qty", "quantity", "decimal");
			Xbo::select("Unit Price", "unitprice", "decimal");
			Xbo::select("Total  Price", "lineprice", "decimal");
			Xbo::select("Entered By", "enterby");
			Xbo::select("Transaction Date", "enterdate", "date");
			XboSet::getXboSet("servsaletrans", $getOwner);
			XboSet::displayData();
			exit();
		}
		if($tableid=="mxtable_soterm_child"){
			Xbo::select("Line Num.", "termlinenum");
			Xbo::select("Term Code", "termcode");
			Xbo::select("Description", "description");
			XboSet::getXboSet("soterm", $getOwner);
			XboSet::displayData();
			exit();
		}
		if($tableid=="mxtable_soterm2_child"){
			Xbo::select("Line Num.", "termlinenum");
			Xbo::select("Term Code", "termcode");
			Xbo::select("Description", "description");
			XboSet::getXboSet("soterm2", $getOwner);
			XboSet::displayData();
			exit();
		}
	}
}

soChildren::init();

?>