<?php 
require_once("actions.php");
header('Access-Control-Allow-Origin: *');
		
Class Action  {
	public static function Run(){
		if(isset($_POST["addlocation"])) ActionSet::AddLocations();
		if(isset($_POST["addfleets"])) ActionSet::AddFleets();
		if(isset($_POST["pricechange"])) ActionSet::PriceChange();
		if(isset($_GET["tbcx"])) ValidateTables::tbcx();
		
	}	
}
Action::Run();

?>