<?php 
include ("../../eat.php");

class TermDetail  {
	public static function showDetail(){
		XboSet::getXboSet("term");
		XboSet::showDetail();	
	}	
}

TermDetail::showDetail();


?>