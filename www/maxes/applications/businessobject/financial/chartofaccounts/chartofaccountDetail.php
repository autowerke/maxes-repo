<?php 
include ("../../eat.php");

class ChartofAccountsDetail  {
	public static function showDetail(){
		XboSet::getXboSet("chartofaccount");
		XboSet::showDetail();	
	}	
}

ChartofAccountsDetail::showDetail();


?>