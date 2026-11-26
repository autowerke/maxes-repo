<?php 
include ("../eat.php");

class ProceduresLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("Num.", "proceduresnum");
		Xbo::select("Name", "docname");
		Xbo::select("Description", "description");
		Xbo::select("Type", "doctype");
		Xbo::select("Category", "category");
		Xbo::select("Status", "status");
		Xbo::select("Effective Date", "effectivedate", "date");
		Xbo::select("Expiry Date", "expirydate", "date");
		Xbo::select("Revision.", "revision");
		Xbo::select("Rev. Num.", "oldproceduresnum");
		XboSet::getXboSet("procedures"); 	
		XboSet::displayData();
	}
	public static function init(){
		ProceduresLookup::displayData();
	}
	
}
ProceduresLookup::init();
?>