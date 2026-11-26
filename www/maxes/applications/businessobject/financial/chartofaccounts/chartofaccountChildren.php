<?php 
require_once ("../../eat.php");


class ChartOfAccountChildren {

	public static function init(){
		XboSet::validateChildren();	
		ChartOfAccountChildren::displayData();
		
	}
	public static function displayData(){
		$getOwner = XboSet::getOwner();
		Xbo::select("Account Code", "glaccount2");
		Xbo::select("Description", "chartofaccount.description");
		XboSet::getXboSet("chartofaccountline", $getOwner);
		XboSet::displayData(["chartofaccount.glaccount = chartofaccountline.glaccount2"]);
	}
}

ChartOfAccountChildren::init();

?>