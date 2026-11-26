<?php 
include ("../../eat.php");

class CustinventoryDetail  {
	public static function showDetail(){
		XboSet::getXboSet("custinventory");
		XboSet::showDetail();	
	}	
}

CustinventoryDetail::showDetail();


?>