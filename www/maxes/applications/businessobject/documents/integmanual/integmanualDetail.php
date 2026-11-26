<?php 
include ("../../eat.php");

class IntegManual  {
	public static function showDetail(){
		XboSet::getXboSet("integmanual");
		XboSet::showDetail();	
	}	
}

IntegManual::showDetail();


?>