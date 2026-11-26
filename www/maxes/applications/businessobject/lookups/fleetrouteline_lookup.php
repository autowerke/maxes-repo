<?php 
include ("../eat.php");

class FleetRouteAssets extends Xbo {

	public function __destruct()
    {
       return null;
    }
	public static function displayData(){
		$whereArr=array();
		$fleetroutenum = Super::getParam("fleetroutenum");
		if(!empty($fleetroutenum)) array_push($whereArr, "fleetroutenum ='".$fleetroutenum."'");
		else $where = null;
		$where = implode(" and ", $whereArr);
		Xbo::select("Armada", "assetnum");
		Xbo::select("Description", "description");
		XboSet::getXboSet("fleetrouteline",$where);
		XboSet::displayData();
	}
	public static function init(){
		FleetRouteAssets::displayData();
	}
	
}
FleetRouteAssets::init();
?>