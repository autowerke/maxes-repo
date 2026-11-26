<?php 
require_once ("../../eat.php");

class PmCampaign extends Xbo {
	
	protected $pmcampaignnum;
		
	public function __construct(){
		$this->pmcampaignnum = Xbo::getString("pmcampaignnum");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("PM Campaign", "pmcampaignnum");
		Xbo::select("Description", "description");
		Xbo::select("Start Date", "startdate", "date");
		Xbo::select("End Date", "enddate", "date");
		Xbo::select("Status", "status");
		Xbo::select("Enter By", "enterby");
	}
	public static function displayData(){
		PmCampaign::selectData();
		XboSet::getXboSet("pmcampaign");
		renderData();
	}
	public static function init(){
		$get = new PmCampaign();		
		$pmcampaignnum = $get->pmcampaignnum;
		if(isset($pmcampaignnum)) {
			XboSet::getXboSet("pmcampaign", "pmcampaignnum = '$pmcampaignnum'");
			$result = Xboset::validate();
			if ($result->num_rows){
				$exec = PmCampaign::changes();
				}else{
				$exec = PmCampaign::add();
			}
		}
		
		PmCampaign::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("pmcampaign");
		XboSet::add();
	}

	public static function changes(){
		$get = new PmCampaign();		
		$pmcampaignnum =	$get->pmcampaignnum;		
		XboSet::getXboSet("pmcampaign", "pmcampaignnum='$pmcampaignnum'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

PmCampaign::init();




?>