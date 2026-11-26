<?php 
require_once("actions.php");
header('Access-Control-Allow-Origin: *');
		
Class Action  {
	public static function Run(){
		if(isset($_POST["transferitems"])) ActionSet::CustomerTransferItems();
		if(isset($_POST["curbaladjust"])) ActionSet::CustomerQuantityAdjustment();
		if(isset($_POST["costadjust"])) ActionSet::CustomerCostAdjustment();
		if(isset($_POST["returnitem"])) ActionSet::CustomerReturnItems();
		if(isset($_POST["custshipment"])) ActionSet:: CustomerShipment();
		
		if(isset($_GET["tbcx"])) ValidateTables::tbcx();
	}	
}


Action::Run();

?>