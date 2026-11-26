<?php 
include ("../../eat.php");

class ProceduresDetail  {
	public static function showDetail(){
		XboSet::getXboSet("procedures");
		XboSet::showDetail();	
	}	
}

ProceduresDetail::showDetail();


?>