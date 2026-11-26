<?php 
include ("../../eat.php");

class RFSDetail  {
	public static function showDetail(){
		XboSet::getXboSet("rfs");
		XboSet::showDetail();	
	}	
}

RFSDetail::showDetail();


?>