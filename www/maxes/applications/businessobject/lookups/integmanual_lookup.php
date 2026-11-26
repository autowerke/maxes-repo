<?php 
include ("../eat.php");

class IntegManualLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("Integ. Manual", "integmanualnum");
		Xbo::select("Title", "manualtitle");
		Xbo::select("Description", "description");
		Xbo::select("Remarks", "remarks");
		Xbo::select("Status", "status");
		Xbo::select("Effective Date", "effectivedate", "date");
		Xbo::select("Expiry Date", "expirydate", "date");
		Xbo::select("Revision.", "revision");
		Xbo::select("Rev. Num.", "oldintegmanualnum");
		XboSet::getXboSet("integmanual"); 	
		XboSet::displayData();
	}
	public static function init(){
		IntegManualLookup::displayData();
	}
	
}
IntegManualLookup::init();
?>