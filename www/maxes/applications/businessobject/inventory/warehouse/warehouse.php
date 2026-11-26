<?php 

require_once ("../../eat.php");

class Warehouse extends Xbo {

	public $warehouse;	
	public function __construct(){
		$this->warehouse = Xbo::getString("warehouse");
	}
	public function __destruct()
    {
        return null;
    }
	public static function renderData(){
		Xbo::select("Gudang", "warehouse");
		Xbo::select("Deskripsi", "description");
		Xbo::select("Perusahaan", "orgcode");
		Xbo::select("Cabang", "sitecode");
	}
	public static function displayData(){
		Warehouse::renderData();
		$whereclause = Super::IDWhereClause("warehouseid");
		XboSet::getXboSet("warehouse", $whereclause);
		renderData();	
	}
	public static function init(){
		$get = new Warehouse();		
		$warehouse = $get->warehouse;
		if(isset($warehouse)) {
			
			XboSet::getXboSet("warehouse", "warehouse = '$warehouse'");
			$result = Xboset::validate();
			
			if ($result->num_rows){
				$exec = Warehouse::changes();
				}else{
				$exec = Warehouse::add();
				
			}
		}
		
		Warehouse::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("warehouse");
		XboSet::add();
	}

	public static function changes(){
		$get = new Warehouse();		
		$warehouse =	$get->warehouse;		
		XboSet::getXboSet("warehouse", "warehouse='$warehouse'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

Warehouse::init();




?>