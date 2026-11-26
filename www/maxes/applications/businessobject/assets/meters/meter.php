<?php 
require_once ("../../eat.php");

class Meter extends Xbo {
	
	protected $meternum;
		
	public function __construct(){
		$this->meternum = Xbo::getString("meternum");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("Meter", "meternum");
		Xbo::select("Description", "description");
		Xbo::select("Meter Type", "metertype");
	}
	public static function displayData(){
		Meter::selectData();
		XboSet::getXboSet("meter");
		renderData();
	}
	public static function init(){
		$get = new Meter();		
		$meternum = $get->meternum;
		if(isset($meternum)) {
			XboSet::getXboSet("meter", "meternum = '$meternum'");
			$result = Xboset::validate();
			if ($result->num_rows){
				$exec = Meter::changes();
				}else{
				$exec = Meter::add();
			}
		}
		
		Meter::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("meter");
		XboSet::add();
	}

	public static function changes(){
		$get = new Meter();		
		$meternum =	$get->meternum;		
		XboSet::getXboSet("meter", "meternum='$meternum'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

Meter::init();




?>