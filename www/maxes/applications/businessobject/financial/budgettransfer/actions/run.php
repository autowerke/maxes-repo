<?php 
require_once("actions.php");
header('Access-Control-Allow-Origin: *');
		
Class Action  {
	public static function Run(){
		if($_POST["transfering_budget"]){
			ActionSet::transfering_budget();
		}
		
		if(isset($_POST["loaddata"])) DataLoader::Load();
		if(isset($_POST["downloadloader"])) DataLoader::DownloadLoader();
	}	
}


Action::Run();

?>