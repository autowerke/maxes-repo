<?php 
include ("../eat.php");

class BudgetStatus extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
					
		Xbo::select("Status", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks", "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='budget'");
		XboSet::displayData();
	}
	public static function init(){
		BudgetStatus::displayData();
	}
	
}
BudgetStatus::init();
?>