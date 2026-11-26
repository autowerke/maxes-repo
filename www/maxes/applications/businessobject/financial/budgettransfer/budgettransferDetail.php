<?php 
include ("../../eat.php");

class BudgetTransferDetail  {
	public static function showDetail(){
		XboSet::getXboSet("budgettransfer");
		XboSet::showDetail();	
	}	
}

BudgetTransferDetail::showDetail();


?>