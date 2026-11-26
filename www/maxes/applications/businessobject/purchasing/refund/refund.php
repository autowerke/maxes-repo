<?php 
require_once ("../../eat.php");

class Refund extends Xbo {
	
	
	protected $refundnum;
		
	public function __construct(){
		$this->refundnum = Xbo::getString("refundnum");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("Refund. No", "refundnum");
		Xbo::select("Description", "description");
		Xbo::select("PO", "ponum");
		Xbo::select("SO", "sonum");
		Xbo::select("Bill#", "billingnum");
		Xbo::select("Trans#", "transnum");
		Xbo::select("Refunded By", "refundby");
		Xbo::select("Refund Date", "actualdate", "date");
		Xbo::select("Total", "total", "decimal");
		Xbo::select("Sub Total", "totalrefund", "decimal");
		
	}
	public static function displayData(){
		$defaultjoin = null;
		if(isset($_GET['mxsdvsearch'])){
			$a = new Super();
			$b = $a->getWhere($_GET['mxsdvsearch'], "refund");
			$jointable = $b->join;
			$where = $b->where;
			Refund::selectData();
			XboSet::getXboSet("refund", $where);
			renderData($defaultjoin, $jointable);
		}else{
			Refund::selectData();
			XboSet::getXboSet("refund");
			renderData($defaultjoin);	
		}	
	}
	public static function init(){
	
		$get = new Refund();		
		$refundnum = $get->refundnum;
		if(isset($refundnum)) {
			XboSet::getXboSet("refund", "refundnum = '$refundnum'");
			$result = Xboset::validate();
			if ($result->num_rows){
				$exec = Refund::changes();
				}else{
				$exec = Refund::add();
			}
		}
		
		Refund::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("refund");
		XboSet::add();
	}

	public static function changes(){
		$get = new Refund();		
		$refundnum = $get->refundnum;		
		XboSet::getXboSet("refund", "refundnum='$refundnum'");		
		XboSet::changes();
	}
	
}

Refund::init();




?>