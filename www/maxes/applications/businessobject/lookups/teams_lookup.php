<?php 
include ("../eat.php");

class TeamLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	
	
	public static function displayData(){
			
		Xbo::select("Team", "teamnum");
		Xbo::select("Description", "description");
		XboSet::getXboSet("teams");
		XboSet::displayData();
	
	}
	
	public static function init(){
		TeamLookup::displayData();
	}
	
}

TeamLookup::init();




?>