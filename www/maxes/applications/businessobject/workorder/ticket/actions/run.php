<?php 
require_once("actions.php");
header('Access-Control-Allow-Origin: *');
		
Class Action  {
	public static function Run(){
		if(Super::hasParam("getAutoNum")) ActionSet::getAutoNum();
		if(Super::hasParam("createwofromticket")) ActionSet::CreateWOfromTicket();
	
		if(Super::hasParam("validateStatusChange")) ActionSet::validateStatusChange();
		if(Super::hasParam("tbcx"))ValidateTables::tbcx();
		
	}	
}
Action::Run();

?>