<?php 
include ("../../eat.php");

class OrganizationDetail  {
	public static function showDetail(){
		XboSet::getXboSet("organization");
		XboSet::showDetail();	
	}	
}

OrganizationDetail::showDetail();


?>