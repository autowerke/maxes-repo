<?php 
include ("../eat.php");

class ResourceLeave extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("Person ID", "personid");
		Xbo::select("Description", "description");
		Xbo::select("Leave Type", "leavetype");
		Xbo::select("startdate", "startdate", "date");
		Xbo::select("enddate", "enddate", "date");
		
		XboSet::getXboSet("resourceleave");
		XboSet::displayData();
	}
	public static function init(){
		ResourceLeave::displayData();
	}
	
}
ResourceLeave::init();
?>