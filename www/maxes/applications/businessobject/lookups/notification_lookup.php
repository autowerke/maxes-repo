<?php 
include ("../eat.php");

class NotificationLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$appname = Super::getParam("appname");
		if(!empty($appname)) $where = " appname = '".$appname."'";
		else $where = null;
		
				
		Xbo::select("Notification", "notifcode");
		Xbo::select("Description", "description");
		Xbo::select("Application", "appname");
		Xbo::select("Title", "subject");
		Xbo::select("Message", "messages");
		XboSet::getXboSet("notification", $where);
		XboSet::displayData();
	}
	public static function init(){
		NotificationLookup::displayData();
	}
	
}
NotificationLookup::init();
?>