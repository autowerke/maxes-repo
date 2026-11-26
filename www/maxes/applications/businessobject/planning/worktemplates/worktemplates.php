<?php 
require_once ("../../eat.php");

class WorkTemplates extends Xbo {
	public $wtnum;
	public function __construct(){
		$this->wtnum = Xbo::getString("wtnum");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("Work Order Templates", "wtnum");
		Xbo::select("Description", "description");
		Xbo::select("Asset", "assetnum");
		Xbo::select("Location", "location");
		Xbo::select("Priority", "priority");
		Xbo::select("Status", "status");
	}
	public static function displayColumns(){
		if(isset($_GET['mxsdvsearch'])){
			$a = new Super();
			$b = $a->getWhere($_GET['mxsdvsearch'], "worktemplates");
			$jointable = $b->join;
			$where = $b->where;
			WorkTemplates::selectData();
			XboSet::getXboSet("worktemplates", $where);
			renderData(null, $jointable);
		}else{
			WorkTemplates::selectData();
			XboSet::getXboSet("worktemplates");
			renderData();	
		}	
		
	}
	public static function init(){
		$get = new WorkTemplates();		
		$wtnum = $get->wtnum;
		
		if(isset($wtnum)) {
			XboSet::getXboSet("worktemplates", "wtnum = '$wtnum'");
			$result = Xboset::validate();
			
			if ($result->num_rows){
				$exec = WorkTemplates::changes();
				}else{
				$exec = WorkTemplates::add();
				
			}
		}
		
		WorkTemplates::displayColumns();
	}
	
	
	public static function add(){
		XboSet::getXboSet("worktemplates");
		XboSet::add();
	}

	public static function changes(){
		$get = new WorkTemplates();		
		$wtnum =	$get->wtnum;		
		XboSet::getXboSet("worktemplates", "wtnum='$wtnum'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

WorkTemplates::init();




?>