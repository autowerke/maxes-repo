<?php 
include ("../../eat.php");

class MRDetail  {
	public static function showDetail(){
		XboSet::getXboSet("mr");
		XboSet::showDetail();	
	}	
}

MRDetail::showDetail();


?>