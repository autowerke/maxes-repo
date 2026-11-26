<?php 
require_once ("../../eat.php");

class SO extends Xbo {
	public $sonum;
	public function __construct(){
		$this->sonum = Xbo::getString("sonum");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("SO#", "sonum");
		Xbo::select("Description", "description");
		Xbo::select("Customer", "customer.name");	
		Xbo::select("Pre-Assignment#", "preordernum");
		Xbo::select("SQ#", "salesquotenum");
		Xbo::select("Order Date", "orderdate", "date");	
		Xbo::select("Total Price", "pretaxtotal", "decimal");
		Xbo::select("Discount", "discountprice", "decimal");
		Xbo::select("Grand Total", "discountedtotalprice", "decimal");
		Xbo::select("Paid", "paidprice", "decimal");
		Xbo::select("Unpaid", "unpaidprice", "decimal");
		Xbo::select("Status", "status");
	}
	public static function displayData(){
		$defaultjoin = ["so.customercode = customer.customercode"];
		if(isset($_GET['mxsdvsearch'])){			
			$a = new Super();
			$b = $a->getWhere($_GET['mxsdvsearch'], "so");
			$jointable = $b->join;
			$where = $b->where;
			SO::selectData();			
			XboSet::getXboSet("so", $where);
			renderData($defaultjoin, $jointable);	
		}else{
			SO::selectData();
			$whereclause = Super::IDWhereClause("soid", "so.status not in ('closed', 'cancel')");
			XboSet::getXboSet("so", $whereclause);
			renderData($defaultjoin);	
		}	
	}
	public static function init(){
		$get = new SO();		
		$sonum = $get->sonum;
		if(isset($sonum)) {
			XboSet::getXboSet("so", "sonum = '$sonum'");
			$result = Xboset::validate();
			if ($result->num_rows){
				$exec = SO::changes();
				}else{
				$exec = SO::add();
			}
		}
		SO::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("so");
		XboSet::add();
	}

	public static function changes(){
		$get = new SO();		
		$sonum =	$get->sonum;	
		
		XboSet::getXboSet("so", "sonum='$sonum'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

SO::init();




?>