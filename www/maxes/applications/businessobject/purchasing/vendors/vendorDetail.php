<?php 
include ("../../eat.php");

class vendorDetail  {
	public static function showDetail(){
		XboSet::getXboSet("vendor");
		XboSet::showDetail();	
	}	
}

vendorDetail::showDetail();


?>