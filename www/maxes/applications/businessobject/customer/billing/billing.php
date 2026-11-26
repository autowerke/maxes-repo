<?php 
require_once ("../../eat.php");

class Billing extends Xbo {
	
	
	protected $billingnum;
		
	public function __construct(){
		$this->billingnum = Xbo::getString("billingnum");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("Bill", "billingnum");
		Xbo::select("Customer Code", "customercode");
		Xbo::select("Customer Name", "customer.name");
		Xbo::select("Description", "description");
		Xbo::select("Staff", "personid");
		Xbo::select("Full Name", "employees.fullname");
		Xbo::select("Entered Date", "enterdate", "date");
		Xbo::select("Status", "status");
		Xbo::select("Sub Total", "subtotalpricediscount", "decimal");
		Xbo::select("Tax", "totaltax", "decimal");
		Xbo::select("Total", "totalprice", "decimal");
		Xbo::select("Paid", "paidprice", "decimal");
		Xbo::select("Unpaid", "unpaidprice", "decimal");
	}
	public static function displayData(){
		$defaultjoin = ["customer.customercode = billing.customercode", "billing.personid = employees.personid"];
		if(isset($_GET['mxsdvsearch'])){
			$a = new Super();
			$b = $a->getWhere($_GET['mxsdvsearch'], "Billing");
			$jointable = $b->join;
			$where = $b->where;
			Billing::selectData();
			XboSet::getXboSet("billing", $where);
			renderData($defaultjoin, $jointable);
		}else{
			Billing::selectData();
			XboSet::getXboSet("billing");
			renderData($defaultjoin);	
		}	
	}
	public static function init(){
	
		$get = new Billing();		
		$billingnum = $get->billingnum;
		if(isset($billingnum)) {
			XboSet::getXboSet("billing", "billingnum = '$billingnum'");
			$result = Xboset::validate();
			if ($result->num_rows){
				$exec = Billing::changes();
				}else{
				$exec = Billing::add();
			}
		}
		
		Billing::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("Billing");
		XboSet::add();
	}

	public static function changes(){
		$get = new Billing();		
		$billingnum = $get->billingnum;		
		XboSet::getXboSet("billing", "billingnum='$billingnum'");		
		XboSet::changes();
	}
	
}

Billing::init();




?>