<?php 
include ("../../eat.php");

class DocTicketDetail  {
	public static function showDetail(){
		XboSet::getXboSet("docticket");
		XboSet::showDetail();	
	}	
}

DocTicketDetail::showDetail();


?>