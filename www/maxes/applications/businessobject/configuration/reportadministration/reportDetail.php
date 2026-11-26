<?php 
include ("../../eat.php");

class ReportAdministration  {
	public static function showDetail(){
		XboSet::getXboSet("report");
		XboSet::showDetail();	
	}	
}

ReportAdministration::showDetail();


?>