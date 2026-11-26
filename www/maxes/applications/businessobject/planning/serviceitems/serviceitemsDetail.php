<?php 
include ("../../eat.php");

class ServiceItemsDetail  {
	public static function showDetail(){
		XboSet::getXboSet("serviceitems");
		XboSet::showDetail();	
	}	
}

ServiceItemsDetail::showDetail();


?>