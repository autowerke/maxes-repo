<?php 
require_once ("../../eat.php");

class Customer extends Xbo {
	public $customercode;
	public function __construct(){
		$this->customercode = Xbo::getString("customercode");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("Customer ID", "customercode");
		Xbo::select("Name", "name");
		Xbo::select("Reseller", "reseller");
		Xbo::select("Distributor", "distributor");
		Xbo::select("Status", "status");
		Xbo::select("Bank", "bankname1");
		Xbo::select("Bank Account", "bankaccount1");
		Xbo::select("Credit", "balance", "decimal");
	}
	public static function displayData(){
		if(isset($_GET['mxsdvsearch'])){
			$a = new Super();
			$b = $a->getWhere($_GET['mxsdvsearch'], "customer");
			$jointable = $b->join;
			$where = $b->where;
			Customer::selectData();
			XboSet::getXboSet("customer", $where);
			renderData(null, $jointable);
		}else{
			Customer::selectData();
			XboSet::getXboSet("customer");
			renderData();	
		}	
		
	}
	public static function init(){
		$get = new Customer();		
		$customercode = $get->customercode;
		if(isset($customercode)) {
		XboSet::getXboSet("customer", "customercode = '$customercode'");
			$result = Xboset::validate();
			if ($result->num_rows){
				$exec = Customer::changes();
				}else{
				$exec = Customer::add();
			}
		}
		Customer::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("customer");
		XboSet::add();
	}

	public static function changes(){
		$get = new Customer();		
		$customercode =	$get->customercode;		
		XboSet::getXboSet("customer", "customercode='$customercode'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

Customer::init();




?>