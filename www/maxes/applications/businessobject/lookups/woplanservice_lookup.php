<?php 
include ("../eat.php");

class WOPlanService extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		
		$wonum = Super::getParam("wonum");
		if(!empty($wonum)) $where = "wonum ='".$wonum."'";	
		else  $where =null;
		
		Xbo::select("Line Num.", "planlinenum");
		Xbo::select("Description", "description");
		Xbo::select("PR", "prnum");
		Xbo::select("Line Num. PR", "prlinenum");
		Xbo::select("Unit Cost", "prline.unitcost", "decimal");
		Xbo::select("Line Cost", "prline.linecost", "decimal");
		XboSet::getXboSet("woplanservice", "woplanservice.".$where);
		XboSet::displayData(["prline.prlinenum = woplanservice.prlinenum and prline.prnum = woplanservice.prnum"]);
	
	}
	
	public static function init(){
		WOPlanService::displayData();
	}
	
}

WOPlanService::init();




?>