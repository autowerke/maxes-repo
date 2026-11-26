<?php 
require_once ("../../eat.php");

class EmailSubs extends Xbo {
	
	protected $emailsubs;
		
	public function __construct(){
		$this->emailsubs = Xbo::getString("emailsubs");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("Email", "emailsubs");
	}
	public static function displayData(){
		EmailSubs::selectData();
		XboSet::getXboSet("emailsubs");
		renderData();
	}
	public static function init(){
		$get = new EmailSubs();		
		$emailsubs = $get->emailsubs;
		if(isset($emailsubs)) {
			XboSet::getXboSet("emailsubs", "emailsubs = '$emailsubs'");
			$result = Xboset::validate();
			if ($result->num_rows){
				$exec = EmailSubs::changes();
				}else{
				$exec = EmailSubs::add();
			}
		}
		
		EmailSubs::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("emailsubs");
		XboSet::add();
	}

	public static function changes(){
		$get = new EmailSubs();		
		$emailsubs = $get->emailsubs;		
		XboSet::getXboSet("emailsubs", "emailsubs='$emailsubs'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

EmailSubs::init();




?>