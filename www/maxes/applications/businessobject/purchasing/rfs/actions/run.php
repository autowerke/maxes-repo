<?php 
require_once("actions.php");
header('Access-Control-Allow-Origin: *');
		
Class Action  {
	public static function Run(){
		if(isset($_POST["CompleteReturn"])) ActionSet::CompleteReturn(); 
		if(isset($_POST["validate_returnqty"])) ActionSet::validate_returnqty(); 
		if(isset($_POST["isprocessed"])) ActionSet::isprocessed(); 
		if(isset($_POST["CompleteReceipt"])) ActionSet::CompleteReceipt(); 
		if(isset($_POST["getPOEstimatedDateArrival"])) ActionSet::getPOEstimatedDateArrival(); 
		if(isset($_POST["copyemptorfsins"])) ActionSet::CopyEmployeesToInspection(); 
		if(isset($_POST["copypolinetorfsline"])) ActionSet::CopyPOLineToRFSLine(); 
		if(isset($_POST["copyrfslinetoinspection"])) ActionSet::CopyRFSLineToInspection(); 
		if(isset($_POST["receiptinsitem"])) ActionSet::CompleteReceipt();
		if(isset($_POST["returninspitem"])) ActionSet::ReturnItems();
		if(isset($_POST["inspchangeorderqty"])) ActionSet::ValidateInspectedQty(); 		
		//validate tables

		if(Super::hasParam("tbcx"))ValidateTables::tbcx();
	}	
}


Action::Run();

?>