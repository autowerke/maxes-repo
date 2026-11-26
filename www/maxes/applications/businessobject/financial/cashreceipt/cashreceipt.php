<?php 
require_once ("../../eat.php");

class CashReceipt extends Xbo {
	
	
	protected $cashreceiptnum;
		
	public function __construct(){
		$this->cashreceiptnum = Xbo::getString("cashreceiptnum");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("Petty Cash Num.", "cashreceiptnum");
		Xbo::select("Description", "description");
		Xbo::select("Financial Period", "financialperiod");
		Xbo::select("Opening Balance", "openbalance", "decimal");
		Xbo::select("Total Cash Receipt", "totalcashreceipt", "decimal");
		Xbo::select("Additional Cost", "additionalcost", "decimal");
		Xbo::select("Total Expense", "totalexpense", "decimal");
		Xbo::select("Closing Balance", "closebalance", "decimal");
	
		Xbo::select("Status", "status");
	}
	public static function displayData(){
		$defaultjoin = null;
		if(isset($_GET['mxsdvsearch'])){
			$a = new Super();
			$b = $a->getWhere($_GET['mxsdvsearch'], "cashreceipt");
			$jointable = $b->join;
			$where = $b->where;
			CashReceipt::selectData();
			XboSet::getXboSet("cashreceipt", $where);
			renderData($defaultjoin, $jointable);
		}else{
			CashReceipt::selectData();
			XboSet::getXboSet("cashreceipt");
			renderData($defaultjoin);	
		}	
	}
	public static function init(){
	
		$get = new CashReceipt();		
		$cashreceiptnum = $get->cashreceiptnum;
		if(isset($cashreceiptnum)) {
			XboSet::getXboSet("cashreceipt", "cashreceiptnum = '$cashreceiptnum'");
			$result = Xboset::validate();
			if ($result->num_rows){
				$exec = CashReceipt::changes();
				}else{
				$exec = CashReceipt::add();
			}
		}
		
		CashReceipt::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("cashreceipt");
		XboSet::add();
	}

	public static function changes(){
		$get = new CashReceipt();		
		$cashreceiptnum = $get->cashreceiptnum;		
		XboSet::getXboSet("cashreceipt", "cashreceiptnum='$cashreceiptnum'");		
		XboSet::changes();
	}
	
}

CashReceipt::init();




?>