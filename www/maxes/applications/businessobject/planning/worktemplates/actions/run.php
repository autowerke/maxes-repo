<?php 
require_once("actions.php");
header('Access-Control-Allow-Origin: *');
		
Class Action  {
	public static function Run(){
		if(isset($_POST["loaddata"])) DataLoader::Load();
		if(isset($_POST["downloadloader"])) DataLoader::DownloadLoader();


		if(Super::hasParam("getLaborCost")) ActionSet::getLaborCost();
		if(isset($_POST["getTeamLeader"])) ActionSet::getTeamLeader(); 
		if(isset($_GET["validatematplanqty"])) ActionSet::ValidateMaterialPlanQty();
		if(isset($_GET["validateIssueItem"])) ActionSet::ValidateIssueItem();
		if(isset($_GET["tbcx"])) ValidateTables::tbcx();
		
	}	
}
Action::Run();

?>