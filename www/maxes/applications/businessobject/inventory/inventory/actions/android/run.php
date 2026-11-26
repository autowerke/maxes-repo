<?php 
require_once("actions.php");
//require_once("../../../../workorder/workorder/actions/run.php");

header('Access-Control-Allow-Origin: *');
		
Class AndroidRun  {
	public static function Run(){
		
		if(isset($_POST["deleteInvFl"])){
			InventoryAndroidActions::deleteInvFl();
		}
		
		if(isset($_POST["getIssueMaterials"])){
			InventoryAndroidActions::getIssueMaterials($_POST["minid"], $_POST["length"]);
		}
		if(isset($_POST["getPlanMaterials"])){
			InventoryAndroidActions::getPlanMaterials($_POST["minid"], $_POST["length"]);
		}
		if(isset($_POST["checkSafetyStock"])){
			InventoryAndroidActions::checkSafetyStock();
		}
		if(isset($_POST["getCountry"])){
			InventoryAndroidActions::getCountry($_POST["minid"], $_POST["length"]);
		}
		
		if(isset($_POST["getConditionType"])){
			InventoryAndroidActions::getConditionType($_POST["minid"], $_POST["length"]);
		}
		if(isset($_POST["modifymeasureunit"])){
			InventoryAndroidActions::modifyMeasureUnit();
		}
		
		if(isset($_POST["deletemeasureunit"])){
			InventoryAndroidActions::deleteMeasureUnit();
		}
		if(isset($_POST["getSafetyStocks"])){
			InventoryAndroidActions::getSafetyStocks($_POST["minid"], $_POST["length"]);
		}
		if(isset($_POST["readMultiConversionStock"])){
			InventoryAndroidActions::readMultiConversionStock();
		}
		if(isset($_POST["deleteConversionUnit"])){
			InventoryAndroidActions::deleteConversionUnit();
		}
		if(isset($_POST["addMoreConversion"])){
			InventoryAndroidActions::addMoreConversion();
		}
		if(isset($_POST["getItemConversions"])){
			InventoryAndroidActions::AddConversions();
		}
		if(isset($_POST["addvendor"])){
			InventoryAndroidActions::AddVendors();
		}
		
		if(isset($_POST["getVendors"])){
			InventoryAndroidActions::getVendors($_POST["minid"], $_POST["length"]);
		}
		
		if(isset($_POST["addwarehouse"])){
			InventoryAndroidActions::AddWarehouse();
		}
		
		
		if(isset($_POST["delracks"])){
			InventoryAndroidActions::DelRacks();
		}
		if(isset($_POST["addracks"])){
			InventoryAndroidActions::AddRacks();
		}
		
		
		if(isset($_POST["addMeasureUnit"])){
			InventoryAndroidActions::AddMeasureUnit();
		}
		if(isset($_POST["refreshinvFifoLifoRecord"])){
			InventoryAndroidActions::refreshRecord();
		}
		if(isset($_POST["getInventoryPrice"])){
			InventoryAndroidActions::getInventoryPrice($_POST["minid"], $_POST["length"]);
		}
		if(isset($_POST["getInventoryReservation"])){
			InventoryAndroidActions::getInventoryReservation($_POST["minid"], $_POST["length"]);
		}
		if(isset($_POST["getFinancialPeriod"])){
			InventoryAndroidActions::getFinancialPeriod();
		}
		
		if(isset($_POST["getOtherInfo"])){
			InventoryAndroidActions::getOtherInfo();
		}
		if(isset($_POST["getRacks"])){
			InventoryAndroidActions::getRacks($_POST["minid"], $_POST["length"]);
		}
		if(isset($_POST["getIssueType"])){
			InventoryAndroidActions::getIssueType($_POST["minid"], $_POST["length"]);
		}
		if(isset($_POST["getMeasureUnit"])){
			InventoryAndroidActions::getMeasureUnit($_POST["minid"], $_POST["length"]);
		}
		if(isset($_POST["getWarehouse"])){
			InventoryAndroidActions::getWarehouse($_POST["minid"], $_POST["length"]);
		}
		if(isset($_POST["getStaffs"])){
			InventoryAndroidActions::getStaffs($_POST["minid"], $_POST["length"]);
		}
		if(isset($_POST["getInvFLData"])){
			InventoryAndroidActions::getInventoryFIFOLIFOData($_POST["minid"], $_POST["length"]);
		}
		
		if(isset($_POST["getWorkOrders"])){
			InventoryAndroidActions::getWorkOrders($_POST["minid"], $_POST["length"]);
		}
	}	
}


AndroidRun::Run();

?>