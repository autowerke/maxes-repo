<?php 
require_once("actions.php");
header('Access-Control-Allow-Origin: *');
		
Class Action  {
	public static function Run(){
		if(isset($_POST["crimporttax"])) ActionSet::ImportTax();
		if(isset($_POST["updtaxgrouppayment"])) ActionSet::UpdateTaxGroupPayment();
		if(isset($_POST["updtaxpayment"])) ActionSet::UpdateTaxPayment();
		if(isset($_GET["tbcx"])) ValidateTables::tbcx();
	}	
}
Action::Run();

?>