<?php 
require_once ("../../eat.php");

class TimelinerChildren {

	public static function init(){
		XboSet::validateChildren();	
		TimelinerChildren::displayData();
	}
	
	
	public static function displayData(){
		$getOwner = XboSet::getOwner();
		$tableid = Xbo::getTableID();
		if($tableid=="mxtable_timelinerline_child"){
			Xbo::select("WO", "wonum");
			Xbo::select("Description", "workorder.description");
			Xbo::select("Status", "workorder.status");
			Xbo::select("Target Dimulai", "workorder.targstartdate", "date");
			Xbo::select("Target Selesai", "workorder.targcompdate", "date");
			Xbo::select("Progress Pekerjaan(%)", "workorder.progress", "decimal");
			Xbo::select("Actual Cost", "workorder.actualcost", "decimal");
			Xbo::select("Biaya Roll Up", "workorder.rollupcost", "decimal");
			XboSet::getXboSet("timelinerline", $getOwner);
			XboSet::displayData(["timelinerline.wonum = workorder.wonum"]);
		}

		
		
	}
}

TimelinerChildren::init();

?>