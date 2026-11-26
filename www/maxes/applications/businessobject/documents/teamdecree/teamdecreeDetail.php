<?php 
include ("../../eat.php");

class TeamdecreeDetail {
	public static function showDetail(){
		XboSet::getXboSet("teamdecree");
		XboSet::showDetail();	
	}	
}

TeamdecreeDetail::showDetail();
?>