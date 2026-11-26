<?php 
require_once ("../../eat.php");

class Teamdecree extends Xbo {
	public $teamdecreenum;

	public function __construct(){
		$this->teamdecreenum = Xbo::getString("teamdecreenum");
	}

	public function __destruct(){
		return null;
	}

	public static function selectData(){
		Xbo::select("Decree Num.", "teamdecreenum");
		Xbo::select("Decree Title", "decreetitle");
		Xbo::select("Decree Type", "decreetype");
		Xbo::select("Previous Decree", "oldteamdecreenum");
		Xbo::select("Start Date", "startdate");
		Xbo::select("End Date", "enddate");
		Xbo::select("Status", "status");
		Xbo::select("Description", "description");
		Xbo::select("Remarks", "remarks");
	}

	public static function displayData(){
		if(isset($_GET['mxsdvsearch'])){
			$a = new Super();
			$b = $a->getWhere($_GET['mxsdvsearch'], "teamdecree");
			$jointable = $b->join;
			$where = $b->where;
			Teamdecree::selectData();		
			XboSet::getXboSet("teamdecree", $where);
			renderData(null, $jointable);
		}else{
			Teamdecree::selectData();
			XboSet::getXboSet("teamdecree");
			renderData();	
		}	
	}

	public static function init(){
		$get = new Teamdecree();		
		$teamdecreenum = $get->teamdecreenum;
		
		if(isset($teamdecreenum)) {
			XboSet::getXboSet("teamdecree", "teamdecreenum = '$teamdecreenum'");
			$result = Xboset::validate();
			
			if ($result->num_rows){
				$exec = Teamdecree::changes();
			}else{
				$exec = Teamdecree::add();
			}
		}
		Teamdecree::displayData();
	}
	
	public static function add(){
		XboSet::getXboSet("teamdecree");
		XboSet::add();
	}

	public static function changes(){
		$get = new Teamdecree();		
		$teamdecreenum = $get->teamdecreenum;		
		XboSet::getXboSet("teamdecree", "teamdecreenum='$teamdecreenum'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

Teamdecree::init();
?>