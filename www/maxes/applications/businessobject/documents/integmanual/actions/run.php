<?php 
require_once("actions.php");
header('Access-Control-Allow-Origin: *');
		
Class Action  {
	public static function Run(){
		
		if(Super::hasParam("updateProcedureFromIntegManual")) ActionSet::updateProcedureFromIntegManual();
		if(Super::hasParam("addProcedures")) ActionSet::addProcedures();
		if(Super::hasParam("tbcx"))ValidateTables::tbcx();
	}	
}
Action::Run();

?>