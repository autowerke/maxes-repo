<?php
require_once ("../../eat.php");

class AnnualBudgetAlloc extends Xbo {

	protected $annualbudgetallocid;
	public $annualbudgetallocnum;
	public function __construct(){
		$this->annualbudgetallocnum = Xbo::getString("annualbudgetallocnum");
	}

	public function __destruct()
    {
        return null;
    }

	public static function selectData(){
		Xbo::select("Alloc Number", "annualbudgetallocnum");
		Xbo::select("PO Number", "ponum");
		Xbo::select("Budget Year", "budgetyear", "number");
		Xbo::select("Budget Num", "budgetnum"); 
		Xbo::select("PO Line", "polinenum");
		Xbo::select("Budget Line Num", "budgetlinenum"); 
		Xbo::select("Allocated Amount", "allocatedamount", "decimal");
		Xbo::select("Status", "status");
	}

	public static function displayData(){
		AnnualBudgetAlloc::selectData();
		XboSet::getXboSet("annualbudgetalloc");
		renderData();
	}

	public static function init(){
		$get = new AnnualBudgetAlloc();
		$annualbudgetallocnum = $get->annualbudgetallocnum;

		if(isset($annualbudgetallocnum) ) {
			XboSet::getXboSet("annualbudgetalloc", "annualbudgetallocnum='$annualbudgetallocnum'");
			$result = Xboset::validate();
			if ($result->num_rows){
				$exec = AnnualBudgetAlloc::changes();
			}else{
				$exec = AnnualBudgetAlloc::add();
			}
		}

		AnnualBudgetAlloc::displayData();
	}


	public static function add(){
		XboSet::getXboSet("annualbudgetalloc");
		XboSet::add();
	}

	public static function changes(){
		$get = new AnnualBudgetAlloc();
		$annualbudgetallocnum = $get->annualbudgetallocnum;
		XboSet::getXboSet("annualbudgetalloc", "annualbudgetallocnum='$annualbudgetallocnum'");
		XboSet::changes();
	}

	public static function showDetail(){
		XboSet::showDetail();
	}
}

AnnualBudgetAlloc::init();

?>