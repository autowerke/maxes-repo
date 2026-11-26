<?php 
/*author : Tommy Nitidiryo Sumarsono (c) 2015
*/
require_once ("../../eat.php");

class Receiving extends Xbo {
	
	public $ponum;
	public function __construct(){
		$this->ponum = Xbo::getString("ponum");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("PO", "ponum");
		Xbo::select("Description", "description");
		Xbo::select("Status", "status");
		Xbo::select("Order Date", "orderdate", "date");		
		Xbo::select("Vendor Name", "vendor.name");
		Xbo::select("Line Cost", "totalcost", "decimal");
		Xbo::select("Receipt", "receipt");
	}
	public static function displayData(){
		$defaultjoin = ["po.vendor = vendor.vendorcode"];
		$status = "'sent', 'fullpaid', 'partialpaid'";
		if(isset($_GET['mxsdvsearch'])){
			$a = new Super();
			$b = $a->getWhere($_GET['mxsdvsearch'], "po");
			$jointable = $b->join;
			$where = $b->where;
			Receiving::selectData();
			XboSet::getXboSet("po", "status in($status) and ".$where);
			renderData($defaultjoin, $jointable);
		}else{
			Receiving::selectData();
			$whereclause = Super::IDWhereClause("poid", "status in($status)");
			XboSet::getXboSet("po",$whereclause);
			renderData($defaultjoin);	
		}
	}
	public static function init(){
		$get = new Receiving();		
		$ponum = $get->ponum;
		if(isset($ponum)) {
			XboSet::getXboSet("po", "ponum = '$ponum'");
			$result = Xboset::validate();
			
			if ($result->num_rows){
				$exec = Receiving::changes();
				}else{
				$exec = Receiving::add();
			}
		}
		Receiving::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("PO", "status='sent'");
		XboSet::add();
	}

	public static function changes(){
		$get = new Receiving();		
		$ponum =	$get->ponum;		
		XboSet::getXboSet("po", "ponum='$ponum'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

Receiving::init();




?>