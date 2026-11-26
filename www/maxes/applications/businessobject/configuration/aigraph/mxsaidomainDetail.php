<?php 
include ("../../eat.php");

class MXSAIDOMAIN  {
	public static function showDetail(){
		XboSet::getXboSet("mxsaidomain");
		XboSet::showDetail();	
	}	
}

MXSAIDOMAIN::showDetail();


?>