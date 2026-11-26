<?php 
require_once("actions.php");
header('Access-Control-Allow-Origin: *');
		
Class Action  {
	public static function Run(){
		
		if(Super::hasParam("rfqquotationAI")) ActionSet::rfqquotationAI();
		
		if(Super::hasParam("createcontracttransaction")) ActionSet::createcontracttransaction();
		if(Super::hasParam("createpotransaction")) ActionSet::createpotransaction();
		if(Super::hasParam("createcontract")) ActionSet::createcontract();
		if(Super::hasParam("createpo")) ActionSet::createpo();
		if(Super::hasParam("selectWinner")) ActionSet::selectWinner();
		if(Super::hasParam("submitNtp")) ActionSet::submitNtp();
		if(Super::hasParam("getVendorTotalCost")) ActionSet::getVendorTotalCost();
		if(Super::hasParam("getServiceItemInfo")) ActionSet::GetServiceItemInfo();
		
		if(isset($_POST["vendorpriceChart"])) ActionSet::vendorpriceChart(); 
		if(isset($_POST["copypr"])) ActionSet::copypr();
		if(isset($_POST["pochangeorderqty"])) ActionSet::POChangeOrderQty();
		if(isset($_POST["copyprline"])) ActionSet::CopyPRLines(); 
		if(isset($_POST["pochangeorderserv"])) ActionSet::POChangeOrderServ();
		if(isset($_POST["pochangeorderserv"])) ActionSet::POChangeOrderServ();
		if(isset($_POST["addquotationline"])) ActionSet::AddQuotationLines();
		if(isset($_GET["getwogl"])) ActionSet::GetWOGLAccount();
		if(isset($_GET["prpocan"])) ActionSet::PRPOCan();
		if(isset($_POST["sumrfqcost"])) ActionSet::sumrfqcost();	
		if(isset($_GET["validateshowchildren"])) ActionSet::ValidateShowChildren();
		//validate tables
		if(Super::hasParam("tbcx")) ValidateTables::tbcx();
	}	
}


Action::Run();

?>