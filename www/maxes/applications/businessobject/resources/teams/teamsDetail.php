<?php 
include ("../../eat.php");

class Team  {
	public static function showDetail(){
		XboSet::getXboSet("teams");
		XboSet::showDetail();	
	}	
}

Team::showDetail();


?>