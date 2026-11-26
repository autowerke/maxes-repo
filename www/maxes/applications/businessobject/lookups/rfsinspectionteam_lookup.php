<?php 
include ("../eat.php");

class RFSInspectionTeam extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		$where =null;
		$rfsnum = Super::getParam("rfsnum");
		if(!empty($rfsnum)) $where  = "rfsinsteam.rfsnum ='".$rfsnum."'";
		else $where = null;
		
		
		Xbo::select("User", "personid");
		Xbo::select("Name", "employees.fullname");
		Xbo::select("Title", "employees.title");
		Xbo::select("Department", "employees.department");
		XboSet::getXboSet("rfsinsteam", $where);
		XboSet::displayData(["rfsinsteam.personid = employees.personid"]);
	
	}
	
	public static function init(){
		RFSInspectionTeam::displayData();
	}
	
}

RFSInspectionTeam::init();




?>