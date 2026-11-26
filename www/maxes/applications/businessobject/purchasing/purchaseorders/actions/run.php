<?php 
require_once("actions.php");
header('Access-Control-Allow-Origin: *');
		
Class POAction  {
	public static function Run(){
		if(Super::hasParam("changePONumber")) POActionSet::changePONumber();
		if(Super::hasParam("getVendorPhone")) POActionSet::getVendorPhone();
		if(Super::hasParam("getServiceItemInfo")) POActionSet::GetServiceItemInfo();
		if(isset($_POST["createAnnualBudgetAlloc"])) POActionSet::createAnnualBudgetAlloc();
		if(isset($_POST["addadditionalPORequest"])) POActionSet::addadditionalPORequest();
		if(isset($_POST["getglcreditacct"])) POActionSet::GetGLCreditAcct();
		if(isset($_POST["pochangeorderqty"])) POActionSet::POChangeOrderQty();
		if(Super::hasParam("CopyPRLines")) POActionSet::CopyPRLines(); 
		
		if(Super::hasParam("CopyContractLines")) POActionSet::CopyContractLines(); 
		if(isset($_POST["pochangeorderserv"])) POActionSet::POChangeOrderServ();
		if(isset($_GET["getwogl"])) POActionSet::GetWOGLAccount();
		if(isset($_GET["prpocan"])) POActionSet::PRPOCan();
		if(isset($_GET["sumpocost"])) POActionSet::SumPOCost();	
		if(isset($_GET["validateshowchildren"])) POActionSet::ValidateShowChildren();
		
		//validate tables
		if(isset($_POST["tbcx"])) POValidateTables::tbcx();
	}	
}


POAction::Run();

?>