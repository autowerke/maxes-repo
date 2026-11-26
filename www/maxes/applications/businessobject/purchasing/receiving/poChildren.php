<?php 
require_once ("../../eat.php");

class ReceivingChildren {

	public static function init(){
		XboSet::validateChildren();	
		ReceivingChildren::displayData();
	}
	
	
	public static function displayData(){
		
		$getOwner = XboSet::getOwner();
		
		$tableid = Xbo::getTableID();
		if($tableid=="mxtable_matrectrans_child"){
			Xbo::select("Line Num.", "polinenum");
			Xbo::select("Item", "itemnum");
			Xbo::select("Description", "description"); 
			Xbo::select("Received Qty", "quantity", "decimal");
			Xbo::select("Unit Cost", "unitcost", "decimal");
			Xbo::select("Line Cost", "linecost", "decimal");
			Xbo::select("Warehouse", "warehouse");
			Xbo::select("Rack/Bin", "racknum");
			Xbo::select("Transaction Type", "transtype");
			Xbo::select("Entered Date", "enterdate", "date");
			Xbo::select("Financial Period", "financialperiod");
			XboSet::getXboSet("matrectrans", $getOwner);
			XboSet::displayData();
		}
		if($tableid=="mxtable_servrectrans_child"){
			
			Xbo::select("Line Num.", "polinenum");
			Xbo::select("Description", "description");
			Xbo::select("Received Qty", "quantity");
			Xbo::select("Unit Cost", "unitcost", "decimal");
			Xbo::select("Line Cost", "linecost", "decimal");
			Xbo::select("Transaction Type", "transtype");
			Xbo::select("Entered Date", "enterdate", "date");
			Xbo::select("Financial Period", "financialperiod");
			XboSet::getXboSet("servrectrans", $getOwner);
			XboSet::displayData();
		}
	}
}

ReceivingChildren::init();

?>