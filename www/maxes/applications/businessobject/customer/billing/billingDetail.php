<?php 
include ("../../eat.php");

class Billing  {
	public static function showDetail(){
		XboSet::getXboSet("billing");
		XboSet::showDetail();	
	}	
}

Billing::showDetail();


?>