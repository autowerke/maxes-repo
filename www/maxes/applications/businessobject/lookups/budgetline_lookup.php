<?php 
include ("../eat.php");

class POLineLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
	
		$where =null;
		$notcancel = "and budget.status not in('canceled', 'draft', 'inactive', 'rejected', 'closed')";	
		
		if(isset($_GET['tobudgetnum'])){
			$budgetnum = Super::getParam("tobudgetnum");
		}else if(isset($_GET["frombudgetnum"])){
			$budgetnum = Super::getParam("frombudgetnum");
		}else if (isset($_GET["newbudgetnum"])){
			$budgetnum = Super::getParam("newbudgetnum");
		}else if (isset($_GET["changebudgetnum"])){
			$budgetnum = Super::getParam("changebudgetnum");
		}else {
			$budgetnum = Super::getParam("budgetnum");
		}
		
		if(!empty($budgetnum)) $where  = "budgetline_summary.budgetnum ='$budgetnum'";
		else $where = null;
		
		
		Xbo::select("Line", "budgetlinenum");
		Xbo::select("Budget Line Code", "budgetlinecode");
		Xbo::select("Description", "budgetline_description");
		Xbo::select("Budget#", "budgetnum");
		Xbo::select("Bgt. Description", "description");
		Xbo::select("Funding Source", "fundingsource");
		Xbo::select("Budget Amount", "budgetamount", "decimal");

		if(Super::hasParam("changebudgetnum")){
			Xbo::select("New Budget Amount", "_input_text_newbudgetamount", "decimal");
			Xbo::select("Funding Source", "_input_text_newfundingsource");
		}
		Xbo::select("Commited Amount", "committed_amount", "decimal");
		Xbo::select("Allocated Amount", "allocated_amount", "decimal");
		Xbo::select("Remaining Amount", "remaining_amount", "decimal");

		
		XboSet::getXboSet("budgetline_summary", $where);
		XboSet::displayData(["budgetline_summary.budgetnum = budget.budgetnum"]);
	
	}
	
	public static function init(){
		POLineLookup::displayData();
	}
	
}

POLineLookup::init();




?>