<?php 
include ("../../eat.php");

class TaxReceivingDetail  {
	public static function showDetail(){
		XboSet::getXboSet("taxreceipt");
		XboSet::showDetail();	
	}	
}

TaxReceivingDetail::showDetail();


?>