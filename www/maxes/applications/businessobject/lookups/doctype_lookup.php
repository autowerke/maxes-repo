<?php 
include ("../eat.php");

class DocTypeLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("Num.", "doctypenum");
		Xbo::select("Type", "type");
		Xbo::select("Name", "doctypename");
		Xbo::select("Description", "description");
		Xbo::select("Remarks", "remarks");
		XboSet::getXboSet("doctype");
		XboSet::displayData();
	}
	public static function init(){
		DocTypeLookup::displayData();
	}
	
}
DocTypeLookup::init();
?>