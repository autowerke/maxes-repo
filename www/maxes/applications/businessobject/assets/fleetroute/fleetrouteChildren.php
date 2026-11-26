<?php 
require_once ("../../eat.php");



class FleetRouteChildren {

	public static function init(){
		XboSet::validateChildren();	
		FleetRouteChildren::displayData();
		
	}
	public static function displayData(){
		$getOwner = XboSet::getOwner();
	
		$tableid = Xbo::getTableID();
		if($tableid=="mxtable_fleetroute_child"){
			Xbo::select("No.", "fleetroutelinenum");
			Xbo::select("Asset", "assetnum");
			Xbo::select("Remarks", "description");
			XboSet::getXboSet("fleetrouteline", $getOwner);
			XboSet::displayData();
		}
		
		if($tableid=="mxtable_fleetprice_child"){
		
			Xbo::select("Tipe/Kelas", "seattype");
			Xbo::select("Price Base", "price", "decimal");
			XboSet::getXboSet("fleetprice", $getOwner);
			XboSet::displayData();
		}
		if($tableid=="mxtable_fleetrouteprice_child"){
			Xbo::select("Price Num.", "fleetroutepricenum");
			Xbo::select("Asset", "assetnum");
			Xbo::select("Type", "seattype");
			Xbo::select("Base Price", "price", "decimal");
			XboSet::getXboSet("fleetrouteprice", $getOwner);
			XboSet::displayData();
		}
		if($tableid=="mxtable_fleetrouteprice2_child"){
			Xbo::select("Num.", "fleetrouteprice2num");
			Xbo::select("Asset", "assetnum");
			Xbo::select("Package Type", "packtype");
			Xbo::select("Base Price", "price", "decimal");
			XboSet::getXboSet("fleetrouteprice2", $getOwner);
			XboSet::displayData();
		}
		if($tableid == "mxtable_fleetroutelocations_child"){
			Xbo::select("Num.", "fleetroutelocnum");
			Xbo::select("Num.", "locnum");
			Xbo::select("Description", "description");
			XboSet::getXboSet("fleetroutelocations", $getOwner);
			XboSet::displayData();
		}
		
		
	}
}

FleetRouteChildren::init();

?>