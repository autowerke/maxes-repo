<?php 
require_once ("../../eat.php");

class TaxGroupChildren {

	public static function init(){
		XboSet::validateChildren();	
		TaxGroupChildren::displayData();
	}
	
	
	public static function displayData(){
		$getOwner = XboSet::getOwner();
		$getOwner = "taxgroupline.".$getOwner;
		$tableid = Xbo::getTableID();
		if($tableid=="mxtable_taxgroupline_child"){
			Xbo::select("Line Num.", "taxgrouplinenum");
			Xbo::select("E Faktur", "taxreceiptnum");
			Xbo::select("Status", "taxreceipt.status");
			Xbo::select("Quantity", "taxreceipt.quantity", "decimal");
			Xbo::select("Total  Price", "taxreceipt.lineprice", "decimal");
			XboSet::getXboSet("taxgroupline", $getOwner);
			XboSet::displayData(["taxreceipt.taxgroupnum = taxgroupline.taxgroupnum and taxgroupline.taxreceiptnum = taxreceipt.taxreceiptnum "]);
		}
		
	}
}

TaxGroupChildren::init();

?>