<?php 
require_once ("../../eat.php");

class pr extends Xbo {
	
	protected $orgcode;
	public $prnum;	
	public function __construct(){
		$this->prnum = Xbo::getString("prnum");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("PR", "prnum");
		Xbo::select("Description", "description");
		Xbo::select("Requested Date", "requesteddate", "date");	
		Xbo::select("Vendor Name", "vendor.name");			
		Xbo::select("Requested By", "requestedby");	
		Xbo::select("WO. Ref.", "wonum");
		Xbo::select("Name", "employees.fullname");
		Xbo::select("Currency", "currencycode");
		Xbo::select("Line Cost", "totalcost", "decimal");
		Xbo::select("Line Cost Basis", "totalbasecost", "decimal");
		Xbo::select("Status", "status");
		
	}
	public static function displayData(){
		$defaultjoin = ["pr.vendor = vendor.vendorcode", "employees.personid = pr.requestedby"];
		if(isset($_GET['mxsdvsearch'])){
			$a = new Super();
			$b = $a->getWhere($_GET['mxsdvsearch'], "pr");
			$jointable = $b->join;
			$where = $b->where;			
			pr::selectData();
			XboSet::getXboSet("pr", $where);
			renderData($defaultjoin, $jointable);	
		}else{
			pr::selectData();
			$whereclause = Super::IDWhereClause("prid", " status not in ('closed', 'cancel')");
			XboSet::getXboSet("pr", $whereclause);
			renderData($defaultjoin);
		}
	}
	public static function init(){
		$get = new PR();		
		$prnum = $get->prnum;
		if(isset($prnum)) {
			XboSet::getXboSet("pr", "prnum = '$prnum'");
			$result = Xboset::validate();
			if ($result->num_rows){
				
				$exec = PR::changes();
				}else{
				$exec = PR::add();
				
			}
		}
		
		PR::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("PR");
		XboSet::add();
	}

	public static function changes(){
		$get = new PR();		
		$prnum = $get->prnum;		
		XboSet::getXboSet("pr", "prnum='$prnum'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

PR::init();




?>