<?php 
include ("../../eat.php");

class BudgetDetail  {
	public static function showDetail(){
		XboSet::getXboSet("budget");
		XboSet::showDetail();	
	}	
}

BudgetDetail::showDetail();


?>