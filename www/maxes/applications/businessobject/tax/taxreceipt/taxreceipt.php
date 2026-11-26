<?php 
require_once ("../../eat.php");

class TaxReceiving extends Xbo {
	
	protected $taxreceiptnum;
		
	public function __construct(){
		$this->taxreceiptnum = Xbo::getString("taxreceiptnum");
	}
	public function __destruct()
    {
        return null;
    }
	public static function selectData(){
		Xbo::select("Tax Receipt", "taxreceiptnum");
		Xbo::select("Invoice", "invoicenum");
		Xbo::select("Description", "invoice.description");
		Xbo::select("Sales Order", "invoice.sonum");
		Xbo::select("SO Description", "so.description");
		Xbo::select("Status Pembayaran", "so.status");
		Xbo::select("Discount(%)", "so.discountpercentage", "decimal");
		Xbo::select("Quantity", "quantity", "decimal");
		Xbo::select("Sub Total", "invoice.subtotalcost", "decimal");
		Xbo::select("Biaya Pajak", "lineprice", "decimal");
		Xbo::select("Line Cost", "invoice.totalcost","decimal");
		Xbo::select("Tgl Transfer", "statusdate2", "date");
		Xbo::select("Status", "status as taxstatus");
	}
	public static function displayData(){
		TaxReceiving::selectData();
		XboSet::getXboSet("taxreceipt", "taxreceipt.taxreceiptnum is not null");
		renderData(["taxreceipt.invoicenum = invoice.invoicenum", "invoice.sonum = so.sonum"]);
	}
	public static function init(){
		$get = new TaxReceiving();		
		$taxreceiptnum = $get->taxreceiptnum;
		if(isset($TaxReceiving)) {
			XboSet::getXboSet("taxreceipt", "taxreceiptnum = '$taxreceiptnum'");
			$result = Xboset::validate();
			if ($result->num_rows){
				$exec = TaxReceiving::changes();
				}else{
				$exec = TaxReceiving::add();
			}
		}
		
		TaxReceiving::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("taxreceipt");
		XboSet::add();
	}

	public static function changes(){
		$get = new TaxReceiving();		
		$taxreceiptnum = $get->taxreceiptnum;		
		XboSet::getXboSet("taxreceipt", "taxreceiptnum='$taxreceiptnum'");		
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

TaxReceiving::init();




?>