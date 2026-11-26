<?php 
require_once ("../../eat.php");



class AssetsChildren {

	public static function init(){
		XboSet::validateChildren();	
		AssetsChildren::displayData();
		return;
	}
	public static function displayData(){
		$getOwner = XboSet::getOwner();
		$linktable = Super::getParam("linktable");
		$tableid = Xbo::getTableID();
		if($tableid=="mxtable_assetclassification_child"){
			Xbo::select("Attribute Name", "attributename");
			Xbo::select("Description", "attribute.description");
			Xbo::select("Value", "value");
			Xbo::select("Unit", "unit");
			Xbo::select("Remarks", "description");
			XboSet::getXboSet("assetclassification", $getOwner);
			XboSet::displayData(["assetclassification.attributename = attribute.attributename"]);
			return;
		}
		if($tableid=="mxtable_assetdepreciation_child"){
			
			Xbo::select("Asset Value", "assetvalue", "decimal");
			Xbo::select("Monthly Cost", "monthlycost", "decimal");
			Xbo::select("Depreciation Cost", "deprecost", "decimal");
			Xbo::select("Total Depreciation Cost", "totaldeprecost", "decimal");
			Xbo::select("Since Date", "fromdate", "date");
			Xbo::select("To Date", "todate", "date");
			XboSet::getXboSet("assetdepreciation", $getOwner);
			XboSet::displayData();
			return;
		}
		
		if($tableid=="mxtable_workorder_child"){
			Xbo::select("Work Order", "wonum");
			Xbo::select("Description", "description");
			Xbo::select("Work Type", "worktype");
			Xbo::select("Work Classification", "workclass");
			Xbo::select("Actual Start Date", "actstartdate", "date");
			Xbo::select("Actual Finish Date", "actfinishdate", "date");
			Xbo::select("Actual Cost", "actualcost", "decimal");
			XboSet::getXboSet("workorder", $getOwner. " and status not in('cancel', 'waitappr')");
			XboSet::displayData();
			return;
		}
		
		if($tableid=="mxtable_assettrans_child"){
			Xbo::select("Dari Lokasi", "fromlocation");
			Xbo::select("Ke Lokasi", "tolocation");
			Xbo::select("Entered Date", "enterdate", "date");
			Xbo::select("Entered By", "enterby");
			XboSet::getXboSet("assettrans", $getOwner);
			XboSet::displayData();
			return;
		}
		if($tableid=="mxtable_assetsubs_child"){
			Xbo::select("Asset", "subassetnum");
			Xbo::select("Description", "assets.description");
			Xbo::select("System", "system");
			XboSet::getXboSet("assetsubs", $getOwner);
			XboSet::displayData(["assets.assetnum = assetsubs.subassetnum"]);
			return;
		}
		if($tableid=="mxtable_assetsparepart_child"){			
			Xbo::select("Item", "itemnum");
			Xbo::select("Description", "item.description");
			Xbo::select("Quantity", "quantity", "decimal");
			Xbo::select("Unit Cost", "unitcost", "decimal");
			Xbo::select("Line Cost", "linecost", "decimal");
			Xbo::select("Last WO", "wonum");
			XboSet::getXboSet("assetsparepart", $getOwner);
			XboSet::displayData(["assetsparepart.itemnum = item.itemnum"]);
			return;
		}
		
		if($tableid=="mxtable_assetseatconfig_child"){
			Xbo::select("Sort", "sort");
			Xbo::select("Map", "map");
			XboSet::getXboSet("assetseatconfig", $getOwner);
			XboSet::displayData();
			return;
		}
		
		if($tableid=="mxtable_assetmeter_child"){
			Xbo::select("Meter", "meternum");
			Xbo::select("Description", "meter.description");
			Xbo::select("Type", "meter.metertype");
			Xbo::select("Last Reading", "lastreading", "decimal");
			Xbo::select("Avg. Daily Use", "avgdailyusage", "decimal");
			Xbo::select("Unit", "unit");
			Xbo::select("Last Reading Date", "lastreadingdate", "date");
			Xbo::select("Predicted Due Date", "predictedduedate", "date");
			Xbo::select("Days until due", "daysuntildue");
			Xbo::select("Units To Go", "unitstogo", "decimal");
			Xbo::select("Meter Health Score", "meterhealthscore", "decimal");
			Xbo::select("Meter Status", "meterstatus");
			XboSet::getXboSet("assetmeter", $getOwner);
			XboSet::displayData(["assetmeter.meternum = meter.meternum"]);
			return;
		}
		
		if($tableid=="mxtable_assetmeterconfig_child"){
			Xbo::select("Meter", "meternum");
			Xbo::select("Description", "meter.description");
			Xbo::select("Measure Unit", "meter.measureunit");
			Xbo::select("Normal Low", "normal_low", "decimal");
			Xbo::select("Normal High", "normal_high", "decimal");
			Xbo::select("Warning Low", "warning_low", "decimal");
			Xbo::select("Warning High", "warning_high", "decimal");
			Xbo::select("Limit Low", "limit_low", "decimal");
			Xbo::select("Limit High", "limit_high", "decimal");
			Xbo::select("Health Impact Weight", "health_impact_weight", "decimal");
			XboSet::getXboSet("assetmeterconfig", $getOwner);
			XboSet::displayData(["assetmeterconfig.meternum = meter.meternum"]);
			return;
		}
		if($tableid=="mxtable_assetproductivity_child"){
			Xbo::select("Parameter", "assetparamnum");
			Xbo::select("Description", "assetparam.description");
			Xbo::select("Potential Value", "value", "decimal");
			Xbo::select("Measure Unit", "assetparam.measureunit");
			Xbo::select("Actual Value", "actualvalue", "decimal");
			Xbo::select("Unit Cost", "unitcost", "decimal");
			Xbo::select("Enter By", "enterby");
			Xbo::select("Enter Date", "enterdate", "date");
			Xbo::select("Change By", "changeby");
			Xbo::select("Change Date", "changedate", "date");
			XboSet::getXboSet("assetproductivity", $getOwner);
			XboSet::displayData(["assetproductivity.assetparamnum = assetparam.assetparamnum"]);
			return;
		}
		if($tableid=="mxtable_assetmod_child"){
			Xbo::select("Modification Date", "modificationdate", "date");
			Xbo::select("Subject", "subject");
			Xbo::select("Description", "description");
			Xbo::select("Objective", "objective");
			Xbo::select("Status", "status");
			Xbo::select("Total Cost", "totalcost", "decimal");
			Xbo::select("Is Approved?", "isapproved");
			Xbo::select("Approved By", "approvedby");
			Xbo::select("Approved Date", "approveddate", "date");
			XboSet::getXboSet("assetmod", $getOwner);
			XboSet::displayData();
			return;
		}
		
		// When User display record
		if(!empty($linktable)){	
			$where = Xbo::getLinkTableWhere($linktable); 
			$tableName = Xbo::getLinkTableName($linktable);
			if($tableName=="mxtable_assetmodline_child") {
				
				AssetsChildren :: AssetModificationLine($where);
				return;
			}
			if($tableName=="mxtable_assetmodmeter_child") {
				AssetsChildren :: AssetModificationMeter($where);
				return;
			}
			if($tableName=="mxtable_assetmodproductivity_child") {
				AssetsChildren :: AssetModificationProductivity($where);
				return;
			}
			
			if($tableName=="mxtable_assetmodmaintenance_child") {
				AssetsChildren :: AssetModificationMaintenance($where);
				return;
			}
			
			if($tableName == "mxtable_assetmoddowntime_child"){
				AssetsChildren :: AssetModDowntime($where);
			}
		}{
			//When User click New Record
			if($tableid=="mxtable_assetmodline_child") {
				AssetsChildren :: AssetModificationLine($getOwner);
				return;
			}
			if($tableid=="mxtable_assetmodmeter_child") {
				AssetsChildren :: AssetModificationMeter($getOwner);
				return;
			}
			if($tableid=="mxtable_assetmodproductivity_child") {
				AssetsChildren :: AssetModificationProductivity($getOwner);
				return;
			}
			if($tableid=="mxtable_assetmodmaintenance_child") {
				AssetsChildren :: AssetModificationMaintenance($getOwner);
				return;
			}
			if($tableid=="mxtable_assetmoddowntime_child") {
				AssetsChildren :: AssetModDowntime($getOwner);
				return;
			}
		}
	}
	
	private static function AssetModDowntime($where){
		Xbo::select("Failure Code", "failurecode");
		Xbo::select("Hist. Duration Minutes", "histdurationmin", "decimal");
		Xbo::select("Est. Reduction(%)", "estreduction", "decimal");
		Xbo::select("Prediction Duration Minutes", "predictdurationmin", "decimal");
		Xbo::select("Remarks", "remarks");
		Xbo::select("Year", "year");
		Xbo::select("Est. Year", "estyear");
		XboSet::getXboSet("assetmoddowntime", $where);
		XboSet::displayData();
	}
	private static function AssetModificationProductivity($where){
		Xbo::select("Current Year", "year");
		Xbo::select("Productivity", "assetparamnum");
		Xbo::select("Potential Value", "value", "decimal");
		Xbo::select("Actual Value", "actualvalue", "decimal");
		Xbo::select("Change Type", "changetype");
		Xbo::select("Change Value", "changevalue", "decimal");
		Xbo::select("Est. Value", "estimatedvalue", "decimal");
		Xbo::select("Measure Unit", "measureunit");
		Xbo::select("Year", "year");
		XboSet::getXboSet("assetmodproductivity", $where);
		XboSet::displayData();
	}
	private static function AssetModificationMaintenance($where){
		Xbo::select("Current Year", "year");
		Xbo::select("Current YTD Cost", "currentytdcost", "decimal");
		Xbo::select("Change (%)", "ytdcostchange", "decimal");
		Xbo::select("Predicted YTD Cost", "predictedytdcost", "decimal");
		Xbo::select("Remarks", "remarks");
		Xbo::select("Est. Year", "estyear");			
		XboSet::getXboSet("assetmodmaintenance", $where);
		XboSet::displayData();
	}
	private static function AssetModificationMeter($where){
		Xbo::select("Meter", "meternum");
		Xbo::select("Measure Unit", "measureunit");
		Xbo::select("Change Type", "changetype");
		Xbo::select("Change Value", "changevalue", "decimal");
		Xbo::select("Remarks", "remarks");
		XboSet::getXboSet("assetmodmeter", $where);
		XboSet::displayData();
	}
	private static function AssetModificationLine($where){
		Xbo::select("Line", "assetmodlinenum");
		Xbo::select("Type", "linetype");
		Xbo::select("Item", "item");
		Xbo::select("Description", "description");
		Xbo::select("Quantity", "quantity", "decimal");
		Xbo::select("Measure Unit", "measureunit");
		Xbo::select("Unit Cost", "unitcost", "decimal");
		Xbo::select("Line Cost", "linecost", "decimal");
		XboSet::getXboSet("assetmodline", $where);
		XboSet::displayData();
	}
	
}

AssetsChildren::init();

?>