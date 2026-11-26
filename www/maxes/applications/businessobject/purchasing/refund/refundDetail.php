<?php 
include ("../../eat.php");

class Refund  {
	public static function showDetail(){
		XboSet::getXboSet("refund");
		XboSet::showDetail();	
	}	
}

Refund::showDetail();


?>