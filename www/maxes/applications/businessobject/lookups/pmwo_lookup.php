<?php 
include ("../eat.php");

class PMWOLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$orgcode = Super::getParam("orgcode");
		$pmnum   = Super::getParam("pmnum");
		$wonum   = Super::getParam("wonum");
		$targstartdate   = Super::getParam("targstartdate");
		$targcompdate   = Super::getParam("targcompdate");
		$whereClauses = [];
		$whereClauses[] = "workorder.status NOT IN ('cancel', 'close', 'closed', 'complete')";

		// Tambahin kondisi kalau ada input
		if (!empty($orgcode)) {
			$whereClauses[] = "orgcode = '" . addslashes($orgcode) . "'";
		}
		if (!empty($pmnum)) {
			$whereClauses[] = "pmnum = '" . addslashes($pmnum) . "'";
		}
		if (!empty($wonum)) {
			$whereClauses[] = "wonum = '" . addslashes($wonum) . "'";
		}
		if (!empty($targstartdate)) {
			$whereClauses[] = "targstartdate = '" . addslashes($targstartdate) . "'";
		}
		if (!empty($targcompdate)) {
			$whereClauses[] = "targcompdate = '" . addslashes($targcompdate) . "'";
		}

		$where = implode(" AND ", $whereClauses);
		
		
		Xbo::select("WO", "wonum");
		Xbo::select("Asset", "assetnum");
		Xbo::select("Location", "location");
		Xbo::select("PM", "pmnum");
		Xbo::select("PM Desc.", "pm.description");
		Xbo::select("Description", "description");
		Xbo::select("Target Start Date", "targstartdate", "date");
		Xbo::select("Target Finish Date", "targcompdate", "date");
		Xbo::select("Status", "status");
		XboSet::getXboSet("workorder", $where);
		XboSet::displayData(["workorder.pmnum = pm.pmnum"]);
	}
	public static function init(){
		PMWOLookup::displayData();
	}
	
}
PMWOLookup::init();
?>