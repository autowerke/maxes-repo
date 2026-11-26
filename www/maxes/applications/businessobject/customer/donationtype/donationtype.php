<?php 
require_once ("../../eat.php");

class DonationType extends Xbo {
	
	protected $donationtype;
		
	public function __construct(){
		$this->donationtype = Xbo::getString("donationtype");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("Donation Type", "donationtype");
		Xbo::select("Description", "description");
	}
	public static function displayData(){
		DonationType::selectData();
		XboSet::getXboSet("donationtype");
		renderData();
	}
	public static function init(){
		$get = new DonationType();		
		$donationtype = $get->donationtype;
		if(isset($donationtype)) {
			XboSet::getXboSet("donationtype", "donationtype = '$donationtype'");
			$result = Xboset::validate();
			if ($result->num_rows){
				$exec = DonationType::changes();
				}else{
				$exec = DonationType::add();
			}
		}
		
		DonationType::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("donationtype");
		XboSet::add();
	}

	public static function changes(){
		$get = new DonationType();		
		$donationtype = $get->donationtype;		
		XboSet::getXboSet("donationtype", "donationtype='$donationtype'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

DonationType::init();




?>