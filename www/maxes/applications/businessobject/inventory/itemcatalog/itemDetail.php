<?php 
include ("../../eat.php");

class itemDetail  {
	public static function showDetail(){
		XboSet::getXboSet("item");
		XboSet::showDetail();	
	}	
}

itemDetail::showDetail();


?>