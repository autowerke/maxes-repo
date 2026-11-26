<?php 
require_once ("../../eat.php");

class BulletinBoard extends Xbo {
	
	protected $bboardcode;
		
	public function __construct(){
		$this->bboardcode = Xbo::getString("bboardcode");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("ID", "bboardcode");
		Xbo::select("Title", "subject");
		Xbo::select("Posted Date", "enterdate", "date");
		Xbo::select("Expired Date", "expireddate", "date");
	}
	public static function displayData(){
		BulletinBoard::selectData();
		XboSet::getXboSet("bboard");
		renderData();	
	}
	public static function init(){
		$get = new BulletinBoard();		
		$bboardcode = $get->bboardcode;
		if(isset($bboardcode)) {
			XboSet::getXboSet("bboard", "bboardcode = '$bboardcode'");
			$result = Xboset::validate();
			if ($result->num_rows){
				$exec = BulletinBoard::changes();
				}else{
				$exec = BulletinBoard::add();
			}
		}
		
		BulletinBoard::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("bboard");
		XboSet::add();
	}

	public static function changes(){
		$get = new BulletinBoard();		
		$bboardcode =	$get->bboardcode;		
		XboSet::getXboSet("bboard", "bboardcode='$bboardcode'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

BulletinBoard::init();




?>