<?php 
include ("../eat.php");

class WorkInstrLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("Num.", "workinstrnum");
		Xbo::select("Parent Procedure", "proceduresnum");
		Xbo::select("Doc. Identifier", "docidentifier");
		Xbo::select("Work Instr. Name", "workinstrname");
		Xbo::select("Category", "category");
		Xbo::select("Status", "status");
		Xbo::select("Revision.", "revision");
		Xbo::select("Rev. Num.", "oldworkinstrnum");
		XboSet::getXboSet("workinstr"); 	
		XboSet::displayData();
	}
	public static function init(){
		WorkInstrLookup::displayData();
	}
	
}
WorkInstrLookup::init();
?>