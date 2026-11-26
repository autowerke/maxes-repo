<?php 
require_once ("../../eat.php");



class AssetsChildren {

	public static function init(){
		XboSet::validateChildren();	
		AssetsChildren::displayData();
		
	}
	public static function displayData(){
		$getOwner = XboSet::getOwner();
		$tableid = Xbo::getTableID();
		if($tableid=="mxtable_servrectrans_child"){
			Xbo::select("PO", "ponum");
			Xbo::select("Contract", "contractnum");
			Xbo::select("Description", "description");
			Xbo::select("Quantity", "quantity");
			Xbo::select("Order Unit", "orderunit");
			Xbo::select("Unit Cost", "unitcost", "decimal");
			Xbo::select("Line Cost", "linecost", "decimal");
			Xbo::select("Vendor", "po.vendor");
			Xbo::select("Status", "po.status");
			Xbo::select("Received Date", "actualdate", "date");
			XboSet::getXboSet("servrectrans", $getOwner);
			XboSet::displayData(["servrectrans.ponum = po.ponum"]);
		}
	}
}

AssetsChildren::init();

?>