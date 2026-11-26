<?php 
require_once("actions.php");
header('Access-Control-Allow-Origin: *');

Class Action  {
	public static function Run(){
        if(Super::hasParam("addAttributes")) ActionSet:: addAttributes();
    }

}

Action::Run();

?>