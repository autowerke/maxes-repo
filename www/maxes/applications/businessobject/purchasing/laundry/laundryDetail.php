<?php 
include ("../../eat.php");

class soDetail  {
	public static function showDetail(){
		XboSet::getXboSet("laundry");
		XboSet::showDetail();	
	}	
}

soDetail::showDetail();


?>