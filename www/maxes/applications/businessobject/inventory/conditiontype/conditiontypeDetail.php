<?php 
include ("../../eat.php");

class ConditionTypeDetail  {
	public static function showDetail(){
		XboSet::getXboSet("conditiontype");
		XboSet::showDetail();	
	}	
}

ConditionTypeDetail::showDetail();


?>