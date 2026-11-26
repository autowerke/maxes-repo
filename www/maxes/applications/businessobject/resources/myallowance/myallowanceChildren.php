<?php 
require_once ("../../eat.php");

class MyAllowanceChildren {

	public static function init(){
		XboSet::validateChildren();	
		MyAllowanceChildren::displayData();
	}
	public static function displayData(){
		global $getOwnerCols;
		$getOwner = XboSet::getOwner();		
		$tableid = Xbo::getTableID();
		
		
		if($tableid=="mxtable_myallowance_child"){
			Xbo::select("Line Num.", "myallowancelinenum");
			Xbo::select("Description", "description");
			Xbo::select("Unit Cost", "unitcost", "decimal");
			Xbo::select("Quantity", "quantity", "decimal");
			Xbo::select("Order Unit", "measureunit");
			Xbo::select("Line Cost", "linecost", "decimal");
			Xbo::select("Remarks", "Remarks");
			XboSet::getXboSet("myallowanceline", $getOwner);
			XboSet::displayData();
		}
		
	}
}

MyAllowanceChildren::init();

?>