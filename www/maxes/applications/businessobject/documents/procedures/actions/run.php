<?php 
require_once("actions.php");
header('Access-Control-Allow-Origin: *');
		
Class Action  {
	public static function Run(){
		if(isset($_POST["loaddata"])) DataLoader::Load();
		if(isset($_POST["downloadloader"])) DataLoader::DownloadLoader();
		if(Super::hasParam("validateStatusChange")) ActionSet::validateStatusChange();
		if(Super::hasParam("tbcx"))ValidateTables::tbcx();
		
	}	
}
Action::Run();

?>