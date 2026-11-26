<?php 
include ("../../eat.php");

class MeterGroup  {
	public static function showDetail(){
		XboSet::getXboSet("metergroup");
		XboSet::showDetail();	
	}	
}

MeterGroup::showDetail();


?>