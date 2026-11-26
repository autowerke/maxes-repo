<?php 
require_once ("../../eat.php");

class Currency extends Xbo {
	
	protected $currencycode;
		
	public function __construct(){
		$this->currencycode = Xbo::getString("currencycode");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("Currency", "currencycode");
		Xbo::select("Description", "description");
	}
	public static function displayData(){
		Currency::selectData();
		XboSet::getXboSet("currency");
		renderData();
	}
	public static function init(){
		$get = new Currency();		
		$currencycode = $get->currencycode;
		if(isset($currencycode)) {
			XboSet::getXboSet("currency", "currencycode = '$currencycode'");
			$result = Xboset::validate();
			if ($result->num_rows){
				$exec = Currency::changes();
				}else{
				$exec = Currency::add();
			}
		}
		
		Currency::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("currency");
		XboSet::add();
	}

	public static function changes(){
		$get = new Currency();		
		$currencycode =	$get->currencycode;		
		XboSet::getXboSet("currency", "currencycode='$currencycode'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

Currency::init();




?>