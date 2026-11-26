<?php 
require_once ("../../eat.php");

class PettyCash extends Xbo {
	
	
	protected $pettycashnum;
		
	public function __construct(){
		$this->pettycashnum = Xbo::getString("pettycashnum");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("No", "pettycashnum");
		Xbo::select("Description", "description");
		Xbo::select("Financial Period", "financialperiod");
		Xbo::select("Opening Balance", "openbalance", "decimal");
		Xbo::select("Total Expense", "totalcost", "decimal");
		Xbo::select("Closing Balance", "closebalance", "decimal");
		Xbo::select("Status", "status");
		Xbo::select("Company", "orgcode");
		Xbo::select("Site", "sitecode");
	}
	public static function displayData(){
		$defaultjoin = null;
		if(isset($_GET['mxsdvsearch'])){
			$a = new Super();
			$b = $a->getWhere($_GET['mxsdvsearch'], "pettycash");
			$jointable = $b->join;
			$where = $b->where;
			PettyCash::selectData();
			XboSet::getXboSet("pettycash", $where);
			renderData($defaultjoin, $jointable);
		}else{
			PettyCash::selectData();
			XboSet::getXboSet("pettycash");
			renderData($defaultjoin);	
		}	
	}
	public static function init(){
	
		$get = new PettyCash();		
		$pettycashnum = $get->pettycashnum;
		if(isset($pettycashnum)) {
			XboSet::getXboSet("pettycash", "pettycashnum = '$pettycashnum'");
			$result = Xboset::validate();
			if ($result->num_rows){
				$exec = PettyCash::changes();
				}else{
				$exec = PettyCash::add();
			}
		}
		
		PettyCash::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("pettycash");
		XboSet::add();
	}

	public static function changes(){
		$get = new PettyCash();		
		$pettycashnum = $get->pettycashnum;		
		XboSet::getXboSet("pettycash", "pettycashnum='$pettycashnum'");		
		XboSet::changes();
	}
	
}

PettyCash::init();




?>