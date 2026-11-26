<?php 
require_once ("../../eat.php");

class RFQ extends Xbo {
	
	protected $orgcode;
	public $rfqnum;	
	public function __construct(){
		$this->rfqnum = Xbo::getString("rfqnum");
	}
	public function __destruct()
    {
        return null;
    }
	
	public static function selectData(){
		Xbo::select("RFQ", "rfqnum");
		Xbo::select("Description", "description");
		Xbo::select("Requested By", "requestedby");
		Xbo::select("RFQ Date", "rfqdate", "date");
		Xbo::select("Line Cost", "totalcost", "decimal");
		Xbo::select("Line Cost Basis", "totalbasecost", "decimal");
		Xbo::select("Status", "status");
	}
	public static function displayCols(){
		$defaultjoin = null;
		if(isset($_GET['mxsdvsearch'])){
			$a = new Super();
			$b = $a->getWhere($_GET['mxsdvsearch'], "rfq");
			$jointable = $b->join;
			$where = $b->where;
			RFQ :: selectData();
			XboSet::getXboSet("rfq", $where);
			renderData($defaultjoin, $jointable);
		}else{
			RFQ :: selectData();
			$whereclause = Super::IDWhereClause("rfqid");
			XboSet::getXboSet("rfq", $whereclause);
			renderData($defaultjoin);
		}
	}
	
	public static function init(){
		$get = new RFQ();		
		$rfqnum = $get->rfqnum;
		if(isset($rfqnum)) {
			XboSet::getXboSet("rfq", "rfqnum = '$rfqnum'");
			$result = Xboset::validate();
			if ($result->num_rows){
				$exec = RFQ::changes();
				}else{
				$exec = RFQ::add();
				
			}
		}
		
		RFQ::displayCols();
	}
	
	
	public static function add(){
		XboSet::getXboSet("rfq");
		XboSet::add();
	}

	public static function changes(){
		$get = new RFQ();		
		$rfqnum =	$get->rfqnum;		
		XboSet::getXboSet("rfq", "rfqnum='$rfqnum'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

rfq::init();




?>