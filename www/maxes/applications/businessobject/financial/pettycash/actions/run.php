<?php 
require_once("actions.php");
header('Access-Control-Allow-Origin: *');
		
Class Action  {
	public static function Run(){
		
		if(isset($_POST["importpetcashexp"])) ActionSet::importpetcashexp();
		if(isset($_POST["getopbal"])) ActionSet::getopbal();
		if(isset($_GET["sumpettycash"])) ActionSet::SumPettyCash();
		//validate tables
		if(isset($_GET["tbcx"])) ValidateTables::tbcx();
	}	
}


Action::Run();

?>