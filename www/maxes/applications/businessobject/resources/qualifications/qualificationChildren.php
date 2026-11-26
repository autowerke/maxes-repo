<?php 
require_once ("../../eat.php");

class QualificationChildren {

	public static function init(){
		XboSet::validateChildren();	
		QualificationChildren::displayData();
	}
	public static function displayData(){
		XboSet::getXboSet("empqual");
		$getOwner = XboSet::getOwner();		
		Xbo::select("Staff", "personid");
		Xbo::select("Full Name", "employees.fullname");
		Xbo::select("Title", "employees.title");
		Xbo::select("Department", "employees.department");
		Xbo::select("Phone", "employees.primaryphone");
		XboSet::getXboSet("empqual", $getOwner);
		XboSet::displayData(["empqual.personid = employees.personid"]);
	}
}

QualificationChildren::init();

?>