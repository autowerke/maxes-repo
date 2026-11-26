<?php 
include ("../eat.php");

class RFQLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("RFQ", "rfqnum");
		Xbo::select("Description", "description");
		Xbo::select("RFQ Date", "rfqdate", "date");
		Xbo::select("Status", "status");
		XboSet::getXboSet("rfq");
		XboSet::displayData();
	}
	
	public static function init(){
		RFQLookup::displayData();
	}
	
}

RFQLookup::init();




?>