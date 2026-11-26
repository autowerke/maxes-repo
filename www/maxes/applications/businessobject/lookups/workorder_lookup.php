<?php 
include ("../eat.php");

class WorkorderLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$orgcode = Super::getParam("orgcode");
		if(!empty($orgcode)) $where  = "workorder.orgcode ='".$orgcode."' and workorder.status not in('waitappr', 'cancel', 'closed')";	
		else $where = " status not in('cancel', 'closed')";
		
		$wonum = Super::getParam("wonum");
		if(!empty($wonum)) $where  = "wonum = '$wonum'";
		
		Xbo::select("WO", "wonum");
		Xbo::select("Asset", "assetnum");
		Xbo::select("Location", "location");
		Xbo::select("Customer Code", "customercode");
		Xbo::select("WO Owner", "employees.fullname");
		Xbo::select("Description", "description");
		Xbo::select("Status", "status");
		Xbo::select("Target Start Date", "targstartdate", "date");
		Xbo::select("Target Finish Date", "targcompdate", "date");
		Xbo::select("Planned Cost", "plannedcost", "decimal");
		Xbo::select("Actual Cost", "actualcost", "decimal");
		XboSet::getXboSet("workorder", $where);
		XboSet::displayData(["workorder.owner = employees.personid"]);
	}
	public static function init(){
		WorkorderLookup::displayData();
	}
	
}
WorkorderLookup::init();
?>