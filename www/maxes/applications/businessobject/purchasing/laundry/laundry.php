<?php 
require_once ("../../eat.php");

class Laundry extends Xbo {
	
	public function __construct(){
		$this->laundrynum = Xbo::getString("laundrynum");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("No.", "laundrynum");
		Xbo::select("Name", "customercode");
		Xbo::select("Phone", "phone");		
		Xbo::select("Tgl. Order", "orderdate", "date");				
		Xbo::select("Start Date", "startdate", "date");		
		Xbo::select("Finish Date", "enddate", "date");
		Xbo::select("Total KG", "totalkg", "decimal");
		Xbo::select("Jarak", "distance", "decimal");
		Xbo::select("Biaya Ongkir", "distanceprice", "decimal");
		Xbo::select("Discount", "discountprice", "decimal");
		Xbo::select("Discount(%)", "discountpercentage", "decimal");
		Xbo::select("Total  Price", "totalprice", "decimal");
		Xbo::select("Address", "address");
		Xbo::select("Layanan", "service");
		Xbo::select("Status", "status");
		Xbo::select("Tgl. Status", "statusdate");
	}
	public static function displayData(){
		$defaultjoin = ["laundry.customercode = customer.customercode"];
		if(isset($_GET['mxsdvsearch'])){			
			$a = new Super();
			$b = $a->getWhere($_GET['mxsdvsearch'], "laundry");
			$jointable = $b->join;
			$where = $b->where;
			Laundry::selectData();			
			XboSet::getXboSet("laundry", $where);
			renderData($defaultjoin, $jointable);	
		}else{
			Laundry::selectData();
			//$whereclause = Super::IDWhereClause("laundryid", "laundry.status not in ('closed', 'cancel')");
			$whereclause = null;
			XboSet::getXboSet("laundry", $whereclause);
			renderData($defaultjoin);	
		}	
	}
	public static function init(){
		$get = new Laundry();		
		$laundrynum = $get->laundrynum;
		if(isset($laundrynum)) {
			XboSet::getXboSet("laundry", "laundrynum = '$laundrynum'");
			$result = Xboset::validate();
			if ($result->num_rows){
				$exec = Laundry::changes();
				}else{
				$exec = Laundry::add();
			}
		}
		Laundry::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("laundry");
		XboSet::add();
	}

	public static function changes(){
		$get = new Laundry();		
		$laundrynum =	$get->laundrynum;	
		XboSet::getXboSet("laundry", "laundrynum='$laundrynum'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

Laundry::init();




?>