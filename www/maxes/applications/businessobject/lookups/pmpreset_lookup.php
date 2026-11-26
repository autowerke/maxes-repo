<?php 
include ("../eat.php");

class PMPreset extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		if(Super::hasParam("pmcampaignnum")){
			$pmcampaignnum = Super::getParam("pmcampaignnum");
			$where  = "pmcampaignnum = '$pmcampaignnum'";
		}else{
			$where = "";
		}
		
		
		Xbo::select("Preset ID", "pmpresetnum");
		Xbo::select("Preset Description", "description");
		Xbo::select("Created Date", "enterdate", "date");
		Xbo::select("Enter By", "enterby");
		Xbo::select("PM Campaign", "pmcampaignnum");
		
		XboSet::getXboSet("pmpreset", $where);
		XboSet::displayData();
	}
	public static function init(){
		PMPreset::displayData();
	}
	
}
PMPreset::init();
?>