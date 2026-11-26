<?php 
require_once ("../../eat.php");

class Classfications extends Xbo {
	
	protected $classificationcode;
		
	public function __construct(){
		$this->classificationcode = Xbo::getString("classificationcode");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("Kode Spek", "classificationcode");
		Xbo::select("Description", "description");
	}
	public static function displayData(){
		Classfications::selectData();
		XboSet::getXboSet("classification");
		renderData();
	}
	public static function init(){
		$get = new Classfications();		
		$classificationcode = $get->classificationcode;
		if(isset($classificationcode)) {
			XboSet::getXboSet("classification", "classificationcode = '$classificationcode'");
			$result = Xboset::validate();
			if ($result->num_rows){
				$exec = Classfications::changes();
				}else{
				$exec = Classfications::add();
			}
		}
		
		Classfications::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("classification");
		XboSet::add();
	}

	public static function changes(){
		$get = new Classfications();		
		$classificationcode =	$get->classificationcode;		
		XboSet::getXboSet("classification", "classificationcode='$classificationcode'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

Classfications::init();




?>