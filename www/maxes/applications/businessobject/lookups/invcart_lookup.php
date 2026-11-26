<?php 
include ("../eat.php");

class InventoryCart extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
					
		Xbo::select("Subject", "itemnum");
		Xbo::select("Description", "item.description");
        Xbo::select("Remarks", "remarks");
        Xbo::select("Replenish From", "replenishfrom");
		Xbo::select("Quantity", "quantity", "decimal");
		Xbo::select("Unit Cost", "unitcost", "decimal");
        Xbo::select("Line Cost", "linecost", "decimal");
        
		XboSet::getXboSet("invcart");
		XboSet::displayData(["item.itemnum = invcart.itemnum"]);
	}
	public static function init(){
		InventoryCart::displayData();
	}
	
}
InventoryCart::init();
?>