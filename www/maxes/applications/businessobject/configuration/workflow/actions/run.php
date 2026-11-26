<?php 
require_once("actions.php");
header('Access-Control-Allow-Origin: *');
		
Class Action  {
	public static function Run(){
		if (Super::hasParam("getRoles"))  ActionSet::getRoles();
		if (Super::hasParam("saveWF"))  ActionSet::saveWF();

		if (Super::hasParam("renderWF"))  ActionSet::renderWF();
		if(Super::hasParam("moveinbox")) ActionSet::MoveWorkflowInbox();
		if(Super::hasParam("reassigninbox")) ActionSet::ReassignInbox();
		if(Super::hasParam("tbcx")) ValidateTables::tbcx();
		
	}	
	
	
}
Action::Run();

?>