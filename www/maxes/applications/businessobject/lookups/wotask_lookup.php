<?php 
include ("../eat.php");

class WOTaskLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		
		$wonum = Super::getParam("wonum");
		if(!empty($wonum)) $where = "wonum ='".$wonum."'";	
		else  $where =null;
		
		
		Xbo::select("Task Num.", "tasklinenum");
		Xbo::select("Description", "description");
		Xbo::select("Target Start Date", "targstartdate", "date");
		Xbo::select("Target Finish Date", "targcompdate", "date");
		Xbo::select("Status", "status");
		
		XboSet::getXboSet("wotask", $where);
		XboSet::displayData();
	
	}
	
	public static function init(){
		WOTaskLookup::displayData();
	}
	
}

WOTaskLookup::init();




?>