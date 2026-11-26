<?php 
include ("../../eat.php");

class LocationDetail  {
	public static function showDetail(){
		XboSet::getXboSet("locations");
		
		XboSet::showDetail();	
	}	
}

LocationDetail::showDetail();


?>