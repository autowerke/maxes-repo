<?php 
require_once("actions.php");
header('Access-Control-Allow-Origin: *');
		
Class Action  {
	public static function Run(){
		if(isset($_POST["loaddata"])) DataLoader::Load();
		if(isset($_POST["downloadloader"])) DataLoader::DownloadLoader();
		
		if(isset($_POST["updbasesalary"])) ActionSet::UpdateBaseSalary(); 
		if(isset($_POST["copyallowance"])) ActionSet::CopyAllowance();
		if(isset($_GET["totalotall"])) ActionSet::TotalOtherAllowance();
		
		if(isset($_POST["sumallowance"])) ActionSet::SumAllowance(); 
		if(isset($_GET["taxexempt"])) ActionSet::TaxExempt();
		if(isset($_GET["updalltax"])) ActionSet::UpdateAllTax();
		
		if(isset($_GET["tbcx"])) ValidateTables::tbcx();
	}	
}
Action::Run();

?>