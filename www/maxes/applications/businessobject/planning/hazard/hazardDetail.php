<?php 
include ("../../eat.php");

class HazardDetail  {
	public static function showDetail(){
		XboSet::getXboSet("hazard");
		XboSet::showDetail();	
	}	
}

HazardDetail::showDetail();


?>