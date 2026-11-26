<?php 
include ("../../eat.php");

class DonationTypeDetail  {
	public static function showDetail(){
		XboSet::getXboSet("donationtype");
		XboSet::showDetail();	
	}	
}

DonationTypeDetail::showDetail();


?>