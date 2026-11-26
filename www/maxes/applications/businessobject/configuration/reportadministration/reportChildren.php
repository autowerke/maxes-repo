<?php 
require_once ("../../eat.php");

class ReportAdministrationChildren {

	public static function init(){
		XboSet::validateChildren();	
		ReportAdministrationChildren::displayData();
	}
	public static function displayData(){
		$getOwner = XboSet::getOwner();	
		
		Xbo::select("Parameter", "parametername");
		Xbo::select("Lookup", "lookupname");
		Xbo::select("Display Name", "displayname");
		Xbo::select("Default Value", "defaultvalue");
		XboSet::getXboSet("reportparameter", $getOwner);
		XboSet::displayData();
	}
}

ReportAdministrationChildren::init();

?>