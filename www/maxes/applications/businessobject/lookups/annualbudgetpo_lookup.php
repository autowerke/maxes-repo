<?php 
include ("../eat.php");

class AnnualBudgetPO extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
	
		$where =null;
		
		$selectedyear = Super::getParam("budgetyear");
		$annualponum = Super::getParam("annualponum");
		
		$selectedyear = ($selectedyear !== null) ? $selectedyear : '';
		$annualponum = ($annualponum !== null) ? $annualponum : '';

		$where = (!empty($annualponum) && !empty($selectedyear))
			? "selected_year = '$selectedyear' and ponum = '$annualponum'"
			: null;
			
		
		Xbo::select("PO", "ponum");
		Xbo::select("PO Line", "polinenum");
		Xbo::select("Last Year", "last_year");
		Xbo::select("Last Budget Num", "last_budget");
		Xbo::select("Last Budget Line", "last_budgetlinenum", "decimal");
		Xbo::select("Last Allocated Amount", "last_allocatedamount", "decimal");
		Xbo::select("Selected Year", "selected_year");
		Xbo::select("New Budget#", "_input_text_newbudgetnum_budget");
		Xbo::select("New Budget Line#", "_input_text_newbudgetlinenum_budgetline@newbudgetnum");
		XboSet::getXboSet("annualbudgetpo", $where);
		XboSet::displayData();
	}
	
	public static function init(){
		AnnualBudgetPO::displayData();
	}
	
}

AnnualBudgetPO::init();




?>