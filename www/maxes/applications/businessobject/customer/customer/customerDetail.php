<?php 
include ("../../eat.php");

class customerDetail  {
	public static function showDetail(){
		XboSet::getXboSet("customer");
		XboSet::showDetail();	
	}	
}

customerDetail::showDetail();


?>