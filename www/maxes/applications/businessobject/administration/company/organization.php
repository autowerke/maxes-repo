<?php 
require_once ("../../eat.php");

class Organization extends Xbo {
	
	protected $orgcode;
		
	public function __construct(){
		$this->orgcode = Xbo::getString("orgcode");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("Organization Code", "orgcode");
		Xbo::select("Name", "description");
		Xbo::select("Base Currency", "basecurrency");
		Xbo::select("Capital Account", "capitalacct");
		Xbo::select("Cash on Bank Account", "cashonbankacct");
		Xbo::select("Cash on Hand Account", "cashonhandacct");
		
	}
	public static function displayData(){
		Organization::selectData();
		XboSet::getXboSet("organization");
		renderData();	
	}
	public static function init(){
		$get = new Organization();		
		$orgcode = $get->orgcode;
		if(isset($orgcode)) {
			XboSet::getXboSet("organization", "orgcode = '$orgcode'");
			$result = Xboset::validate();
			if ($result->num_rows){
				$exec = Organization::changes();
				}else{
				$exec = Organization::add();
			}
		}
		
		Organization::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("organization");
		XboSet::add();
	}

	public static function changes(){
		$get = new Organization();		
		$orgcode =	$get->orgcode;		
		XboSet::getXboSet("organization", "orgcode='$orgcode'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

Organization::init();




?>