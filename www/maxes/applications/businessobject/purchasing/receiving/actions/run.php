<?php 
require_once("actions.php");
header('Access-Control-Allow-Origin: *');
		
Class Action  {
	public static function Run(){
		if(isset($_POST["changeexpdate"])) ActionSet::ChangeExpDate();
		if(isset($_POST["changerecdate"])) ActionSet::ChangeReceivedDate();
		if(isset($_POST["porectrans"])) ActionSet::PORecTrans();
		//validate tables
		if(isset($_GET["tbcx"])) ValidateTables::tbcx();
	}	
}


Action::Run();

?>