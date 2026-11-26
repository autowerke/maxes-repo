<?php
require_once ("../../eat.php");

class BudgetTransfer extends Xbo {

	protected $budgettransferid;
	public $budgettransfernum;
	public function __construct(){
		$this->budgettransfernum = Xbo::getString("budgettransfernum");
	}

	public function __destruct()
    {
        return null;
    }

	public static function selectData(){
		Xbo::select("Transfer Number", "budgettransfernum");
		Xbo::select("Description", "description");
		Xbo::select("Transfer Date", "transferdate", "date");
		Xbo::select("From Budget", "frombudgetnum");
		Xbo::select("To Budget", "tobudgetnum");
		Xbo::select("Transfer Amount", "transferamount", "decimal");
		Xbo::select("Reason", "transferreason");
		Xbo::select("Requestor", "requestor");
		Xbo::select("Status", "status");
		Xbo::select("Approval Date", "approvaldate", "date");
		Xbo::select("Approver", "approver");
		Xbo::select("Comments", "comments");
	}

	public static function displayData(){
		BudgetTransfer::selectData();
		XboSet::getXboSet("budgettransfer");
		renderData();
	}

	public static function init(){
		$get = new BudgetTransfer();
		$budgettransfernum = $get->budgettransfernum;

		if(isset($budgettransfernum) ) { 
			XboSet::getXboSet("budgettransfer", "budgettransfernum='$budgettransfernum'");
			$result = Xboset::validate();
			if ($result->num_rows){
				$exec = BudgetTransfer::changes();
			}else{
				$exec = BudgetTransfer::add();
			}
		}

		BudgetTransfer::displayData();
	}


	public static function add(){
		XboSet::getXboSet("budgettransfer");
		XboSet::add();
	}

	public static function changes(){
		$get = new BudgetTransfer();
		$budgettransfernum = $get->budgettransfernum; 
		XboSet::getXboSet("budgettransfer", "budgettransfernum='$budgettransfernum'");
		XboSet::changes();
	}

	public static function showDetail(){
		XboSet::showDetail();
	}
}

BudgetTransfer::init();

?>