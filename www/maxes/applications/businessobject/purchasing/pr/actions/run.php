<?php 
require_once("actions.php");
header('Access-Control-Allow-Origin: *');
		
Class Action  {
	public static function Run(){
		//purchasing actions

		if(Super::hasParam("createcontracttransaction")) ActionSet::createcontracttransaction();
		if(Super::hasParam("createcontract")) ActionSet::createcontract();
		if(Super::hasParam("getServiceItemInfo")) ActionSet::GetServiceItemInfo();
		if(Super::hasParam("validateApproval")) ActionSet::validateApproval();

		if(isset($_POST["pochangeorderqty"])) ActionSet::POChangeOrderQty();
		if(isset($_POST["pochangeorderserv"])) ActionSet::POChangeOrderService();
		if(isset($_POST["getglcreditacct"])) ActionSet::GetGLCreditAccount();
		
		if(isset($_GET["validateshowchildren"])) ActionSet::ValidateShowChildren();
		if(isset($_POST["creatingnewpo"])) ActionSet::CreateNewPO();
		if(isset($_GET["sumprcost"])) ActionSet::SumPRCost();
		
		
		//validate tables
		if(isset($_POST["tbcx"])) ValidateTables::tbcx();
	}	
}


Action::Run();

?>