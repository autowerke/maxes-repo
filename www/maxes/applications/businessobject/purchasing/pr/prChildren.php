<?php 
require_once ("../../eat.php");


class addVendorCode extends prChildren{
	public static function updateVendorCode(){
		global $ownerCols;
		global $childTable;
		
	}
}

class prChildren {

	public static function init(){
		XboSet::validateChildren();	
		prChildren::displayData();
		addVendorCode::updateVendorCode();
	}
	
	
	public static function displayData(){
		$getOwner = XboSet::getOwner();
		$tableid = Xbo::getTableID();
		
		if($tableid=="mxtable_prlines_child"){
			Xbo::select("Line Num.", "prlinenum");
			Xbo::select("Item", "itemnum");
			Xbo::select("Description", "description");
			Xbo::select("Line Type", "linetype");
			Xbo::select("Quantity", "orderqty", "decimal");
			Xbo::select("Order Unit", "orderunit");
			Xbo::select("Unit Cost", "unitcost", "decimal");
			Xbo::select("Line Cost", "linecost", "decimal");
			Xbo::select("Work Order", "wonum");
			Xbo::select("Location", "warehouse");
			XboSet::getXboSet("prline", $getOwner);
			XboSet::displayData();
		}
	
	
	}
}

prChildren::init();

?>