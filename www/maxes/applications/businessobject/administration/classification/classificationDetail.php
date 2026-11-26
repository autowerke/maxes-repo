<?php 
include ("../../eat.php");

class ClasificationsDetail  {
	public static function showDetail(){
		XboSet::getXboSet("classification");
		XboSet::showDetail();	
	}	
}

ClasificationsDetail::showDetail();


?>