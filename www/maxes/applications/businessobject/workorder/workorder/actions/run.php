<?php 
require_once("actions.php");
header('Access-Control-Allow-Origin: *');
		
Class Action  {

	
	public static function Run(){
		

		
		if(Super::hasParam("productionOrderMultiplier")) ActionSet::productionOrderMultiplier();
		if(Super::hasParam("woqualityalert")) ActionSet::getWOQualityScore();
		if(Super::hasParam("removeWOParent")) ActionSet::removeWOParent();
		if(Super::hasParam("assignWOParent")) ActionSet::assignWOParent();
		if(Super::hasParam("getLaborCost")) ActionSet::getLaborCost();
		if(Super::hasParam("getWBSCode")) ActionSet::getWBSCode();
		if(Super::hasParam("rsrcwb")) ActionSet::resourceWorkbench();
		if(Super::hasParam("wcwt")) ResourceWorkbenchHelper::loadWorkClassificationAndType();

		if(Super::hasParam("updateWoTaskProgress")) ActionSet::updateWoTaskProgress();
		if(Super::hasParam("newWOProgress")) ActionSet::newWOProgress();
		if(Super::hasParam("iswohastask")) ActionSet::iswohastask();
		if(Super::hasParam("getOprAssignment")) ActionSet::getOprAssignment();
 		if(Super::hasParam("checkavailablelabors")) ActionSet::checkavailablelabors();
		
		if(Super::hasParam("woTaskRecom")) ActionSet::woTaskRecommendation();
		if(Super::hasParam("failRecom")) ActionSet::failureRecommendation();
		if(Super::hasParam("submitKHS")) ActionSet::submitKHS();
		if(Super::hasParam("gSeritDetail")) ActionSet::getServiceItemDetail();

		if(isset($_POST["calculateAllWos"])) ActionSet::calculateAllWos();
		
		if(isset($_POST["getToolType"])) ActionSet::getToolType(); 
		if(isset($_POST["lendTools"])) ActionSet::lendTools();
		if(isset($_POST["transferTools"])) ActionSet::transferTools(); 
		if(isset($_POST["dispatchTools"])) ActionSet::dispatchTools(); 
		
		if(isset($_POST["createAssets"])) ActionSet::createAssets();  
		if(isset($_POST["createItems"])) ActionSet::createItems(); 
		if(isset($_POST["createWT"])) ActionSet::createWT(); 
		
		if(isset($_POST["updateLastStartDate"])) ActionSet::updateLastStartDate();  
		if(isset($_POST["updateLastCompDate"])) ActionSet::updateLastCompDate();  
		if(isset($_POST["getAssetHealth"])) ActionSet::getAssetHealth(); 
		if(isset($_POST["save_asset_health"])) ActionSet::saveAssetHealth(); 
		if(isset($_POST["reportDowntime"])) ActionSet::reportDowntime(); 
		if(isset($_POST["getFailures"])) ActionSet::getFailureCodeOptions(); 
		if(isset($_POST["selectedZone"])) ActionSet::selectedZone();
		if(isset($_POST["viewFailureHierarchy"])) ActionSet::viewFailureHierarchy();
		if(isset($_POST["rootcause"])) ActionSet::rootcause();
		if(isset($_POST["quick_reservation"])) ActionSet::QuickReservation();
		if(isset($_POST["createprfromwo"])) ActionSet::createprfromwo();
		if(isset($_POST["createsqfromwo"])) ActionSet::createsqfromwo();
		if(isset($_POST["getPRNumber"])) ActionSet::getPRNumber();
		if(isset($_POST["getSQNumber"])) ActionSet::getSQNumber();
		if(isset($_POST["calculateLaborCost"])) ActionSet::calculateLaborCost();
		if(isset($_POST["calculateworkprogress"])) ActionSet::calculateworkprogress();
		if(isset($_POST["modifylabortransline"])) ActionSet::ModifyLaborTransLine();
		if(isset($_POST["addlabortransline"])) ActionSet::AddLaborTransLine();
		if(isset($_POST["actprogress"])) ActionSet::ActualProgress();
		if(isset($_POST["viewmodshipment"])) ActionSet::ModifyShipment();
		if(isset($_POST["dispatchlabor"])) ActionSet::DispatchLabor();
		if(isset($_POST["dispatchtools"])) ActionSet::DispatchTools();
		if(isset($_POST["dispatchservices"])) ActionSet::DispatchServices();
		if(isset($_POST["checkshipitems"])) ActionSet::CheckShipItems();
		if(isset($_POST["changeshippingstatus"])) ActionSet::ChangeShippingStatus();
		if(isset($_POST["copyworkplan"])) ActionSet::CopyWorkPlan();	
		if(isset($_POST["origrecordid"])) ActionSet::UpdateTicket();
		if(isset($_POST["chksubrecord"])) ActionSet::CheckSubRecord();	
		if(isset($_POST["createitemfromwo"])) ActionSet::CreateItemFromWorkOrder();	
		if(isset($_POST["createassetfromwo"])) ActionSet::CreateAssetFromWorkOrder();	
		if(isset($_POST["getDefaultItemInfo"])) ActionSet::getDefaultItemInfo();
		if(isset($_POST["getItemIssueType"])) ActionSet::getItemIssueType();
		if(isset($_POST["addinvreserve"])) ActionSet::AddItemToInvReserve();
 		if(isset($_GET["checkactmat"])) ActionSet::CheckActualMaterialIfExist();	
		if(Super::hasParam("validateStatusChange")) ActionSet::validateStatusChange();
		if(isset($_GET["removereserveditems"])) ActionSet::RemoveReservedItems();
		if(isset($_POST["validatematplanqty"])) ActionSet::ValidateMaterialPlanQty();
		if(isset($_GET["sumwocost"])) ActionSet::SumWOCost();
		if(isset($_GET["updassetparent"])) ActionSet::UpdateAssetParent();	
		if(isset($_POST["validateIssueItem"])) ActionSet::ValidateIssueItem();	
		
		
		//DELETE RECORDS ACTION
		if(Super::hasParam("tbcx")) ValidateTables::tbcx();
		if(isset($_POST["deleteToolTrans"])) ValidateTables::deleteToolTrans();
		
	}	
}
Action::Run();

?>