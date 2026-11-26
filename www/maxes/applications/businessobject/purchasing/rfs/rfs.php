<?php 
require_once ("../../eat.php");

class RFS extends Xbo {
	
	protected $orgcode;
	public $rfsnum;
	public function __construct(){
		$this->rfsnum = Xbo::getString("rfsnum");
	}
	public function __destruct()
    {
        return null;
    }
	
	public static function selectData(){
		Xbo::select("RFS", "rfsnum");
		Xbo::select("Description", "description");
		Xbo::select("Status", "status");
		Xbo::select("User", "personid");	
		Xbo::select("PO", "ponum");	
		Xbo::select("Est. Date Arrival", "estdatearrival");	
		
	}
	public static function displayCols(){
		$defaultjoin = null;
		if(isset($_GET['mxsdvsearch'])){
			$a = new Super();
			$b = $a->getWhere($_GET['mxsdvsearch'], "rfs");
			$jointable = $b->join;
			$where = $b->where;
			RFS :: selectData();
			XboSet::getXboSet("rfs", $where);
			renderData($defaultjoin, $jointable);
		}else{
			RFS :: selectData();
			$whereclause = Super::IDWhereClause("rfsid");
			XboSet::getXboSet("rfs", $whereclause);
			renderData($defaultjoin);
		}
	}
	
	public static function init(){
		$get = new RFS();		
		$rfsnum = $get->rfsnum;
		if(isset($rfsnum)) {
			XboSet::getXboSet("rfs", "rfsnum = '$rfsnum'");
			$result = Xboset::validate();
			if ($result->num_rows){
				$exec = RFS::changes();
				}else{
				$exec = RFS::add();
				
			}
		}
		
		RFS::displayCols();
	}
	
	
	public static function add(){
		XboSet::getXboSet("RFS");
		XboSet::add();
	}

	public static function changes(){
		$get = new RFS();		
		$rfsnum =	$get->rfsnum;		
		XboSet::getXboSet("rfs", "rfsnum='$rfsnum'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

RFS::init();




?>