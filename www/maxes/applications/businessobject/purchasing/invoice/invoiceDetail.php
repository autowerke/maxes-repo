<?php 
include ("../../eat.php");

class invoiceDetail  {
	public static function showDetail(){
		XboSet::getXboSet("invoice");
		XboSet::showDetail();	
	}	
}

invoiceDetail::showDetail();


?>