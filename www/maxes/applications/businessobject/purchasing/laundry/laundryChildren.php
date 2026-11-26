<?php 
require_once ("../../eat.php");

class LaundryChildren {
	public static function init(){
		XboSet::validateChildren();	
		LaundryChildren::displayData();
	}
	public static function displayData(){
		$getOwner = XboSet::getOwner();
		$tableid = Xbo::getTableID();
		if($tableid=="mxtable_laundryline_child"){
			Xbo::select("No", "laundrylinenum");
			Xbo::select("Name", "laundryname");
			Xbo::select("Quantity", "quantity", "decimal");
			Xbo::select("Unit Price", "unitprice", "decimal");
			Xbo::select("Total  Price", "lineprice", "decimal");
			XboSet::getXboSet("laundryline", $getOwner);
			XboSet::displayData();
		}
	
	
	}
}

LaundryChildren::init();

?>