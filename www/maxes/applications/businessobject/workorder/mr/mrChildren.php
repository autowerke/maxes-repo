<?php 
require_once ("../../eat.php");

class MRChildren {

	public static function init(){
		XboSet::validateChildren();	
		MRChildren::displayData();
	}
	
	
	public static function displayData(){
		$getOwner = XboSet::getOwner();
		$tableid = Xbo::getTableID();
		if($tableid=="mxtable_mrline_child"){
			Xbo::select("Line Num.", "mrlinenum");
			Xbo::select("Item", "itemnum");
			Xbo::select("Description", "item.description");
			Xbo::select("Quantity", "quantity", "decimal");
			Xbo::select("Issue Unit", "item.issueunit");
			Xbo::select("Unit Cost", "unitcost", "decimal");
			Xbo::select("Line Cost", "linecost", "decimal");
			XboSet::getXboSet("mrline", $getOwner);
			XboSet::displayData("item.itemnum = mrline.itemnum");
		}
		
		if($tableid=="mxtable_matusetrans_child"){
			Xbo::select("Asset", "assetnum");
			Xbo::select("Location", "location");
			Xbo::select("Item", "itemnum");
			Xbo::select("Description", "item.description");
			Xbo::select("From Warehouse", "warehouse");
			Xbo::select("From Rack", "racknum");
			Xbo::select("Quantity", "quantity", "decimal");
			Xbo::select("Issue Unit", "item.issueunit");
			Xbo::select("Unit Cost", "unitcost", "decimal");
			Xbo::select("Line Cost", "linecost", "decimal");
			XboSet::getXboSet("matusetrans", $getOwner);
			XboSet::displayData("item.itemnum = matusetrans.itemnum");
		}
	}
}

MRChildren::init();

?>