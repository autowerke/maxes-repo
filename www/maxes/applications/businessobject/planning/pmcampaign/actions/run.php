<?php 
require_once("actions.php");
header('Access-Control-Allow-Origin: *');
		
Class Action  {
	public static function Run(){
		
		if(Super::hasParam("schedulerAI")) ActionSet::schedulerAI();
		if(Super::hasParam("updateQtyProcCart")) ActionSet::updateQtyProcCart();
		if(Super::hasParam("createSQFromCart")) ActionSet::createSQFromCart();
		if(Super::hasParam("createPRFromCart")) ActionSet::createPRFromCart();
		if(Super::hasParam("updateProcurementCartBalloon")) ActionSet::updateProcurementCartBalloon();
		if(Super::hasParam("submitToProcurementCart")) ActionSet::submitToProcurementCart();

		if(isset($_POST["serviceHealth"])) ActionSet::serviceHealth();
		if(isset($_POST["materialHealth"])) ActionSet::materialHealth(); 
		if(isset($_POST["applyResSmoothing"])) ActionSet::applyResSmoothing(); 
		if(isset($_POST["deletePresets"])) ActionSet::deletePresets(); 
		if(isset($_POST["loadPreset"])) ActionSet::loadPreset(); 
		if(isset($_POST["savepreset"])) ActionSet::savepreset(); 
		if(isset($_POST["refreshHistogram"])) ActionSet::refreshHistogram(); 
		if(isset($_POST["importWOs"])) ActionSet::importWOs(); 
		if(isset($_POST["initGanttChart"])) ActionSet::initGanttChart(); 
		if(isset($_POST["addForecastings"])) ActionSet::addForecastings(); 
		if(isset($_POST["addForecastings"])) ActionSet::addForecastings(); 
		if(isset($_POST["addWorkorders"])) ActionSet::addWorkorders(); 
		

		
		if(Super::hasParam("tbcx")) ValidateTables::tbcx();
	}	
}
Action::Run();

?>