<?php 
require_once ("../../eat.php");

class Invoice extends Xbo {
	
	protected $orgcode;
	public $invoicenum;
	public function __construct(){
		$this->invoicenum = Xbo::getString("invoicenum");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("Invoice", "invoicenum");
		Xbo::select("Description", "description");
		Xbo::select("Status", "status");
		Xbo::select("Status Date", "statusdate", "date");
		Xbo::select("Invoice Date", "invoicedate", "date");
		Xbo::select("Sub Total", "subtotalcost", "decimal");
		Xbo::select("Total", "totalcost", "decimal");
		Xbo::select("PO", "ponum");	
		Xbo::select("SO", "sonum");
		Xbo::select("Term (%)", "paypercent");
		Xbo::select("Vendor", "vendor.name");
		Xbo::select("Discount(%)", "so.discountpercentage");
		Xbo::select("customer", "customer.name as custname");
	}
	public static function displayData(){
		$defaultjoin = ["invoice.vendorcode = vendor.vendorcode", "invoice.sonum = so.sonum", "customer.customercode = so.customercode"];
		if(isset($_GET['mxsdvsearch'])){
			$a = new Super();
			$b = $a->getWhere($_GET['mxsdvsearch'], "invoice");
			$jointable = $b->join;
			$where = $b->where;
			
			Invoice::selectData();
			XboSet::getXboSet("invoice", $where);
			renderData($defaultjoin, $jointable);
		}else{
			$whereclause = Super::IDWhereClause("invoiceid");
			Invoice::selectData();
			XboSet::getXboSet("invoice", $whereclause);
			renderData($defaultjoin);	
		}
	}
	
	public static function init(){
		$get = new Invoice();		
		$invoicenum = $get->invoicenum;
		if(isset($invoicenum)) {
			XboSet::getXboSet("invoice", "invoicenum = '$invoicenum'");
			$result = Xboset::validate();
			if ($result->num_rows){
				$exec = Invoice::changes();
				}else{
				$exec = Invoice::add();
			}
		}
		Invoice::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("invoice");
		XboSet::add();
	}

	public static function changes(){
		$get = new Invoice();		
		$invoicenum =	$get->invoicenum;		
		XboSet::getXboSet("invoice", "invoicenum='$invoicenum'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

Invoice::init();




?>