<?php 
require_once ("../../eat.php");

class PriceGroup extends Xbo {
	
	public $pricecode;	
	public function __construct(){
		$this->pricecode = Xbo::getString("pricecode");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("Price Code", "pricecode");
		Xbo::select("Description", "description");
	}
	
	
	
	
	public static function displayData(){
		PriceGroup::selectData();
		$whereclause = Super::IDWhereClause("pricegroupid");
		XboSet::getXboSet("pricegroup", $whereclause);
		renderData();	
	}
	public static function init(){
		$get = new PriceGroup();		
		$pricecode = $get->pricecode;		
		if(isset($pricecode)) {
			XboSet::getXboSet("pricegroup", "pricecode = '$pricecode'");
			$result = Xboset::validate();	
			if ($result->num_rows){			
				$exec = PriceGroup::changes();
			}else{				
				$exec = PriceGroup::add();				
			}
		}
		
		PriceGroup::displayData();
	}	
	
	public static function add(){
		XboSet::getXboSet("pricegroup");
		XboSet::add();
	}

	public static function changes(){
		$get = new PriceGroup();		
		$pricecode = $get->pricecode;		
		XboSet::getXboSet("pricegroup", "pricecode='$pricecode'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

PriceGroup::init();




?>