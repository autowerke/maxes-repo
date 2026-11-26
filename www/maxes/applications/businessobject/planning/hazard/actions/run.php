<?php 
require_once("actions.php");
header('Access-Control-Allow-Origin: *');
		
Class Action  {
	public static function Run(){
		
		if(isset($_POST["addPrecaution"])) ActionSet::addPrecaution(); 
		
	}	
}
Action::Run();

?>