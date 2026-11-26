<?php 
require_once ("../../eat.php");

class GeneralLedger extends Xbo {
	
	
	protected $ledgernum;
		
	public function __construct(){
		$this->ledgernum = Xbo::getString("ledgernum");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("No.", "ledgernum");
		Xbo::select("Description", "description");
		Xbo::select("Debit", "debitcost", "decimal");
		Xbo::select("Credit", "creditcost", "decimal");
		Xbo::select("Status", "status");
		Xbo::select("Company", "orgcode");
		Xbo::select("Site", "sitecode");
	}
	public static function displayData(){
		$defaultjoin = null;
		if(isset($_GET['mxsdvsearch'])){
			$a = new Super();
			$b = $a->getWhere($_GET['mxsdvsearch'], "ledger");
			$jointable = $b->join;
			$where = $b->where;
			GeneralLedger::selectData();
			XboSet::getXboSet("ledger", $where);
			renderData($defaultjoin, $jointable);
		}else{
			GeneralLedger::selectData();
			XboSet::getXboSet("ledger");
			renderData($defaultjoin);	
		}	
	}
	public static function init(){
	
		$get = new GeneralLedger();		
		$ledgernum = $get->ledgernum;
		if(isset($ledgernum)) {
			XboSet::getXboSet("ledger", "ledgernum = '$ledgernum'");
			$result = Xboset::validate();
			if ($result->num_rows){
				$exec = GeneralLedger::changes();
				}else{
				$exec = GeneralLedger::add();
			}
		}
		
		GeneralLedger::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("ledger");
		XboSet::add();
	}

	public static function changes(){
		$get = new GeneralLedger();		
		$ledgernum = $get->ledgernum;		
		XboSet::getXboSet("ledger", "ledgernum='$ledgernum'");		
		XboSet::changes();
	}
	
}

GeneralLedger::init();




?>