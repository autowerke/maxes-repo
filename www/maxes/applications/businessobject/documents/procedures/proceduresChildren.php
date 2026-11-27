<?php 
require_once ("../../eat.php");

class ProceduresChildren {

	public static function init(){
		XboSet::validateChildren();	
		ProceduresChildren::displayData();
	}
	
	public static function displayData(){
		$getOwner = XboSet::getOwner();
		$tableid = Xbo::getTableID();
		
		// 1. Menangani tabel child Work Instructions (IK)
		if($tableid == "mxtable_workinstr_child"){
			Xbo::select("Instr. Num", "workinstrnum");
			Xbo::select("WI Identifier.", "docidentifier");
			Xbo::select("Instr. Name", "workinstrname");
			Xbo::select("Category", "category");
			Xbo::select("Status", "status");
			Xbo::select("Revision", "revision");
			XboSet::getXboSet("workinstr", $getOwner);
			XboSet::displayData();
		}
		
		// 2. Menangani tabel child Doc. Form (Formulir Terkait)
		if($tableid == "mxtable_docform_child"){
			Xbo::select("Doc. Form Num", "docformnum");
			Xbo::select("Form Name", "docformname");
			Xbo::select("Category", "category");
			Xbo::select("Status", "status");
			Xbo::select("Revision", "revision");
			XboSet::getXboSet("docform", $getOwner);
			XboSet::displayData();
		}
	}
}

ProceduresChildren::init();
?>