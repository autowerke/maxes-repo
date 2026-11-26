<?php 
include ("../../eat.php");

class PrecautionsDetail  {
	public static function showDetail(){
		XboSet::getXboSet("precautions");
		XboSet::showDetail();	
	}	
}

PrecautionsDetail::showDetail();


?>