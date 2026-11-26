<?php 
require_once ("../../eat.php");

class Hazard extends Xbo {
	
	protected $hazardnum;
		
	public function __construct(){
		$this->hazardnum = Xbo::getString("hazardnum");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("Kode Bahaya", "hazardnum");
		Xbo::select("Description", "description");
	}
	public static function displayData(){
		Hazard::selectData();
		XboSet::getXboSet("hazard");
		renderData();
	}
	public static function init(){
		$get = new Hazard();		
		$hazardnum = $get->hazardnum;
		if(isset($hazardnum)) {
			XboSet::getXboSet("hazard", "hazardnum = '$hazardnum'");
			$result = Xboset::validate();
			if ($result->num_rows){
				$exec = Hazard::changes();
				}else{
				$exec = Hazard::add();
			}
		}
		
		Hazard::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("hazard");
		XboSet::add();
	}

	public static function changes(){
		$get = new Hazard();		
		$hazardnum =	$get->hazardnum;		
		XboSet::getXboSet("hazard", "hazardnum='$hazardnum'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

Hazard::init();




?>