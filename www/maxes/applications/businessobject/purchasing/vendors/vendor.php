<?php 
require_once ("../../eat.php");

class Vendor extends Xbo {
	
	protected $orgcode;
	public $vendorcode;
	public function __construct(){
		$this->vendorcode = Xbo::getString("vendorcode");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("Vendor", "vendorcode");
		Xbo::select("Currency", "currencycode");
		Xbo::select("Name", "name");
		Xbo::select("Address", "address");
		Xbo::select("Province", "stateprovince");
		Xbo::select("Country", "country");
		Xbo::select("Keyword", "keyword");
		Xbo::select("Manufacturer", "manufacturer");
		Xbo::select("Auth. Agent", "authorizedagent");
		Xbo::select("Gen. Trader", "generaltrading");
		Xbo::select("Lokal", "localcommunity");
		Xbo::select("Unpaid Price", "balance", "decimal");
	}
	public static function displayData(){
		if(isset($_GET['mxsdvsearch'])){
			$a = new Super();
			$b = $a->getWhere($_GET['mxsdvsearch'], "vendor");
			$jointable = $b->join;
			$where = $b->where;
			Vendor::selectData();
			XboSet::getXboSet("vendor", $where);
			renderData(null, $jointable);
			
		}else{
			Vendor::selectData();
			$whereclause = Super::IDWhereClause("vendorid");
			XboSet::getXboSet("vendor",$whereclause);
			renderData();	
		}	
		
	}
	public static function init(){
		$get = new Vendor();		
		$vendorcode = $get->vendorcode;
		if(isset($vendorcode)) {
			XboSet::getXboSet("vendor", "vendorcode = '$vendorcode'");
			$result = Xboset::validate();
		
			if ($result->num_rows){
				$exec = Vendor::changes();
				}else{
				$exec = Vendor::add();
			}
		}
		
		Vendor::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("vendor");
		XboSet::add();
	}

	public static function changes(){
		$get = new Vendor();		
		$vendorcode =	$get->vendorcode;		
		XboSet::getXboSet("vendor", "vendorcode='$vendorcode'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

Vendor::init();




?>