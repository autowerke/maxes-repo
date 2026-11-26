<?php 
include ("../../eat.php");

class RFIDMapDetail  {
	public static function showDetail(){
		XboSet::getXboSet("rfidmap");
		XboSet::showDetail();	
	}	
}

RFIDMapDetail::showDetail();


?>