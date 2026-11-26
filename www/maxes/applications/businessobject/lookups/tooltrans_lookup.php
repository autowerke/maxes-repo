<?php 
include ("../eat.php");

class ToolTrans extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		
		$wonum = Super::getParam("wonum");
		if(!empty($wonum)) $where = "wonum ='".$wonum."'";	
		else  $where =null;
		Xbo::select("Line Num.", "planlinenum");
		Xbo::select("For Task?", "tasklinenum");
		Xbo::select("Tool Num.", "toolnum");
		Xbo::select("Description", "description");
		Xbo::select("Quantity", "quantity", "decimal");
		Xbo::select("Order Unit", "orderunit");
		Xbo::select("Unit Cost", "unitcost", "decimal");
		Xbo::select("Line Cost", "linecost", "decimal");
		XboSet::getXboSet("tooltrans", $where);
		XboSet::displayData();
	
	}
	
	public static function init(){
		ToolTrans::displayData();
	}
	
}

ToolTrans::init();




?>