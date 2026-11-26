<?php 
include ("../../eat.php");

class PettyCash  {
	public static function showDetail(){
		XboSet::getXboSet("pettycash");
		XboSet::showDetail();	
	}	
}

PettyCash::showDetail();


?>