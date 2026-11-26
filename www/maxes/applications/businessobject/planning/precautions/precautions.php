<?php 
require_once ("../../eat.php");

class Precautions extends Xbo {
	
	protected $precaution;
	private $tablename = "precautions";
	public function __construct(){
		$this->precaution = Xbo::getString("precaution");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("Precaution ID", "precaution");
		Xbo::select("Description", "description");
	}
	public static function displayData(){
		$get = new Precautions();	
		Precautions::selectData();
		XboSet::getXboSet($get->tablename);
		renderData();
	}
	public static function init(){
		$get = new Precautions();		
		$precaution = $get->precaution;
		if(isset($precaution)) {
			XboSet::getXboSet($get->tablename, "precaution = '$precaution'");
			$result = Xboset::validate();
			if ($result->num_rows){
				$exec = Precautions::changes();
				}else{
				$exec = Precautions::add();
			}
		}
		
		Precautions::displayData();
	}
	
	
	public static function add(){
		$get = new Precautions();
		XboSet::getXboSet($get->tablename);
		XboSet::add();
	}

	public static function changes(){
		$get = new Precautions();		
		$precaution =	$get->precaution;		
		XboSet::getXboSet($get->tablename, "precaution='$precaution'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

Precautions::init();




?>