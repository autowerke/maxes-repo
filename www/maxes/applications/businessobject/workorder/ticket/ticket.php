<?php 
require_once ("../../eat.php");

class Ticket extends Xbo {
	public $ticketnum;
	public function __construct(){
		$this->ticketnum = Xbo::getString("ticketnum");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		
		Xbo::select("Ticket", "ticketnum");
		Xbo::select("Description", "description");
		Xbo::select("Reported Date", "reportdate", "date");
		Xbo::select("Requested By", "reportedby");
		Xbo::select("Type", "type");
		Xbo::select("Prioritas", "priority");
		Xbo::select("Status", "status");	
	}
	public static function displayData(){
		if(isset($_GET['mxsdvsearch'])){
			$a = new Super();
			$b = $a->getWhere($_GET['mxsdvsearch'], "ticket");
			$jointable = $b->join;
			$where = $b->where;
			Ticket::selectData();		
			XboSet::getXboSet("Ticket", $where);
			renderData(null, $jointable);
		}else{
			Ticket::selectData();
			XboSet::getXboSet("Ticket");
			renderData();	
		}	
		
	}
	public static function init(){
		$get = new Ticket();		
		$ticketnum = $get->ticketnum;
		
		if(isset($ticketnum)) {
			XboSet::getXboSet("Ticket", "ticketnum = '$ticketnum'");
			$result = Xboset::validate();
			
			if ($result->num_rows){
				$exec = Ticket::changes();
				}else{
				$exec = Ticket::add();
				
			}
		}
		Ticket::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("Ticket");
		XboSet::add();
	}

	public static function changes(){
		$get = new Ticket();		
		$ticketnum = $get->ticketnum;		
		XboSet::getXboSet("Ticket", "ticketnum='$ticketnum'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

Ticket::init();




?>