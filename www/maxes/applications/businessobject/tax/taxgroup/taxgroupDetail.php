<?php 
include ("../../eat.php");

class TaxGroupDetail  {
	public static function showDetail(){
		XboSet::getXboSet("taxgroup");
		XboSet::showDetail();	
	}	
}

TaxGroupDetail::showDetail();


?>