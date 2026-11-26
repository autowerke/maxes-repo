<?php 
include ("../../eat.php");

class CurrencyDetail  {
	public static function showDetail(){
		XboSet::getXboSet("currency");
		XboSet::showDetail();	
	}	
}

CurrencyDetail::showDetail();


?>