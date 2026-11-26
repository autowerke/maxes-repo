<?php 
include ("../eat.php");

class Budget extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
					
		Xbo::select("Budget#", "budgetnum");
		Xbo::select("Description", "description");
		Xbo::select("Period", "budgetperiod");
		Xbo::select("Amount", "totalbudgetamount", "decimal");
		XboSet::getXboSet("budget", "status in ('active', 'approved')");
		XboSet::displayData();
	}
	public static function init(){
		Budget::displayData();
	}
	
}
Budget::init();
?>