<?php 
include ("../eat.php");

class LaborTrans extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		
		$wonum = Super::getParam("wonum");
		if(!empty($wonum)) $where = "wonum ='".$wonum."'";	
		else  $where =null;
		
		Xbo::select("Staff", "personid");
		Xbo::select("Name", "employees.fullname");
		Xbo::select("For Task?", "tasklinenum");
		Xbo::select("Task Description", "wotask.description");
		Xbo::select("Start Date", "startdate", "date");
		Xbo::select("Finish Date", "enddate", "date");
		Xbo::select("Working Hours", "workhours", "decimal");
		Xbo::select("Regular Hours", "reghours", "decimal");
		Xbo::select("Unit Cost", "unitcost", "decimal");
		Xbo::select("Line Cost", "linecost", "decimal");
		Xbo::select("Permitted?", "permitted");
		XboSet::getXboSet("labortrans", "labortrans.".$where);
		XboSet::displayData(["employees.personid = labortrans.personid", "labortrans.wonum = wotask.wonum and labortrans.tasklinenum = wotask.tasklinenum"]);
	
	}
	
	public static function init(){
		LaborTrans::displayData();
	}
	
}

LaborTrans::init();




?>