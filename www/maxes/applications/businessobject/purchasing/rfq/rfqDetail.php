<?php 
include ("../../eat.php");

class RFQDetail  {
	public static function showDetail(){
		XboSet::getXboSet("rfq");
		XboSet::showDetail();	
	}	
}

RFQDetail::showDetail();


?>