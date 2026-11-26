<?php 
require_once("actions.php");
header('Access-Control-Allow-Origin: *');
		
Class Action  {
	public static function Run(){
		if(isset($_POST["addcapital"])) ActionSet::AddCapital();
		if(isset($_POST["vrbniglaccount"])) ActionSet::AddGLDefault();
		
		if(isset($_POST["setExistingGL"])) ActionSet::setExistingGL();
	}	
}
Action::Run();

?>