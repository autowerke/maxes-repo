<?php 
include ("../../eat.php");

class MeterDetail  {
	public static function showDetail(){
		XboSet::getXboSet("meter");
		XboSet::showDetail();	
	}	
}

MeterDetail::showDetail();


?>