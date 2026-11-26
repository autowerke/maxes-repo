<?php 
require_once ("../../eat.php");

class DocTicket extends Xbo {
	public $docticketnum;
	public function __construct(){
		$this->docticketnum = Xbo::getString("docticketnum");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("Doc. Form Num.", "docticketnum");
        Xbo::select("Description", "description");
		Xbo::select("Procedure Num.", "proceduresnum");
		Xbo::select("Reported By", "reportedby");
		Xbo::select("Priority", "priority");
		Xbo::select("Status", "status");
	}
	public static function displayData(){
		if(isset($_GET['mxsdvsearch'])){
			$a = new Super();
			$b = $a->getWhere($_GET['mxsdvsearch'], "docticket");
			$jointable = $b->join;
			$where = $b->where;
			DocTicket::selectData();		
			XboSet::getXboSet("docticket", $where);
			renderData(null, $jointable);
		}else{
			DocTicket::selectData();
			XboSet::getXboSet("docticket");
			renderData();	
		}	
		
	}
	public static function init(){
		$get = new DocTicket();		
		$docticketnum = $get->docticketnum;
		
		if(isset($docticketnum)) {
			XboSet::getXboSet("docticket", "docticketnum = '$docticketnum'");
			$result = Xboset::validate();
			
			if ($result->num_rows){
				$exec = DocTicket::changes();
				}else{
				$exec = DocTicket::add();
				
			}
		}
		DocTicket::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("docticket");
		XboSet::add();
	}

	public static function changes(){
		$get = new DocTicket();		
		$docticketnum = $get->docticketnum;		
		XboSet::getXboSet("docticket", "docticketnum='$docticketnum'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

DocTicket::init();




?>