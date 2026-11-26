<?php 
require_once ("../../eat.php");

class mr extends Xbo {
	public $mrnum;
	public function __construct(){
		$this->mrnum = Xbo::getString("mrnum");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("MR", "mrnum");
		Xbo::select("Description", "description");
		Xbo::select("Requested Date", "requesteddate", "date");
		Xbo::select("Requested By", "requestedby");
		Xbo::select("WO", "wonum");	
		Xbo::select("Status", "status");		
	}
	public static function displayData(){
		if(isset($_GET['mxsdvsearch'])){
			$a = new Super();
			$b = $a->getWhere($_GET['mxsdvsearch'], "mr");
			$jointable = $b->join;
			$where = $b->where;
			mr::selectData();
			XboSet::getXboSet("mr", $where);
			renderData(null, $jointable);
		}else{
			mr::selectData();
			Xbo::select("Status", "status");	
			XboSet::getXboSet("mr");
			renderData();	
		}	
		
	}
	public static function init(){
		$get = new mr();		
		$mrnum = $get->mrnum;
		
		if(isset($mrnum)) {
			XboSet::getXboSet("mr", "mrnum = '$mrnum'");
			$result = Xboset::validate();
			
			if ($result->num_rows){
				$exec = mr::changes();
				}else{
				$exec = mr::add();
				
			}
		}
		
		mr::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("mr");
		XboSet::add();
	}

	public static function changes(){
		$get = new mr();		
		$mrnum = $get->mrnum;		
		XboSet::getXboSet("mr", "mrnum='$mrnum'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

mr::init();




?>