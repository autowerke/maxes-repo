<?php 
require_once("actions.php");
header('Access-Control-Allow-Origin: *');
		
Class Action  {
	public static function Run(){
		
		if(isset($_POST["addMeterFreq"])) ActionSet::addMeterFreq();
		if(isset($_POST["addPMMeterFreqJP"])) ActionSet::addPMMeterFreqJP();
		if(isset($_POST["addAdditionalJP"])) ActionSet::addAdditionalJP();
		if(isset($_POST["addMeterRuleJP"])) ActionSet::addMeterRuleJP();
		if(isset($_POST["addHealthRuleJP"])) ActionSet::addHealthRuleJP();
		if(isset($_POST["addMeters"])) ActionSet::addMeters();
		if(isset($_POST["addJobPlans"])) ActionSet::addJobPlans();
		if(isset($_POST["getAssetMeters"])) ActionSet::getAssetMeters();  
		if(isset($_POST["addToCart"])) ActionSet::addToCart(); 
		if(isset($_POST["getJobPlanResources"])) ActionSet::getJobPlanResources(); 
		if(isset($_POST["getTeamLeader"])) ActionSet::getTeamLeader(); 
		if(isset($_POST["generatepm"])) ActionSet::generatePM(); 
		if(isset($_POST["generatewo"])) ActionSet::generateWO(); 
		
	}	
}
Action::Run();

?>