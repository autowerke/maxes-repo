<?php 
include ("../eat.php");

class TeamDecreeLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("Num.", "teamdecreenum");
		Xbo::select("Name", "decreetitle");
		Xbo::select("Description", "startdate", "date");
		Xbo::select("Type", "enddate", "date");
		Xbo::select("Category", "description");
		Xbo::select("Status", "status");
		XboSet::getXboSet("teamdecree"); 	
		XboSet::displayData();
	}
	public static function init(){
		TeamDecreeLookup::displayData();
	}
	
}
TeamDecreeLookup::init();
?>