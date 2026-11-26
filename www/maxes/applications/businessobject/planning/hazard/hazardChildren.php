<?php 
require_once ("../../eat.php");



class AssetsChildren {

	public static function init(){
		XboSet::validateChildren();	
		AssetsChildren::displayData();
		
	}
	public static function displayData(){
		$getOwner = XboSet::getOwner();
		$tableid = Xbo::getTableID();
		if($tableid=="mxtable_hazardprecaution_child"){
			Xbo::select("No", "precautionlinenum");
			Xbo::select("Pencegahan", "precaution");
			Xbo::select("Pencegahan", "precautions.description");
			Xbo::select("Remarks", "remarks");
			XboSet::getXboSet("hazardprecaution", $getOwner);
			XboSet::displayData(["hazardprecaution.precaution = precautions.precaution"]);
		}
		
		
	}
}

AssetsChildren::init();

?>