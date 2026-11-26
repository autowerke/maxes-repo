<?php 
include ("../../eat.php");

class Ledger  {
	public static function showDetail(){
		XboSet::getXboSet("ledger");
		XboSet::showDetail();	
	}	
}

Ledger::showDetail();


?>