<?php 
require_once ("../../eat.php");

class Home extends Xbo {
	
	public function __construct(){
		
	}
	public function __destruct()
    {
       return null;
    }
	
	public static function displayData(){
		$where = "username = '{Xbo::getLoginUser()}'";
		Xbo::select("Number", "objectnum");
		Xbo::select("Object", "object"); 
		Xbo::select("Process", "processname"); 
		Xbo::select("Message", "message");
		XboSet::getXboSet("mxsnotification"); 
		renderData();
		
	}
	public static function init(){
		Home::displayData();
	}
	
	

}

Home::init();



?>