<?php 
include ("../../eat.php");

class soDetail  {
	public static function showDetail(){
		XboSet::getXboSet("salesquotation");
		XboSet::showDetail();	
	}	
}

soDetail::showDetail();


?>