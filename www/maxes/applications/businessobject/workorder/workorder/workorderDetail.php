<?php 
include ("../../eat.php");

class WorkorderDetail  {
	public static function showDetail(){
		XboSet::getXboSet("workorder");
		XboSet::showDetail();	
	}	
}

WorkorderDetail::showDetail();


?>