<?php 
include ("../../eat.php");

class CashReceipt  {
	public static function showDetail(){
		XboSet::getXboSet("cashreceipt");
		XboSet::showDetail();	
	}	
}

CashReceipt::showDetail();


?>