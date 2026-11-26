<?php 
include ("../../eat.php");

class soDetail  {
	public static function showDetail(){
		XboSet::getXboSet("so");
		XboSet::showDetail();	
	}	
}

soDetail::showDetail();


?>