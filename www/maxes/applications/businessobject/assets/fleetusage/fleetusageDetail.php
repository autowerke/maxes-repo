<?php 
include ("../../eat.php");

class AssetUtilizationDetail  {
	public static function showDetail(){
		XboSet::getXboSet("fleetusage");
		XboSet::showDetail();	
	}	
}

AssetUtilizationDetail::showDetail();


?>