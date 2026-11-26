<?php 
include ("../../eat.php");

class TicketDetail  {
	public static function showDetail(){
		XboSet::getXboSet("ticket");
		XboSet::showDetail();	
	}	
}

TicketDetail::showDetail();


?>