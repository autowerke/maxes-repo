<?php
require_once ("../../eat.php");

class BudgetLineChildren {

    public static function init(){
        XboSet::validateChildren();
        BudgetLineChildren::displayData();

    }
    public static function displayData(){
		$getOwner = XboSet::getOwner();
		$tableid = Xbo::getTableID();
		
		if($tableid=="mxtable_budgetline_child"){
			Xbo::select("Line", "budgetlinenum");
			Xbo::select("Code", "budgetlinecode");
			Xbo::select("Description", "description");
			Xbo::select("Budget Amt.", "budgetamount", "decimal");
			Xbo::select("Amount.", "allocatedamount", "decimal");
			Xbo::select("Committed", "committedamount", "decimal");
			Xbo::select("Remaining.", "remainingamount", "decimal");
			Xbo::select("Expense.", "actualexpense", "decimal");
			Xbo::select("Actual Rem.", "actualamount", "decimal");
			Xbo::select("Created Date", "createddate", "date");
			Xbo::select("Closing Date", "closingdate", "date");
			Xbo::select("Status", "status");
			XboSet::getXboSet("budgetline", $getOwner); 
			XboSet::displayData(); 
		}
		
       
    }
}

BudgetLineChildren::init();

?>