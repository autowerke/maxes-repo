<?php 
require_once ("../../eat.php");

class RefundChildren {

	public static function init(){
		XboSet::validateChildren();	
		RefundChildren::displayData();
	}
	public static function displayData(){
		global $getOwnerCols;
		$getOwner = XboSet::getOwner();		
		$tableid = Xbo::getTableID();
		if($tableid=="mxtable_refundline_child"){
			Xbo::select("Line Num.", "refundlinenum");
			Xbo::select("Type", "linetype");
			Xbo::select("Item", "itemnum");
			Xbo::select("Description", "description");
			Xbo::select("Condition Type", "conditiontype");
			Xbo::select("warehouse", "warehouse");
			Xbo::select("Quantity", "quantity", "decimal");
			Xbo::select("Unit Price", "unitcost", "decimal");
			Xbo::select("Total", "linecost", "decimal");
			XboSet::getXboSet("refundline", $getOwner);
			XboSet::displayData();
		}
		if($tableid=="mxtable_refundline2_child"){
			Xbo::select("Line Num.", "refundlinenum");
			Xbo::select("Tipe Baris", "linetype");
			Xbo::select("Item", "itemnum");
			Xbo::select("Description", "description");
			Xbo::select("Condition Code", "conditiontype");
			Xbo::select("Quantity", "sellqty", "decimal");
			Xbo::select("Unit Price", "unitprice", "decimal");
			Xbo::select("Total  Price", "lineprice", "decimal");
			Xbo::select("Variance Price", "varlineprice", "decimal");
			Xbo::select("Status Deliveri", "sentstatus");
			XboSet::getXboSet("refundline2", $getOwner);
			XboSet::displayData();
		}
		
		
		
	}
}

RefundChildren::init();

?>