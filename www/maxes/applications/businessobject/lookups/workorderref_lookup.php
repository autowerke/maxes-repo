<?php 
include ("../eat.php");

class WorkorderLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		$orgcode = Super::getParam("orgcode");
		if(!empty($orgcode)) $where  = "orgcode ='".$orgcode."'";	
		
		$sonum = Super::getParam("sonum");
		if(!empty($sonum)) $where  = "sonum ='".$sonum."'";
		else $where = null;
		
		
						
		Xbo::select("WO", "relatedwonum");
		Xbo::select("Asset", "workorder.assetnum");
		Xbo::select("Location", "workorder.location");
		Xbo::select("Description", "workorder.description");
		Xbo::select("Status", "workorder.status");
		Xbo::select("Target Start Date", "workorder.targstartdate", "date");
		Xbo::select("Target Finish Date", "workorder.targcompdate", "date");
		Xbo::select("Planned Cost", "workorder.plannedcost", "decimal");
		Xbo::select("Actual Cost", "workorder.actualcost", "decimal");
		XboSet::getXboSet("relatedworkorder", $where);
		XboSet::displayData("relatedworkorder.relatedwonum = workorder.wonum");
	}
	public static function init(){
		WorkorderLookup::displayData();
	}
	
}
WorkorderLookup::init();
?>