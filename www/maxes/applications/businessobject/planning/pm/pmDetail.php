<?php 
include ("../../eat.php");

class HazardDetail  {
	public static function showDetail(){
		XboSet::getXboSet("pm");
		XboSet::showDetail();	
	}	
}

HazardDetail::showDetail();


?>