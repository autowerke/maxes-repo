<?php 
include ("../eat.php");

class WOLogs extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$wonum = Super::getParam("wonum");
		$wr = [];
		if(!empty($wonum))  array_push($wr, " wonum = '$wonum'");
		
		if(count($wr)>0){
			$where = implode(" and ", $wr);
		}else  $where =null;
		
		Xbo::select("Line Num.", "loglinenum");
		Xbo::select("Description", "description");
		Xbo::select("Reported By", "reportedby");
		Xbo::select("Reported Date", "reporteddate");
		Xbo::select("Entered Date", "enterdate", "date");
		Xbo::select("Changed By", "enterby");
		
		XboSet::getXboSet("wologs", "wologs.".$where);
		XboSet::displayData();
	}
	
	public static function init(){
		WOLogs::displayData();
	}
	
}

WOLogs::init();




?>