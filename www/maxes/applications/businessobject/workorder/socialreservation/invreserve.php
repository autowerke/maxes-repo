<?php
require_once ("../../eat.php");

class Invreserve extends Xbo {
	public $invreservenum;
	public function __construct(){
		$this->invreservenum = Xbo::getString("invreservenum"); 
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("Item Number", "itemnum");
		Xbo::select("Invreserve", "invreservenum"); 
		Xbo::select("Work Order", "wonum"); 
		Xbo::select("Target Start Date", "targstartdate", "date");
		Xbo::select("Quantity", "quantity");
		Xbo::select("Warehouse", "warehouse");
		Xbo::select("Reservation Status", "status");
		Xbo::select("Negotiation Status", "negotiationstatus");
		Xbo::select("Reserved For", "reservedfor");
		Xbo::select("Last Checking Status", "lastvalidationstatus");
		Xbo::select("Last Checking Date", "lastvalidationstatusdate", "date");
	}
	
	public static function displayData(){
		if(isset($_GET['mxsdvsearch'])){
			$a = new Super();
			$b = $a->getWhere($_GET['mxsdvsearch'], "invreserve"); 
			$jointable = $b->join;
			$where = $b->where;
			Invreserve::selectData();
			XboSet::getXboSet("Invreserve", $where); 
			renderData(null, $jointable);
		}else{
			Invreserve::selectData(); 
			XboSet::getXboSet("Invreserve"); 
			renderData();
		}
	}
	public static function init(){
		$get = new Invreserve();
		$invreservenum = $get->invreservenum; 

		if(isset($invreservenum)) { 
			XboSet::getXboSet("Invreserve", "invreservenum = '$invreservenum'");
			$result = Xboset::validate();

			if ($result->num_rows){
				$exec = Invreserve::changes(); 
				}else{
				$exec = Invreserve::add();

			}
		}
		Invreserve::displayData(); 
	}


	public static function add(){
		XboSet::getXboSet("Invreserve"); 
		XboSet::add();
	}

	public static function changes(){
		$get = new Invreserve(); 
		$invreservenum = $get->invreservenum; 
		XboSet::getXboSet("Invreserve", "invreservenum='$invreservenum'"); 
		XboSet::changes();
	}

	public static function showDetail(){
		XboSet::showDetail();
	}
}

Invreserve::init(); 
?>