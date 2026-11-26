<?php 
include ("../../eat.php");

class AnnualBudgetAlloc  {
	public static function showDetail(){
		XboSet::getXboSet("annualbudgetalloc");
		XboSet::showDetail();	
	}	
}

AnnualBudgetAlloc::showDetail();


?>