<?php 
require_once("actions.php");
header('Access-Control-Allow-Origin: *');
		
Class Action  {
	public static function Run(){
		if(Super::hasParam("changeVendorPassword") ) ActionSet::changeVendorPassword();

		if(isset($_POST["loaddata"])) DataLoader::Load();
		if(isset($_POST["downloadloader"])) DataLoader::DownloadLoader();
		if(isset($_GET["createcustomer"])) ActionSet::CreateCustomer();
		if(isset($_GET["initActionVendor"])) ActionSet::InitActionVendor();
		//validate tables
		if(isset($_GET["tbcx"])) ValidateTables::tbcx();
		if(isset($_GET["tbce"])) ValidateTables::tbce();
	}	
}


Action::Run();

?>