<?php 
include ("../../eat.php");

class poDetail  {
	public static function showDetail(){
		XboSet::getXboSet("po");
		XboSet::showDetail();	
	}	
}

poDetail::showDetail();


?>