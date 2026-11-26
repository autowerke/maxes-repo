<?php 
require_once ("../../eat.php");

class TeamdecreeChildren {

	public static function init(){
		XboSet::validateChildren();	
		TeamdecreeChildren::displayData();
	}
	
	public static function displayData(){
		$getOwner = XboSet::getOwner();
		$tableid = Xbo::getTableID();

		// Menangani child grid anggota SK Tim
		if($tableid == "mxtable_teamdecreemember_child"){
			Xbo::select("Person ID.", "personid");
			Xbo::select("Role / Position", "memberrole");
			Xbo::select("Division", "division");
			Xbo::select("Notes", "notes");
			Xbo::select("Description", "description");
			Xbo::select("Remarks", "remarks");
			XboSet::getXboSet("teamdecreemember", $getOwner);
			XboSet::displayData();
		}
	}
}

TeamdecreeChildren::init();
?>