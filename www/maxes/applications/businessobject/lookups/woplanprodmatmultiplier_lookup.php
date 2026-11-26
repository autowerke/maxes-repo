<?php 
include ("../eat.php");

class WOPlanProdMatMultiplier extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		
		$wonum = Super::getParam("wonum");
		if(!empty($wonum)) $where = "wonum ='".$wonum."'";	
		else  $where =null;
		
		Xbo::select("Line Num.", "planlinenum");
		Xbo::select("Item", "itemnum");
		Xbo::select("Description", "item.description");
		Xbo::select("Condition Type", "conditiontype");
		Xbo::select("Quantity", "quantity", "decimal");
		Xbo::select("Unit Cost", "unitcost", "decimal");
		Xbo::select("Production Order Qty", "_input_text_prodorderqty", "decimal");
		XboSet::getXboSet("woplanmaterial", $where);
		XboSet::displayData(["item.itemnum = woplanmaterial.itemnum"]);
	
	}
	
	public static function init(){
		WOPlanProdMatMultiplier::displayData();
	}
	
}

WOPlanProdMatMultiplier::init();




?>