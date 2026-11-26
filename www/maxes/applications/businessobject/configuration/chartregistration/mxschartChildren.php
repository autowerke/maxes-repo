<?php 
require_once ("../../eat.php");

class MxsChartChildren {

	public static function init(){
		XboSet::validateChildren();	
		MxsChartChildren::displayData();
	}
	public static function displayData(){
		$getOwner = XboSet::getOwner();	
		Xbo::select("Line Num.", "chartlinenum");
		Xbo::select("Application", "appname");
		Xbo::select("Description", "mxsapp.description");
		Xbo::select("Sort Order", "sortorder");
		XboSet::getXboSet("mxschartline", $getOwner);
		XboSet::displayData(["mxschartline.appname = mxsapp.appname"]);
	}
}

MxsChartChildren::init();

?>