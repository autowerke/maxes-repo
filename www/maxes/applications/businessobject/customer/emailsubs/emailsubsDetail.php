<?php 
include ("../../eat.php");

class EmailSubsDetail  {
	public static function showDetail(){
		XboSet::getXboSet("emailsubs");
		XboSet::showDetail();	
	}	
}

EmailSubsDetail::showDetail();


?>