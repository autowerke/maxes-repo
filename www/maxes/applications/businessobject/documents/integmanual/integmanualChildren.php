<?php 
require_once ("../../eat.php");

class IntegManualItem {

	public static function init(){
		XboSet::validateChildren();	
		IntegManualItem::displayData();
	}
	
	
	public static function displayData(){
		$getOwner = XboSet::getOwner();
		$tableid = Xbo::getTableID();
		if($tableid=="mxtable_integmanualitem_child"){
			Xbo::select("Procedure Num.", "proceduresnum");
			Xbo::select("Description", "description");
			Xbo::select("Remarks", "remarks");
			Xbo::select("Notes", "notes");
			XboSet::getXboSet("integmanualitem", $getOwner);
			XboSet::displayData();
		}
		
	}
}

IntegManualItem::init();

?>