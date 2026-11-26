<?php 
require_once ("../../eat.php");

class Workorder extends Xbo {
	public $wonum;
	public function __construct(){
		$this->wonum = Xbo::getString("wonum");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){

		Xbo::select("Work Order", "wonum");
		Xbo::select("Description", "description");
		Xbo::select("Status", "status");
		Xbo::select("Priority", "priority");
		Xbo::select("Asset", "assetnum");
		Xbo::select("Budget", "budgetnum");
		Xbo::select("Rpt. Date", "reportdate", "date");
		Xbo::select("SO", "sonum");
		Xbo::select("Parent", "parent");
		Xbo::select("Targ. Start Date", "targstartdate", "date");
		Xbo::select("Targ. Compl. Date", "targcompdate", "date");
		Xbo::select("Plan. Cost", "plannedcost", "decimal");
		Xbo::select("Act. Cost", "actualcost", "decimal");
		
	}
	public static function displayData(){
		if(isset($_GET['mxsdvsearch'])){
			$a = new Super();
			$b = $a->getWhere($_GET['mxsdvsearch'], "workorder");
			$jointable = $b->join;
			$where = $b->where;
			Workorder::selectData();
			XboSet::getXboSet("workorder", $where);
			renderData(null, $jointable);
		}else{
			Workorder::selectData();
			$whereclause = Super::IDWhereClause("workorderid", " status not in ('closed', 'cancel')");
			XboSet::getXboSet("workorder", $whereclause );
			renderData();	
		}	
		
	}
	public static function init(){
		$get = new Workorder();
		$wonum = $get->wonum;
		
		if(isset($wonum)) {
			XboSet::getXboSet("workorder", "wonum = '$wonum'");
			$result = Xboset::validate();
			if ($result->num_rows){
				$exec = Workorder::changes();
				}else{
				$exec = Workorder::add();
			}
		}
		
		Workorder::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("workorder");
		XboSet::add();
	}

	public static function changes(){
		$get = new Workorder();		
		$wonum = $get->wonum;		
		XboSet::getXboSet("workorder", "wonum='$wonum'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

Workorder::init();




?>