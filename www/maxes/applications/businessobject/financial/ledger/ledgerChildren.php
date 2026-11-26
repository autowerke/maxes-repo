<?php 
require_once ("../../eat.php");

class LedgerLine {

	public static function init(){
		XboSet::validateChildren();	
		LedgerLine::displayData();
	}
	public static function displayData(){
		global $getOwnerCols;
		$getOwner = XboSet::getOwner();		
		$tableid = Xbo::getTableID();
		
		if($tableid=="mxtable_ledgerline_child"){
			Xbo::select("Ref. Num", "refnum");
			Xbo::select("REF", "ref");
			Xbo::select("Ref. Type", "reftype");
			Xbo::select("Description", "refdescription");
			Xbo::select("Biaya", "cost", "decimal");	
			Xbo::select("Financial Period", "financialperiod");
			Xbo::select("Entered Date", "enterdate", "date");	
			XboSet::getXboSet("ledgerline", $getOwner);
			XboSet::displayData();
		}
		
	}
}

LedgerLine::init();

?>