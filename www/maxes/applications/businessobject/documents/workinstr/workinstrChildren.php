<?php 
require_once ("../../eat.php");

class WorkInstrChildren {

	public static function init(){
		XboSet::validateChildren();	
		WorkInstrChildren::displayData();
	}
	
	public static function displayData(){
		$getOwner = XboSet::getOwner();
		$tableid = Xbo::getTableID();
		
		// Menangani tabel child Doc. Form (Formulir Terkait pada Work Instructions)
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

WorkInstrChildren::init();
?>