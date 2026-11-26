<?php 
require_once("actions.php");
header('Access-Control-Allow-Origin: *');
		
Class Action  {
	public static function Run(){
		//purchasing actions
		
		if(Super::hasParam("getContractChartData")) ActionSet::getContractChartData();
		if(Super::getParam("checkAmountSpt")) ActionSet::checkAmountSpt();
		if(Super::getParam("releasePO")) ActionSet::releasePO();
		if(Super::getParam("getPONumber")) ActionSet::getPONumber();
		if(Super::getParam("createRevision")) ActionSet::createRevision();
		if(Super::getParam("validateStatusChange")) ActionSet::validateStatusChange();
		if(Super::getParam("getServiceItemInfo")) ActionSet::getServiceItemInfo();

		//validate tables
		if(Super::getParam("tbcx")) ValidateTables::tbcx();
	}	
}


Action::Run();

?>