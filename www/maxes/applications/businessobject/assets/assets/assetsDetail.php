<?php 
include ("../../eat.php");

class assetsDetail  {
	public static function showDetail(){
		XboSet::getXboSet("assets");
		XboSet::showDetail();	
	}	
}

assetsDetail::showDetail();


?>