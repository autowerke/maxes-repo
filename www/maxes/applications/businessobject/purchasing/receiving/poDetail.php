<?php 
include ("../../eat.php");

class ReceivingDetail  {
	public static function showDetail(){
		XboSet::getXboSet("po");
		XboSet::showDetail();	
	}	
}

ReceivingDetail::showDetail();


?>