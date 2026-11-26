<?php 
include ("../eat.php");

class ViewHistory extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("User Name", "username");
		Xbo::select("Login Time", "logintime", "date");
		Xbo::select("IP Address", "ipaddress");
		XboSet::getXboSet("mxssession");
		XboSet::displayData();
	}
	public static function init(){
		ViewHistory::displayData();
	}
	
}
ViewHistory::init();
?>