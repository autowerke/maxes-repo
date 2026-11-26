<?php 
require_once ("../../eat.php");

class MyAllowance extends Xbo {
	
	
	protected $myallowancenum;
		
	public function __construct(){
		$this->myallowancenum = Xbo::getString("myallowancenum");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("My Allowance", "myallowancenum");
		Xbo::select("Description", "description");
		Xbo::select("Staff", "personid");
		Xbo::select("Full Name", "employees.fullname");
		Xbo::select("Financial Period", "financialperiod");
		Xbo::select("Entered Date", "enterdate");
		Xbo::select("Status", "status");
		Xbo::select("Line Cost", "totalcost", "decimal");
	}
	public static function displayData(){
		$defaultjoin = ["myallowance.personid = employees.personid"];
		if(isset($_GET['mxsdvsearch'])){
			$a = new Super();
			$b = $a->getWhere($_GET['mxsdvsearch'], "myallowance");
			$jointable = $b->join;
			$where = $b->where;
			MyAllowance::selectData();
			XboSet::getXboSet("myallowance", $where);
			renderData($defaultjoin, $jointable);
		}else{
			MyAllowance::selectData();
			$whereclause = Super::IDWhereClause("myallowanceid");
			XboSet::getXboSet("myallowance",$whereclause);
			renderData($defaultjoin);	
		}	
	}
	public static function init(){
	
		$get = new MyAllowance();		
		$myallowancenum = $get->myallowancenum;
		if(isset($myallowancenum)) {
			XboSet::getXboSet("myallowance", "myallowancenum = '$myallowancenum'");
			$result = Xboset::validate();
			if ($result->num_rows){
				$exec = MyAllowance::changes();
				}else{
				$exec = MyAllowance::add();
			}
		}
		
		MyAllowance::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("myallowance");
		XboSet::add();
	}

	public static function changes(){
		$get = new MyAllowance();		
		$myallowancenum = $get->myallowancenum;		
		XboSet::getXboSet("myallowance", "myallowancenum='$myallowancenum'");		
		XboSet::changes();
	}
	
}

MyAllowance::init();




?>