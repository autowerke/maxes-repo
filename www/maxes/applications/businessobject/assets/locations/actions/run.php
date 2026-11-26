<?php 
require_once("actions.php");
header('Access-Control-Allow-Origin: *');
		
Class Action  {
	public static function Run(){
		if(isset($_POST["getAllLocations"])) ActionSet::getAllLocations();
		if(isset($_POST["getGeoLocation"])) ActionSet::getGeoLocation();
		if(isset($_POST["entermeterreading"])) ActionSet::EnterMeterReading();
		if(isset($_POST["addmeters"])) ActionSet::AddExistingMeters();
		if(isset($_GET["tbcx"])) ValidateTables::tbcx();
		if(isset($_POST["loaddata"])) DataLoader::Load();
		if(isset($_POST["downloadloader"])) DataLoader::DownloadLoader();
		//validate tables
		if(Super::hasParam("tbcx")) ValidateTables::tbcx();
	}	
}
Action::Run();

?>