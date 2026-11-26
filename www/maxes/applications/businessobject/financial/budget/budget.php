<?php 
require_once ("../../eat.php");

class Budget extends Xbo {
	
	protected $exchangerateid;
	public $budgetnum;
	public function __construct(){
		$this->budgetnum = Xbo::getString("budgetnum");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("Budget Number", "budgetnum");
		Xbo::select("Budget Period", "budgetperiod");
		Xbo::select("Budget Version", "budgetversion");
		Xbo::select("Description", "description");
		Xbo::select("Status", "status");
		Xbo::select("Total Budget Amount", "totalbudgetamount", "decimal");
	}
	public static function displayData(){
		Budget::selectData();
		XboSet::getXboSet("budget");
		renderData();
	}
	public static function init(){
		$get = new Budget();		
		$budgetnum = $get->budgetnum;
		
		if(isset($budgetnum) ) {
			XboSet::getXboSet("budget", "budgetnum='$budgetnum'");
			$result = Xboset::validate();
			if ($result->num_rows){
				$exec = Budget::changes();
				}else{
				$exec = Budget::add();
			}
		}
		
		Budget::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("budget");
		XboSet::add();
	}

	public static function changes(){
		$get = new Budget();		
		$budgetnum = $get->budgetnum;
		XboSet::getXboSet("budget", "budgetnum='$budgetnum'");	
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

Budget::init();




?>