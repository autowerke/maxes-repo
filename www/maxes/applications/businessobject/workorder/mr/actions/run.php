<?php 
require_once("actions.php");
header('Access-Control-Allow-Origin: *');
		
Class Action  {
	public static function Run(){
		
		if(Super::hasParam("validateIssueItem")) ActionSet::ValidateIssueItem();
		if(Super::hasParam("validatematplanqty")) ActionSet::ValidateMaterialPlanQty();
		if(Super::hasParam("validateStatusChange")) ActionSet::validateStatusChange();
		if(Super::hasParam("addinvreserve")) ActionSet::AddItemToInvReserve();	
		if(isset($_GET["removereserveditems"])) ActionSet::RemoveReservedItems();
		
		if(Super::hasParam("tbcx"))ValidateTables::tbcx();
	}	
}
Action::Run();

?>