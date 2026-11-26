<?php 
include ("../eat.php");

class PmWOLine extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		if(Super::hasParam("pmnum")){
			$pmnum = Super::getParam("pmnum");
			$where  = "pmnum = '$pmnum'";
		}else{
			$where = "";
		}
		Xbo::select("PM", "pmnum");
		Xbo::select("WO", "wonum");
		Xbo::select("Line", "pmwolinenum");
		Xbo::select("Targ. Start Date", "targstartdate", "date");
		Xbo::select("Description", "description");
		Xbo::select("Owner", "owner");
		Xbo::select("Leader", "leader");
		Xbo::select("Team", "teamnum");
		XboSet::getXboSet("pmwoline", $where);
		XboSet::displayData();
	}
	public static function init(){
		PmWOLine::displayData();
	}
	
}
PmWOLine::init();
?>