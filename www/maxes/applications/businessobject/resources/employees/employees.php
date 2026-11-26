<?php 
require_once ("../../eat.php");

class Employee extends Xbo {
	protected $personid;
	public function __construct(){
		$this->personid = Xbo::getString("personid");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("Staff", "personid");
		Xbo::select("Full Name", "fullname");
		Xbo::select("Title", "title");
		Xbo::select("Email", "primaryemail");
		Xbo::select("Kode Tidak Kena Pajak", "taxexemptcode");
		Xbo::select("Penghasilan Tidak Kena Pajak", "taxexemptcost", "decimal");
		Xbo::select("Income Tahunan Nett", "yearlynettincome", "decimal");
		Xbo::select("Pajak Tahunan", "yearlytaxcost", "decimal");
		Xbo::select("Pajak Bulanan", "monthlytaxcharge", "decimal");
		Xbo::select("Company", "orgcode");
		Xbo::select("Site", "sitecode");
	}
	public static function displayCols(){
		if(isset($_GET['mxsdvsearch'])){
			$a = new Super();
			$b = $a->getWhere($_GET['mxsdvsearch'], "employees");
			$jointable = $b->join;
			$where = $b->where;
			Employee::selectData();
			XboSet::getXboSet("employees", $where);			
			renderData(null, $jointable);	
		}else{
			Employee::selectData();
			$whereclause = Super::IDWhereClause("employeesid");
			XboSet::getXboSet("employees",$whereclause);
			renderData();	
		}	
		
	}
	public static function init(){
	
		$get = new Employee();		
		$personid = $get->personid;
		if(isset($personid)) {
			XboSet::getXboSet("employees", "personid = '$personid'");
			$result = Xboset::validate();
			if ($result->num_rows){
				$exec = Employee::changes();
				}else{
				$exec = Employee::add();
			}
		}
		
		Employee::displayCols();
	}
	
	
	public static function add(){
		XboSet::getXboSet("employees");
		XboSet::add();
	}

	public static function changes(){
		$get = new Employee();		
		$personid =	$get->personid;		
		XboSet::getXboSet("employees", "personid='$personid'");		
		XboSet::changes();
	}
	
}

Employee::init();




?>