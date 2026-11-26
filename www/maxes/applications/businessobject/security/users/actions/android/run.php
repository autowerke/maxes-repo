<?php 
require_once("actions.php");

header('Access-Control-Allow-Origin: *');
		
Class AndroidRun  {
	public static function Run(){
		
		
		if(isset($_POST["submitMessage"])){
			UserActions::submitMessage();
		}
		if(isset($_POST["setSoftwareToExpired"])){
			UserActions::setSoftwareToExpired();
		}
		
		if(isset($_POST["checkNotification"])){
			UserActions::checkNotification();
		}
		
		if(isset($_POST["changePassword"])){
			UserActions::changePassword();
		}
		
		if(isset($_POST["getUsers"])){
			UserActions::getUsers($_POST["minid"], $_POST["length"]);
		}
		if(isset($_POST["deleteUsers"])){
			UserActions::deleteUsers();
		}
	}	
}


AndroidRun::Run();

?>