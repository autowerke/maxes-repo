<?php 
require_once ("../../eat.php");

class FleetRoute extends Xbo {
	
	protected $fleetroutenum;
		
	public function __construct(){
		$this->fleetroutenum = Xbo::getString("fleetroutenum");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("No", "fleetroutenum");
		Xbo::select("From", "fromlocation");
		Xbo::select("To", "tolocation");
		Xbo::select("Round Trip?", "roundtrip");
		
	}
	public static function displayData(){
		FleetRoute::selectData();
		XboSet::getXboSet("fleetroute");
		renderData();
	}
	public static function init(){
		$get = new FleetRoute();		
		$fleetroutenum = $get->fleetroutenum;
		if(isset($fleetroutenum)) {
			XboSet::getXboSet("fleetroute", "fleetroutenum = '$fleetroutenum'");
			$result = Xboset::validate();
			if ($result->num_rows){
				$exec = FleetRoute::changes();
				}else{
				$exec = FleetRoute::add();
			}
		}
		
		FleetRoute::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("fleetroute");
		XboSet::add();
	}

	public static function changes(){
		$get = new FleetRoute();		
		$fleetroutenum = $get->fleetroutenum;		
		XboSet::getXboSet("fleetroute", "fleetroutenum='$fleetroutenum'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

FleetRoute::init();




?>