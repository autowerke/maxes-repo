<?php 
require_once("actions.php");
header('Access-Control-Allow-Origin: *');
		
Class Action  {
	public static function Run(){
		if(isset($_POST["calculateprice"])) {
			ActionSet::CalculatePrice();
		}
		
		if(isset($_POST["sumlaundrycost"])) {
			ActionSet::CalculatePrice();
		}
		if(isset($_POST["getlaundrytype"])){
			ActionSet::GetLaundryType();
		}
		if(isset($_POST["calculatelnsatuan"])){
			ActionSet::LaundrySatuan();
		}
		//validate tables
		if(isset($_GET["tbcx"])) ValidateTables::tbcx();
	}	
}


Action::Run();

?>