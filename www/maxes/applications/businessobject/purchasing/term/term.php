<?php 
require_once ("../../eat.php");

class Term extends Xbo {
	
	protected $orgcode;
	public $termcode;
	public function __construct(){
		$this->termcode = Xbo::getString("termcode");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("Kode", "termcode");
		Xbo::select("Description", "description");
		Xbo::select("Company", "orgcode");
	}
	public static function displayData(){
		Term::selectData();
		$whereclause = Super::IDWhereClause("termid");
		XboSet::getXboSet("term",$whereclause);
		XboSet::displayData();	
	}
	public static function init(){
		$get = new Term();		
		$termcode = $get->termcode;
		if(isset($termcode)) {
			XboSet::getXboSet("term", "termcode = '$termcode'");
			$result = Xboset::validate();
			
			if ($result->num_rows){
				$exec = Term::changes();
				}else{
				$exec = Term::add();
				
			}
		}
		
		Term::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("term");
		XboSet::add();
	}

	public static function changes(){
		$get = new Term();		
		$termcode =	$get->termcode;		
		XboSet::getXboSet("term", "termcode='$termcode'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

Term::init();




?>