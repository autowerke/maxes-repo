<?php 
include ("../eat.php");

class RFIDLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
					
		Xbo::select("Serial Number", "sn");
		Xbo::select("Entered Date", "enterdate", "date");
		XboSet::getXboSet("rfidtag");
		XboSet::displayData();
	}
	public static function init(){
		RFIDLookup::displayData();
	}
	
}
RFIDLookup::init();
?>