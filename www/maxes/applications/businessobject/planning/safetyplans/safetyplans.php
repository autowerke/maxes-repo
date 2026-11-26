<?php 
require_once ("../../eat.php");

class SafetyPlans extends Xbo {
	
	protected $safetyplan;
	private $tablename = "safetyplans";
	
	public function __construct(){
		$this->safetyplan = Xbo::getString("safetyplan");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("Kode Safety", "safetyplan");
		Xbo::select("Description", "description");
	}
	public static function displayData(){
		$get = new SafetyPlans();	
		SafetyPlans::selectData();
		XboSet::getXboSet($get->tablename);
		renderData();
	}
	public static function init(){
		$get = new SafetyPlans();		
		$safetyplan = $get->safetyplan;
		if(isset($safetyplan)) {
			XboSet::getXboSet($get->tablename, "safetyplan = '$safetyplan'");
			$result = Xboset::validate();
			if ($result->num_rows){
				$exec = SafetyPlans::changes();
				}else{
				$exec = SafetyPlans::add();
			}
		}
		
		SafetyPlans::displayData();
	}
	
	
	public static function add(){
		$get = new SafetyPlans();
		XboSet::getXboSet($get->tablename);
		XboSet::add();
	}

	public static function changes(){
		$get = new SafetyPlans();		
		$safetyplan =	$get->safetyplan;		
		XboSet::getXboSet($get->tablename, "safetyplan='$safetyplan'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

SafetyPlans::init();




?>