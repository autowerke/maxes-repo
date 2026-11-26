<?php 
include ("../eat.php");

class DonationTypeLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("Tipe Donasi", "donationtype");
		Xbo::select("Description", "description");
		XboSet::getXboSet("donationtype");
		XboSet::displayData();
	}
	public static function init(){
		DonationTypeLookup::displayData();
	}
	
}
DonationTypeLookup::init();
?>