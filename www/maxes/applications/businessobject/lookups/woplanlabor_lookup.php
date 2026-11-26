<?php 
include ("../eat.php");

class WOPlanLabor extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		
		$wonum = Super::getParam("wonum");
		$flag = Super::getParam("flag");
		
		$wr = [];
		if(!empty($wonum))  array_push($wr, " wonum = '$wonum'");
		if(!empty($flag))  array_push($wr, " laborlinenum not in (select laborlinenum from labortrans where wonum='$wonum')");
		
		if(count($wr)>0){
			$where = implode(" and ", $wr);
		}else  $where =null;
		Xbo::select("Line Num.", "laborlinenum");
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
		XboSet::getXboSet("woplanlabor", "woplanlabor.".$where);
		XboSet::displayData(["employees.personid = woplanlabor.personid", "woplanlabor.wonum = wotask.wonum and woplanlabor.tasklinenum = wotask.tasklinenum"]);
	}
	
	public static function init(){
		WOPlanLabor::displayData();
	}
	
}

WOPlanLabor::init();




?>