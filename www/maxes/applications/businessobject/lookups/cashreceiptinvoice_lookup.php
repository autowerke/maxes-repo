<?php 
include ("../eat.php");

class CashReceiptInvoice extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function render(){
		$whereArr=array();
		$cashreceiptnum = Super::getParam("cashreceiptnum");
		if(!empty($cashreceiptnum)) array_push($whereArr, "cashreceiptnum ='".$cashreceiptnum."'");
		else $where = null;
		
		$where = implode(" and ", $whereArr);
		
		Xbo::select("No. Invoice", "invoicenum");
		Xbo::select("Description", "invoice.description");
		Xbo::select("SO", "invoice.sonum");
		Xbo::select("PO", "invoice.ponum");
		Xbo::select("Line Cost", "invoice.totalcost", "decimal");
		Xbo::select("Status", "invoice.status");
		Xbo::select("Tgl. Dibayar", "invoice.statusdate", "date");
		XboSet::getXboSet("cashreceiptline", $where);
		XboSet::displayData(["cashreceiptline.invoicenum = invoice.invoicenum"], "distinct");
	}
	public static function init(){
		CashReceiptInvoice::render();
	}
	
}
CashReceiptInvoice::init();
?>