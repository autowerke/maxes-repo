<?php 
require_once("actions.php");
header('Access-Control-Allow-Origin: *');
		
Class Action  {
	public static function Run(){
		
		if(isset($_POST["updbasesalary"])) ActionSet::UpdateBaseSalary(); 
		
		if(isset($_GET["totalotall"])) ActionSet::TotalOtherAllowance();
		if(isset($_GET["sumallowance"])) ActionSet::SumAllowance();
		if(isset($_GET["taxexempt"])) ActionSet::TaxExempt();
		if(isset($_GET["updmyallowance"])) ActionSet::UpdateMyAllowance();
		if(isset($_GET["sumallowancecost"])) ActionSet::SumAllowanceCost();
		if(isset($_GET["getfinancialperiod"])) ActionSet::GetFinancialPeriod();
		if(isset($_GET["tbcx"])) ValidateTables::tbcx();
	}	
}
Action::Run();

?>