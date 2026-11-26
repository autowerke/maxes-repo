<?php 
require_once ("../../eat.php");


class LocationChildren {

	public static function init(){
		XboSet::validateChildren();	
		LocationChildren::displayData();
	}
	public static function displayData(){
		$getOwner = XboSet::getOwner();
		$tableid = Xbo::getTableID();
	
		if($tableid=="mxtable_assets_child"){
			Xbo::select("Asset", "assetnum");
			Xbo::select("Description", "description");
			Xbo::select("Status", "status");
			Xbo::select("Purchase Price", "purchaseprice", "decimal");
			XboSet::getXboSet("assets", $getOwner);
			XboSet::displayData();
			return;
		}
		if($tableid=="mxtable_locantenna_child"){
			Xbo::select("Antenna", "antenna");
			Xbo::select("Position", "position");
			Xbo::select("Specification", "specification");
			XboSet::getXboSet("locantenna", $getOwner);
			XboSet::displayData();
			return;
		}
		
		if($tableid=="mxtable_matusetrans_child"){
			$getOwner = str_replace("locnum", "location", $getOwner);
			Xbo::select("Item", "itemnum");
			Xbo::select("Description", "item.description");
			Xbo::select("From Warehouse", "warehouse");
			Xbo::select("From Rack", "racknum");
			Xbo::select("Quantity", "quantity", "decimal");
			Xbo::select("Unit Cost", "unitcost","decimal");
			Xbo::select("Line Cost", "linecost","decimal");
			Xbo::select("Transaction Type", "transtype");
			Xbo::select("Issued By", "enterby");
			Xbo::select("Transaction Date", "enterdate", "date");
			XboSet::getXboSet("matusetrans", $getOwner);
			XboSet::displayData("matusetrans.itemnum = item.itemnum");
			return;
		}
		if($tableid=="mxtable_locationmeter_child"){
			Xbo::select("Meter", "meternum");
			Xbo::select("Description", "meter.description");
			Xbo::select("Type", "meter.metertype");
			Xbo::select("Last Reading", "lastreading", "decimal");
			Xbo::select("Unit", "unit");
			Xbo::select("Last Reading Date", "lastreadingdate", "date");
			Xbo::select("Meter Health Score", "meterhealthscore", "decimal");
			Xbo::select("Meter Status", "meterstatus");
			XboSet::getXboSet("locationmeter", $getOwner);
			XboSet::displayData(["locationmeter.meternum = meter.meternum"]);
			return;
		}
		
	}
}

LocationChildren::init();

?>