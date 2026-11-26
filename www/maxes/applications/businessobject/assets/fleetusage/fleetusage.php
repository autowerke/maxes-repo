<?php 
require_once ("../../eat.php");

class FleetUsage extends Xbo {
	
	protected $fleetusagenum;
		
	public function __construct(){
		$this->fleetusagenum = Xbo::getString("fleetusagenum");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("Num", "fleetusagenum");
		Xbo::select("Route", "fleetroutenum");
		Xbo::select("Usage Date", "usagedate", "date");
		Xbo::select("Asset", "assetnum");
		Xbo::select("Type", "usagetype");
		Xbo::select("Selling Price", "totalprice", "decimal");
		Xbo::select("Expense Price", "expense", "decimal");
		Xbo::select("Margin", "margin", "decimal");
		
	}
	public static function displayData(){
		FleetUsage::selectData();
		XboSet::getXboSet("fleetusage");
		renderData();
	}
	public static function init(){
		$get = new FleetUsage();		
		$fleetusagenum = $get->fleetusagenum;
		if(isset($fleetusagenum)) {
			XboSet::getXboSet("fleetusage", "fleetusagenum = '$fleetusagenum'");
			$result = Xboset::validate();
			if ($result->num_rows){
				$exec = FleetUsage::changes();
				}else{
				$exec = FleetUsage::add();
			}
		}
		
		FleetUsage::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("fleetusage");
		XboSet::add();
	}

	public static function changes(){
		$get = new FleetUsage();		
		$fleetusagenum = $get->fleetusagenum;		
		XboSet::getXboSet("fleetusage", "fleetusagenum='$fleetusagenum'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

FleetUsage::init();




?>