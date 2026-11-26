<?php 
require_once ("../../eat.php");

class PreorderLine {

	public static function init(){
		XboSet::validateChildren();	
		PreorderLine::displayData();
	}
	public static function displayData(){
		$getOwner = XboSet::getOwner();
		$tableid = Xbo::getTableID();
		
		if($tableid=="mxtable_preorderline_child"){
			Xbo::select("No.", "preorderlinenum");
			Xbo::select("Description", "description");
			Xbo::select("Quantity", "quantity", "decimal");
			Xbo::select("Order Unit", "orderunit");
			Xbo::select("Unit Price", "unitcost", "decimal");
			Xbo::select("Jumlah Harga", "linecost", "decimal");
			Xbo::select("Tax", "taxcost", "decimal");
			XboSet::getXboSet("preorderline", $getOwner);
			XboSet::displayData();
		}
		
		if($tableid=="mxtable_proresource_child"){
			Xbo::select("No.", "proreslinenum");
			Xbo::select("For Preorder Line", "forpreorderlinenum");
			Xbo::select("Description", "description");
			Xbo::select("Target Start Date", "targstartdate", "date");
			Xbo::select("Target Finish Date", "targcompdate", "date");
			Xbo::select("Cost", "cost", "decimal");
			XboSet::getXboSet("proresource", $getOwner);
			XboSet::displayData();
		}
		if($tableid=="mxtable_proitem_child"){
			Xbo::select("No.", "proitemlinenum");
			Xbo::select("Item.", "itemnum");
			Xbo::select("Description", "description");
			Xbo::select("Required Qty", "requiredqty", "decimal");
			Xbo::select("Isssue Unit", "issueunit");
			Xbo::select("Unit Cost", "unitcost", "decimal");
			Xbo::select("Total Cost", "linecost", "decimal");
			Xbo::select("Cost Type", "costtype");
			Xbo::select("Warehouse", "warehouse");
			Xbo::select("Rack/Bin", "racknum");
			XboSet::getXboSet("proitem", $getOwner);
			XboSet::displayData();
		}
		if($tableid=="mxtable_prodocs_child"){
			Xbo::select("No.", "preorderdoclinenum");
			Xbo::select("Description", "description");
			Xbo::select("acknowledged By", "acknowledgedby");
			Xbo::select("Approved By", "approvedby");
			Xbo::select("Submited Date", "submitdate");
			Xbo::select("Approved Date", "approvaldate", "date");
			Xbo::select("Status", "status");
			Xbo::select("Status Date", "statusdate", "date");
			XboSet::getXboSet("preorderdocs", $getOwner);
			XboSet::displayData();
			
			
		}
	
	}
}

PreorderLine::init();

?>