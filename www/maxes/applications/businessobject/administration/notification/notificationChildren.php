<?php 
require_once ("../../eat.php");


class NotificationChildren {

	public static function init(){
		XboSet::validateChildren();	
		NotificationChildren::displayData();
	}
	public static function displayData(){
		$getOwner = XboSet::getOwner();
		$tableid = Xbo::getTableID();
		if($tableid=="mxtable_notificationline_child"){
			Xbo::select("Line Num.", "notiflinenum");
			Xbo::select("Staff", "personid");
			Xbo::select("Full Name", "employees.fullname");
			Xbo::select("Title", "employees.title");
			Xbo::select("Email", "employees.primaryemail");
			Xbo::select("Phone", "employees.primaryphone");
			Xbo::select("To", "nto");
			Xbo::select("CC", "ncc");
			XboSet::getXboSet("notificationline", $getOwner);
			XboSet::displayData("notificationline.personid = employees.personid");
		}
		if($tableid=="mxtable_emaillogs_child"){
			Xbo::select("Application", "ownertable");
			Xbo::select("Key", "ownercolvalue");
			Xbo::select("Title", "subject");
			Xbo::select("Entered Date", "enterdate", "date");
			Xbo::select("Status", "status");
			XboSet::getXboSet("emaillogs", $getOwner);
			XboSet::displayData();
		}
	}
}

NotificationChildren::init();

?>