<?php 
include ("../../eat.php");

class PMCampaignDetail  {
	public static function showDetail(){
		XboSet::getXboSet("pmcampaign");
		XboSet::showDetail();	
	}	
}

PMCampaignDetail::showDetail();


?>