<?php 
include ("../../eat.php");

class PriceGroupDetail  {
	public static function showDetail(){
		XboSet::getXboSet("pricegroup");
		XboSet::showDetail();	
	}	
}

PriceGroupDetail::showDetail();


?>