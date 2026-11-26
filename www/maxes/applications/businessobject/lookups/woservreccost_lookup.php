<?php 
include ("../eat.php");

class WOServrecCost extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		if(Super::hasParam("wonum")){
			$wonum = Super::getParam("wonum");
			$where = "wonum='$wonum'";
		}else{
			$where =null;
		}
	
		Xbo::select("PO", "ponum");
		Xbo::select("Line Type.", "linetype");
		Xbo::select("Line Num.", "polinenum");
		Xbo::select("Description", "description");
		Xbo::select("Measure Unit", "orderunit");
		Xbo::select("Quantity", "quantity", "decimal");
		Xbo::select("Unit Cost", "unitcost", "decimal");
		Xbo::select("Line Cost", "linecost", "decimal");
		XboSet::getXboSet("woservreccost", $where);
		XboSet::displayData();
		
		/*
		Xbo::select("Service Item", "serviceitemnum");
		Xbo::select("Line Num.", "planlinenum");
		Xbo::select("Description", "description");
		Xbo::select("Quantity", "quantity", "decimal");
		Xbo::select("Measure Unit", "orderunit");
		Xbo::select("Unit Cost", "unitcost", "decimal");
		Xbo::select("Line Cost", "linecost", "decimal");
		XboSet::getXboSet("woplanservice", $where);
		XboSet::displayData();
		*/

	}
	public static function init(){
		WOServrecCost::displayData();
	}
	
}
WOServrecCost::init();
?>