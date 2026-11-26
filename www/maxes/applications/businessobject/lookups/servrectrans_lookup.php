<?php 
include ("../eat.php");

class ServrecLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		
		$wonum = Super::getParam("wonum");
		if(!empty($wonum)) $where = "wonum ='".$wonum."'";	
		else  $where =null;
		
		
		Xbo::select("PO", "ponum");
		Xbo::select("Line Num.", "polinenum");
		Xbo::select("Description", "description");
		Xbo::select("Quantity", "quantity", "decimal");
		Xbo::select("Unit Cost", "unitcost", "decimal");
		Xbo::select("Line Cost", "linecost", "decimal");
		Xbo::select("WO", "wonum");
		Xbo::select("Invoice", "invoicenum");
		XboSet::getXboSet("servrectrans", $where);
		XboSet::displayData();
	
	}
	
	public static function init(){
		ServrecLookup::displayData();
	}
	
}

ServrecLookup::init();




?>