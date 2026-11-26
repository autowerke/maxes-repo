<?php 
require_once ("../../eat.php");

class ExchangeRate extends Xbo {
	
	protected $exchangerateid;
	private $currencycodefrom;
	private $currencycodeto;
	private $activedate;

	public function __construct(){
		$this->currencycodefrom = Xbo::getString("currencycodefrom");
		$this->currencycodeto = Xbo::getString("currencycodeto");
		$this->activedate = Xbo::getDate("activedate");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("Convert From", "currencycodefrom");
		Xbo::select("Convert To", "currencycodeto");
		Xbo::select("Active Date", "activedate", "date");
		Xbo::select("Expired Date", "expireddate", "date");
		Xbo::select("Rate", "exchangerate", "decimal");
	}
	public static function displayData(){
		ExchangeRate::selectData();
		XboSet::getXboSet("exchangerate");
		renderData();
	}
	public static function init(){
		$get = new ExchangeRate();		
		$ccfrom = $get->currencycodefrom;
		$ccto = $get->currencycodeto;
		$actdate = $get->activedate;
		
		if(isset($ccfrom) && isset($ccto) && isset($actdate)) {
			XboSet::getXboSet("exchangerate", "currencycodefrom = '$ccfrom' and currencycodeto = '$ccto' and activedate = '$actdate' ");
			$result = Xboset::validate();
				
			if ($result->num_rows){
				$exec = ExchangeRate::changes();
				}else{
					$exec = ExchangeRate::add();
			}
		}
		ExchangeRate::displayData();
	}
	
	public static function add(){
		XboSet::getXboSet("exchangerate");
		XboSet::add();
	}

	public static function changes(){
		$get = new ExchangeRate();		
		$ccfrom = $get->currencycodefrom;
		$ccto = $get->currencycodeto;
		$actdate = $get->activedate;
		XboSet::getXboSet("exchangerate", "currencycodefrom = '$ccfrom' and currencycodeto = '$ccto' and activedate = '$actdate' ");	
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

ExchangeRate::init();




?>