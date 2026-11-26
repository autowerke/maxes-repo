<?php 
require_once ("../../eat.php");

class MRChildren {

	public static function init(){
		XboSet::validateChildren();	
		MRChildren::displayData();
	}
	
	
	public static function displayData(){
		$getOwner = XboSet::getOwner();
		$tableid = Xbo::getTableID();
		if($tableid=="mxtable_relatedwoticket_child"){
			Xbo::select("WO", "relatedwonum");
			Xbo::select("Description", "workorder.description");
			Xbo::select("Status", "workorder.status");
			Xbo::select("Targ. Start Date", "workorder.targstartdate", "date");
			Xbo::select("Targ. Completion Date", "workorder.targcompdate", "date");
			Xbo::select("Planning Cost", "workorder.plannedcost", "decimal");
			Xbo::select("Actual Line Cost", "workorder.actualcost", "decimal");
			XboSet::getXboSet("relatedwoticket", $getOwner);
			XboSet::displayData("relatedwoticket.relatedwonum = workorder.wonum");
		}
		
	}
}

MRChildren::init();

?>