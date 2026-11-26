<?php 
require_once("actions.php");
header('Access-Control-Allow-Origin: *');
		
Class Action  {

	public static function Run(){
		if(isset($_GET["calculatespace"])) ActionSet::CalculateSpace();
		if(isset($_GET["sumtotalspace"])) ActionSet::SumTotalSpace();
	
		//validate tables
		if(Super::hasParam("tbcx")) ValidateTables::tbcx();
	}	
}


Action::Run();

?>