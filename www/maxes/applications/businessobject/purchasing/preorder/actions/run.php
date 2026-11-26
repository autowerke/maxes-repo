<?php 
require_once("actions.php");
header('Access-Control-Allow-Origin: *');
		
Class Action  {
	public static function Run(){
		if(isset($_POST["createsalesquotation"])) ActionSet::createSalesQuotation();
		if(isset($_POST["createso"])) ActionSet::createSO();
		if(isset($_POST["getItemInfo"])) ActionSet::getItemInfo();
		if(isset($_POST["sumprocost"])) ActionSet::sumprocost();
		if(isset($_GET["getTaxValue"])) ActionSet::getTaxValue();
		
	}	
}


Action::Run();

?>