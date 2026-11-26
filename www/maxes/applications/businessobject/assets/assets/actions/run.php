<?php 

require_once("actions.php");
header('Access-Control-Allow-Origin: *');

Class Action  {
	public static function Run(){
		
		//if(Super::hasParam("aiAsSum"))
		if(isset($_POST["loaddata"])) DataLoader::Load();
		if(isset($_POST["downloadloader"])) DataLoader::DownloadLoader();
		
		
		if(Super::hasParam("trainAssetAI")) ActionSet:: trainAssetAI();
		if(Super::hasParam("aiAsSum")) ActionSet:: AIAssetSummary();

		if(Super::hasParam("addAttributes")) ActionSet:: addAttributes();
		if(Super::hasParam("gendescription")) ActionSet:: generateDescription();


		if(isset($_POST["createAssets"])) ActionSet::createAssets();  
		if(isset($_POST["createItems"])) ActionSet::createItems();  
		if(isset($_POST["getFleetOverview"])) ActionSet::getFleetOverview(); 
		if(isset($_POST["getReverseGeocode"])) ActionSet::getReverseGeocode(); 
		if(isset($_POST["getAssetTrackHistory"])) ActionSet::getAssetTrackHistory(); 
		if(isset($_POST["getCurrentAssetLocations"])) ActionSet::getCurrentAssetLocations(); 
		if(isset($_POST["setGPSPosition"])) ActionSet::setGPSPosition(); 
		if(isset($_POST["getGaugeMeters"])) ActionSet::getGaugeMeters(); 
		if(isset($_POST["getAssetHealth"])) ActionSet::getAssetHealth(); 
		if(isset($_POST["save_asset_health"])) ActionSet::saveAssetHealth(); 
		
		if(isset($_POST["getAssetHealthHistory"])) ActionSet::getAssetHealthHistory();
		if(isset($_POST["approveModification"])) ActionSet::approveModification();
		if(isset($_POST["requestItemNum"])) ActionSet::requestItemNum();
		
		if(isset($_POST["createprassetmod"])) ActionSet::createprassetmod(); 
		if(isset($_POST["checkPRMod"])) ActionSet::checkPRMod(); 
		if(isset($_POST["productivityModChart"])) ActionSet::productivityModChart(); 
		if(isset($_POST["downtimeModChart"])) ActionSet::downtimeModChart(); 
		if(isset($_POST["meterModChart"])) ActionSet::meterModChart(); 
		if(isset($_POST["createroi"])) ActionSet::createAnalyticForROI(); 		
		if(isset($_POST["sumModificationCost"])) ActionSet::sumModificationCost(); 		
		if(isset($_POST["addDowntimePrediction"])) ActionSet::addDowntimePrediction(); 
		if(isset($_POST["addMeterPrediction"])) ActionSet::addMeterPrediction(); 
		if(isset($_POST["addProductivityPrediction"])) ActionSet::addProductivityPrediction(); 
		if(isset($_POST["recalculateproductivity"])) ActionSet::recalculateproductivity(); 
		if(isset($_POST["reportDowntime"])) ActionSet::reportDowntime(); 
		if(isset($_POST["getFailures"])) ActionSet::getFailureCodeOptions(); 
		if(isset($_POST["enterparamproductivity"])) ActionSet::enterparamproductivity(); 
		if(isset($_POST["addparamsForAsset"])) ActionSet::addparamsForAsset(); 
		if(isset($_POST["addAssetParam"])) ActionSet::addAssetParam();
		if(isset($_POST["MTTRChart"])) ActionSet::MTTRChart();
		if(isset($_POST["MTBFChart"])) ActionSet::MTBFChart();
		if(isset($_POST["meterChart"])) ActionSet::meterChart();
		if(isset($_POST["seatconfig"]))ActionSet::SeatConfig();
		if(isset($_POST["addmeters"]))ActionSet::AddExistingMeters();
		if(isset($_POST["addHealthAdvisor"])) ActionSet::addHealthAdvisor();
		if(isset($_POST["chksubrecord"])) ActionSet::CheckSubRecord();
		if(isset($_POST["entermeterreading"])) ActionSet::EnterMeterReading();
		if(isset($_POST["scanrfidasset"])) ActionSet::ScanRFIDAsset();
		if(isset($_POST["moveasset"])) ActionSet::callMoveAsset();
		if(isset($_POST["calcdeprec"])) ActionSet::calcdeprec();
		
		if(isset($_GET["updassetparent"])) ActionSet::updassetparent();
		if(isset($_GET["ytdcost"])) ActionSet::ytdcost();
		if(isset($_GET["addMeter"])) ActionSet::addMeter();
		
		if(Super::hasParam("addClassifications")) ActionSet::addClassifications();
		
		//validate tables
		if(Super::hasParam("tbcx")) ValidateTables::tbcx();
		
	}	
}
Action::Run();

?>