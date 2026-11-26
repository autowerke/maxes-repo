<?php 
include ("../eat.php");

class DocFormLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("Doc. Form Num.", "docformnum");
		Xbo::select("Form Name", "docformname");
		Xbo::select("Owner Team", "ownerteam");
		Xbo::select("Parent Procedure", "proceduresnum");
		Xbo::select("Work Instr. Num", "workinstrnum");
		Xbo::select("Category", "category");
		Xbo::select("Status", "status");
		Xbo::select("Revision.", "revision");
		Xbo::select("Rev. Num.", "olddocformnum");
		XboSet::getXboSet("docform"); 	
		XboSet::displayData();
	}
	public static function init(){
		DocFormLookup::displayData();
	}
	
}
DocFormLookup::init();
?>