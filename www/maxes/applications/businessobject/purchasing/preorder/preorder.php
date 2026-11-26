<?php 
require_once ("../../eat.php");

class Preorder extends Xbo {
	
	protected $orgcode;
		
	public function __construct(){
		$this->preordernum = Xbo::getString("preordernum");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("Kode", "preordernum");
		Xbo::select("Description", "description");
		Xbo::select("Company", "orgcode");
	}
	public static function displayData(){
		Preorder::selectData();
		$whereclause = Super::IDWhereClause("preorderid");
		XboSet::getXboSet("preorder",$whereclause);
		XboSet::displayData();	
	}
	public static function init(){
		$get = new Preorder();		
		$preordernum = $get->preordernum;
		if(isset($preordernum)) {
			XboSet::getXboSet("preorder", "preordernum = '$preordernum'");
			$result = Xboset::validate();
			
			if ($result->num_rows){
				$exec = Preorder::changes();
				}else{
				$exec = Preorder::add();
				
			}
		}
		
		Preorder::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("preorder");
		XboSet::add();
	}

	public static function changes(){
		$get = new Preorder();		
		$preordernum =	$get->preordernum;		
		XboSet::getXboSet("preorder", "preordernum='$preordernum'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

Preorder::init();




?>