<?php 
require_once ("../../eat.php");

class PettyCashChildren {

	public static function init(){
		XboSet::validateChildren();	
		PettyCashChildren::displayData();
	}
	public static function displayData(){
		global $getOwnerCols;
		$getOwner = XboSet::getOwner();		
		$tableid = Xbo::getTableID();
		
		if($tableid=="mxtable_pettycashline_child"){
			Xbo::select("Invoice", "invoicenum");
			Xbo::select("Tipe Baris", "linetype");
			Xbo::select("Line Num.", "Invoicelinenum");
			Xbo::select("Description", "description");
			Xbo::select("Quantity", "quantity", "decimal");			
			Xbo::select("Order Unit", "orderunit");	
			Xbo::select("Unit Cost", "unitcost", "decimal");
			Xbo::select("Line Cost", "linecost", "decimal");			
			Xbo::select("Financial Period", "financialperiod");
			Xbo::select("Tgl. Dibayar", "statusdate", "date");
			XboSet::getXboSet("pettycashline", $getOwner);
			XboSet::displayData();
		}
		
		if($tableid=="mxtable_pettycashline2_child"){
			Xbo::select("Line", "pettycashline2num");
			Xbo::select("Description", "description");
			Xbo::select("Quantity", "quantity", "decimal");			
			Xbo::select("Order Unit", "orderunit");	
			Xbo::select("Unit Cost", "unitcost", "decimal");
			Xbo::select("Line Cost", "linecost", "decimal");		
			XboSet::getXboSet("pettycashline2", $getOwner);
			XboSet::displayData();
		}
		
		
	}
}

PettyCashChildren::init();

?>