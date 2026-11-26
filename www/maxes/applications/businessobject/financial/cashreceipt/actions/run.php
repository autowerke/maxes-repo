<?php 
require_once("actions.php");
header('Access-Control-Allow-Origin: *');
		
Class Action  {
	public static function Run(){
		if(isset($_POST["checktaxreceiptnum"])) ActionSet::CheckTaxReceiptNum();
		if(isset($_POST["crimportexp"])) ActionSet::crimportexp();
		if(isset($_POST["importcashreceipt"])) ActionSet::importcashreceipt();		
		if(isset($_POST["importcashout"])) ActionSet::importcashout();		
		if(isset($_POST["getopbal"])) ActionSet::getopbal();
		if(isset($_GET["sumcrcost"])) XboSet::sumcrcost();
		//validate tables
		if(isset($_GET["tbcx"])) ValidateTables::tbcx();
	}	
}


Action::Run();

?>