<?php 
require_once("actions.php");
header('Access-Control-Allow-Origin: *');
		
Class Action  {
	public static function Run(){
		
		if(Super::hasParam("getDocumentLineBreakdown")) ActionSet::getDocumentLineBreakdown();
		if(Super::hasParam("getBudgetLineDetail")) ActionSet::getBudgetLineDetail();
		if(Super::hasParam("budgetInsightChart")) ActionSet::budgetInsightChart();
		if(Super::hasParam("updateBudgetDummy")) ActionSet::updateBudgetDummy();		
		if(Super::hasParam("modifyBudgetLine")) ActionSet::modifyBudgetLine();
		if(isset($_POST["calculateBudget"])) ActionSet::calculateBudget();
		//validate tables
		if(Super::hasParam("tbcx")) ValidateTables::tbcx();
	}	
}


Action::Run();

?>