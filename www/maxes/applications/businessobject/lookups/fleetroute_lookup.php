<?php 
include ("../eat.php");

class addressLookup extends Xbo {

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
		Xbo::select("Rute", "fleetroutenum");
		Xbo::select("PP?", "roundtrip");
		XboSet::getXboSet("fleetroute",$where);
		XboSet::displayData();
	}
	public static function init(){
		addressLookup::displayData();
	}
	
}
addressLookup::init();
?>