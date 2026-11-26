<?php 
require_once ("../../eat.php");

class Notifications extends Xbo {
	
	protected $notifcode;
		
	public function __construct(){
		$this->notifcode = Xbo::getString("notifcode");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("Notification Code", "notifcode");
		Xbo::select("Description", "description");
		Xbo::select("Application", "appname");
		Xbo::select("Company", "orgcode");
		Xbo::select("Site", "sitecode");
	}
	public static function displayData(){
		Notifications::selectData();
		XboSet::getXboSet("notification");
		renderData();
	}
	public static function init(){
		$get = new Notifications();		
		$notifcode = $get->notifcode;
		if(isset($notifcode)) {
			XboSet::getXboSet("notification", "notifcode = '$notifcode'");
			$result = Xboset::validate();
			if ($result->num_rows){
				$exec = Notifications::changes();
				}else{
				$exec = Notifications::add();
			}
		}
		
		Notifications::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("notification");
		XboSet::add();
	}

	public static function changes(){
		$get = new Notifications();		
		$notifcode =	$get->notifcode;		
		XboSet::getXboSet("notification", "notifcode='$notifcode'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

Notifications::init();




?>