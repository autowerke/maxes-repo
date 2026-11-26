<?php 
require_once ("../../eat.php");

class WorkInstr extends Xbo {
	public $workinstrnum;
	public function __construct(){
		$this->workinstrnum = Xbo::getString("workinstrnum");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("Work Instr. Num.", "workinstrnum");
		Xbo::select("Doc. Identifier", "docidentifier");
		Xbo::select("Procedure Num.", "proceduresnum");
		Xbo::select("Work Instr. Name", "workinstrname");
		Xbo::select("Category", "category");
		Xbo::select("Status", "status");
		Xbo::select("Revision", "revision");
		Xbo::select("Scope", "scope");
		Xbo::select("Description", "description");
	}
	public static function displayData(){
		if(isset($_GET['mxsdvsearch'])){
			$a = new Super();
			$b = $a->getWhere($_GET['mxsdvsearch'], "workinstr");
			$jointable = $b->join;
			$where = $b->where;
			WorkInstr::selectData();		
			XboSet::getXboSet("workinstr", $where);
			renderData(null, $jointable);
		}else{
			WorkInstr::selectData();
			XboSet::getXboSet("workinstr");
			renderData();	
		}	
		
	}
	public static function init(){
		$get = new WorkInstr();		
		$workinstrnum = $get->workinstrnum;
		
		if(isset($workinstrnum)) {
			XboSet::getXboSet("workinstr", "workinstrnum = '$workinstrnum'");
			$result = Xboset::validate();
			
			if ($result->num_rows){
				$exec = WorkInstr::changes();
				}else{
				$exec = WorkInstr::add();
				
			}
		}
		WorkInstr::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("workinstr");
		XboSet::add();
	}

	public static function changes(){
		$get = new WorkInstr();		
		$workinstrnum = $get->workinstrnum;		
		XboSet::getXboSet("workinstr", "workinstrnum='$workinstrnum'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

WorkInstr::init();




?>