<?php 
require_once ("../../eat.php");

class TeamsChildren {

	public static function init(){
		XboSet::validateChildren();	
		TeamsChildren::displayData();
	}
	public static function displayData(){
		global $getOwnerCols;
		$getOwner = XboSet::getOwner();		
		$tableid = Xbo::getTableID();
		
		if($tableid=="mxtable_empteam_child"){
			Xbo::select("Staff", "personid");
			Xbo::select("Full Name", "employees.fullname");
			Xbo::select("Department", "employees.department");
			Xbo::select("Section", "employees.section");
			Xbo::select("Leader?", "leader");
			Xbo::select("priority", "priority");
			Xbo::select("Skill Level", "skilllevel");
			Xbo::select("Unit Cost", "unitcost", "decimal");
			XboSet::getXboSet("empteam", $getOwner);
			XboSet::displayData(["empteam.personid = employees.personid"]);
		}
		
	}
}

TeamsChildren::init();

?>