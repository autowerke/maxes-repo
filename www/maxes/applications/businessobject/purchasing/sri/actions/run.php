<?php 
require_once("actions.php");
header('Access-Control-Allow-Origin: *');
		
Class Action  {
	public static function Run(){
		if(isset($_POST["createpr"])) ActionSet::createpr(); 
		if(isset($_POST["CompleteServiceInspection"])) ActionSet::CompleteServiceInspection(); 
		if(isset($_POST["getMaxReceivableQty"])) ActionSet::getMaxReceivableQty(); 
		if(isset($_POST["copysrilinetosriinspection"])) ActionSet::CopySrilineToSriinspection(); 
		if(isset($_POST["copypolinetosriline"])) ActionSet::CopyPolineToSriline(); 
	}	
}


Action::Run();

?>