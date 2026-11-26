<?php 
include ("../../eat.php");

class SafetyPlansDetail  {
	public static function showDetail(){
		XboSet::getXboSet("safetyplans");
		XboSet::showDetail();	
	}	
}

SafetyPlansDetail::showDetail();


?>