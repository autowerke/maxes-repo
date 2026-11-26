<?php 
require_once("actions.php");
header('Access-Control-Allow-Origin: *');
		
Class Action  {
	public static function Run(){
		if(isset($_POST["changemaxcost"])) ActionSet::changemaxcost();
		if(isset($_POST["changesalesman"])) ActionSet::changesalesman();
		if(isset($_POST["alluninvoiceditems"])) ActionSet::GetAllUninvoicedItems();
		if(isset($_GET["tbce"])) ValidateTables::tbce();
		
		if(isset($_POST["loaddata"])) DataLoader::Load();
		if(isset($_POST["downloadloader"])) DataLoader::DownloadLoader();
		
		
	}	
}


Action::Run();

?>