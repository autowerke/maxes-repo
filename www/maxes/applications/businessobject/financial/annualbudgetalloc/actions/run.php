<?php 
require_once("actions.php");
header('Access-Control-Allow-Origin: *');
		
Class Action  {
	public static function Run(){
		
		if(isset($_POST["updateNewBudgetPO"])) ActionSet::updateNewBudgetPO();
		
	}	
}


Action::Run();

?>