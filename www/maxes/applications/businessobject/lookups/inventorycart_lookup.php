<?php 
include ("../eat.php");

class InventoryCart extends Xbo {

	public function __destruct()
    {
        return null;
    }
	
	
	public static function displayData(){
		
				
		Xbo::select("Item", "itemnum");
		Xbo::select("Condition Type", "conditiontype");
		Xbo::select("Quantity to Order", "quantity");
		Xbo::select("Unit Cost", "unitcost");
		Xbo::select("Line Cost", "linecost");
		Xbo::select("Order Unit", "orderunit");
		Xbo::select("Warehouse", "warehouse");
		Xbo::select("Rack", "racknum");
		Xbo::select("GL Debit", "gldebitacct");
		Xbo::select("GL Credit", "glcreditacct");
		
		XboSet::getXboSet("invcart");
		XboSet::displayData();
	
	}
	
	public static function init(){
		InventoryCart::displayData();
	}
	
}

InventoryCart::init();




?>