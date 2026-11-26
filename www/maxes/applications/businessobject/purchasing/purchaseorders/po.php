<?php 
require_once ("../../eat.php");

class PO extends Xbo {
	
	protected $orgcode;
	public $ponum;
	public function __construct(){
		$this->ponum = Xbo::getString("ponum");
	}
	public function __destruct()
    {
        return null;
    }
	
	public static function selectData(){
		Xbo::select("PO", "ponum");
		Xbo::select("Description", "description");
		Xbo::select("Order Date", "orderdate", "date");	
		Xbo::select("Contract", "contractnum");
		Xbo::select("Vendor", "vendor");	
		Xbo::select("Vendor Name", "vendor.name");			
		Xbo::select("Buyer", "buyer");	
		Xbo::select("Name", "employees.fullname");
		Xbo::select("Currency", "currencycode");
		Xbo::select("Total Cost", "totalcost", "decimal");
		Xbo::select("Total Base Cost ", "totalbasecost", "decimal");
		Xbo::select("Paid Cost", "paidcost", "decimal");
		Xbo::select("Unpaid Cost", "unpaidcost", "decimal");
		Xbo::select("Status", "status");
		Xbo::select("Receipt Status", "receipt");
	}
	public static function displayCols(){
		$defaultjoin = ["po.vendor = vendor.vendorcode", "employees.personid = po.buyer"];
		if(isset($_GET['mxsdvsearch'])){
			$a = new Super();
			$b = $a->getWhere($_GET['mxsdvsearch'], "po");
			$jointable = $b->join;
			$where = $b->where;
			PO :: selectData();
			XboSet::getXboSet("po", $where);
			renderData($defaultjoin, $jointable);
		}else{
			PO :: selectData();
			$whereclause = Super::IDWhereClause("poid", " status not in ('closed', 'cancel')");
			XboSet::getXboSet("po", $whereclause);
			renderData($defaultjoin);
		}
	}
	
	public static function init(){
		$get = new PO();		
		$ponum = $get->ponum;
		if(isset($ponum)) {
			XboSet::getXboSet("po", "ponum = '$ponum'");
			$result = Xboset::validate();
			if ($result->num_rows){
				$exec = PO::changes();
				}else{
				$exec = PO::add();
				
			}
		}
		
		PO::displayCols();
	}
	
	
	public static function add(){
		XboSet::getXboSet("PO");
		XboSet::add();
	}

	public static function changes(){
		$get = new PO();		
		$ponum =	$get->ponum;		
		XboSet::getXboSet("po", "ponum='$ponum'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

PO::init();




?>