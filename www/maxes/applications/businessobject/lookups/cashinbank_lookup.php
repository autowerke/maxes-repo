<?php 
include ("../eat.php");

class CashInBank extends Xbo {

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
		
		Xbo::select("Line Num.", "cashinbanklinenum");
		Xbo::select("Description", "description");
		Xbo::select("Financial Period", "financialperiod");
		Xbo::select("Catatan Bank", "banknotes");
		Xbo::select("Total Transfer", "totaltransfer", "decimal");
		Xbo::select("Transfered Date", "transferdate", "date");
		XboSet::getXboSet("cashinbank", $where);
		XboSet::displayData();
	}
	public static function init(){
		CashInBank::render();
	}
	
}
CashInBank::init();
?>