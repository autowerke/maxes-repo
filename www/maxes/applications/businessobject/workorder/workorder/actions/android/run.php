<?php
require_once("actions.php");
header('Access-Control-Allow-Origin: *');
Class MaximoRun  {
	public static function Run(){
		$json = file_get_contents('php://input');
		// Converts it into a PHP object
		$data = json_decode($json);
		if(isset($data->addworkorder)){
			WorkOrderAndroidActions::AddWorkOrder($data);
		}

		if(isset($data->changestatuswo)){
			WorkOrderAndroidActions::ChangeStatusWorkorder($data);
		}
		
		
		
	
	}
}


MaximoRun::Run();


		
		
?>