<?php 
include ("../../eat.php");

class FleetRouteDetail  {
	public static function showDetail(){
		XboSet::getXboSet("fleetroute");
		XboSet::showDetail();	
	}	
}

FleetRouteDetail::showDetail();


?>