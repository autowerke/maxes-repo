<?php 
require_once ("../../eat.php");



class PmCampaignChildren {

	public static function init(){
		XboSet::validateChildren();	
		PmCampaignChildren::displayData();
		
	}
	public static function displayData(){
		$getOwner = XboSet::getOwner();
		$tableid = Xbo::getTableID();
		if($tableid=="mxtable_pmcampaignline_child"){
			Xbo::select("Line", "pmcampaignlinenum");
			Xbo::select("WO", "wonum");
			Xbo::select("PM", "pmnum");
			Xbo::select("Targ. Start Date", "targstartdate", "date");
			Xbo::select("Targ. Comp. Date", "targcompdate", "date");
			Xbo::select("Duration", "duration");
			Xbo::select("Change Date", "changedate", "date");
			Xbo::select("Is Locked?", "islocked");
			XboSet::getXboSet("pmcampaignline", $getOwner);
			XboSet::displayData();
		}
		
		
	}
}

PmCampaignChildren::init();

?>