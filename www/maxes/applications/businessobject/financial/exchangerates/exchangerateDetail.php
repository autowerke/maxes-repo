<?php 
include ("../../eat.php");

class ExchangeRateDetail  {
	public static function showDetail(){
		XboSet::getXboSet("exchangerate");
		XboSet::showDetail();	
	}	
}

ExchangeRateDetail::showDetail();


?>