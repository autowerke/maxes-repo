<?php 
include ("../../eat.php");

class NotificationDetail  {
	public static function showDetail(){
		XboSet::getXboSet("notification");
		XboSet::showDetail();	
	}	
}

NotificationDetail::showDetail();


?>