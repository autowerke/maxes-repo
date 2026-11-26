<?php 
require_once("actions.php");
header('Access-Control-Allow-Origin: *');
		
Class Action  {
	public static function Run(){
		if(Super::hasParam("getLaborCost")) ActionSet::getLaborCost();
		if(Super::hasParam("adjustMargin")) ActionSet::adjustMargin();
		if(Super::hasParam("getItemPriceInfo")) ActionSet::getItemPriceInfo();
		if(Super::hasParam("getServicePriceInfo")) ActionSet::getServicePriceInfo();
		if(Super::hasParam("createso")) ActionSet::createSO();
		if(Super::hasParam("sumsalesquotationcost")) ActionSet::SumSalesQuotationCost();
		if(Super::hasParam("tbcx")) ValidateTables::tbcx();
		if(Super::hasParam("validateStatusChange")) ActionSet::validateStatusChange();

	}	
}


Action::Run();

?>