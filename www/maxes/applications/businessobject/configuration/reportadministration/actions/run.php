<?php 
require_once("actions.php");
header('Access-Control-Allow-Origin: *');
		
Class Action  {
	public static function Run(){
		if(isset($_GET["trundt"])) ActionSet::trundt();
	}	
}
Action::Run();

?>