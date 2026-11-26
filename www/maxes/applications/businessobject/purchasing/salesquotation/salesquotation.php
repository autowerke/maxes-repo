<?php 
require_once ("../../eat.php");

class SO extends Xbo {
	public $salesquotenum;
	public function __construct(){
		$this->salesquotenum = Xbo::getString("salesquotenum");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("SQ", "salesquotenum");
		Xbo::select("Description", "description");
		Xbo::select("Customer", "customer.name");			
		Xbo::select("Order Date", "orderdate", "date");		
		Xbo::select("Pretax Total", "pretaxtotal", "decimal");
		Xbo::select("Tax", "totaltax", "decimal");
		Xbo::select("Total  Price", "totalprice", "decimal");
		Xbo::select("WO", "wonum");
		Xbo::select("Status", "status");
	}
	public static function displayData(){
		$defaultjoin = ["salesquotation.customercode = customer.customercode"];
		if(isset($_GET['mxsdvsearch'])){
			$a = new Super();
			$b = $a->getWhere($_GET['mxsdvsearch'], "salesquotation");
			$jointable = $b->join;
			$where = $b->where;
			SO::selectData();			
			XboSet::getXboSet("salesquotation", $where);
			renderData($defaultjoin, $jointable);	
		}else{
			SO::selectData();
			$whereclause = Super::IDWhereClause("salesquotationid");
			XboSet::getXboSet("salesquotation", $whereclause);
			renderData($defaultjoin);	
		}	
	}
	public static function init(){
		$get = new SO();		
		$salesquotenum = $get->salesquotenum;
		if(isset($salesquotenum)) {
			XboSet::getXboSet("salesquotation", "salesquotenum = '$salesquotenum'");
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
		XboSet::getXboSet("salesquotation");
		XboSet::add();
	}

	public static function changes(){
		$get = new SO();		
		$salesquotenum =	$get->salesquotenum;	
		XboSet::getXboSet("salesquotation", "salesquotenum='$salesquotenum'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

SO::init();




?>