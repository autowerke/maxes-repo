<?php 
include ("../../eat.php");

class prDetail  {
	public static function showDetail(){
		XboSet::getXboSet("pr");
		XboSet::showDetail();
	}	
}

prDetail::showDetail();


?>