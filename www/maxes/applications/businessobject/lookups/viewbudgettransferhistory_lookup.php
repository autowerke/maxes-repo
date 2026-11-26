<?php 
include ("../eat.php");

class ViewBudgetTransferHistory extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
	
		$where =null;
		
		$budgettransfernum = Super::getParam("budgettransfernum");
		$budgettransfernum = ($budgettransfernum !== null) ? $budgettransfernum : '';
		

		$where = (!empty($budgettransfernum))
			? "budgettransfernum = '$budgettransfernum'"
			: null;
			
		
		Xbo::select("Bgt. Transfer Num.", "budgettransfernum");
		Xbo::select("From Budget Num.", "frombudgetnum");
		Xbo::select("From Budget Line.", "frombudgetlinenum");
		Xbo::select("To Budget Num.", "tobudgetnum");
		Xbo::select("To Budget Line.", "tobudgetlinenum");
		Xbo::select("Prev. Budget Amount", "prevbudgetamount", "decimal");
		Xbo::select("New Budget Amount", "newbudgetamount", "decimal");
		Xbo::select("Transfered By", "transferedby");	
		Xbo::select("Transfered Date", "transfereddate", "date");
		Xbo::select("Transfer Reason", "transferreason");
		XboSet::getXboSet("budgettransferhistory", $where);
		XboSet::displayData();
	}
	
	public static function init(){
		ViewBudgetTransferHistory::displayData();
	}
	
}

ViewBudgetTransferHistory::init();




?>