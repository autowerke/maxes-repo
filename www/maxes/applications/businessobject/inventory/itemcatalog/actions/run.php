<?php 
require_once("actions.php");
header('Access-Control-Allow-Origin: *');
		
Class Action  {
	public static function Run(){
		if(isset($_POST["loaddata"])) DataLoader::Load();
		if(isset($_POST["downloadloader"])) DataLoader::DownloadLoader();
		

		
		if(Super::hasParam("getItemStructNum")) ActionSet::getItemStructNum();
		if(Super::hasParam("prodLogCharts")) ActionSet::prodLogCharts();
		if(Super::hasParam("addAttributes")) ActionSet::addAttributes();
		if(Super::hasParam("gendescription")) ActionSet::generateDescription();
		if(isset($_POST["requestitemnumber"])) ActionSet::requestitemnumber();	
		if(isset($_POST["addHazards"])) ActionSet::addHazards();	
		if(isset($_POST["retcustbal"])) ActionSet::ReturnCustomerBalance();		
		if(isset($_POST["issuereserverditems"])) ActionSet::IssueReservedItems();		
		if(isset($_POST["writeconsigninvoice"])) ActionSet::WriteConsignmentInvoice();		
		if(isset($_POST["setinvprice"])) ActionSet::SetInventoryPrice();
		if(isset($_POST["getconsigninvoice"])) Xbo::GetConsignmentInvoice();
		if(isset($_POST["setconsignqty"])) ActionSet::SetConsignmentQuantity();
		if(isset($_POST["AddClassification"])) ActionSet::AddClassification();
		if(isset($_POST["mapAddToInventory"])) ActionSet::mapAddToInventory();
		if(isset($_POST["AddToInventory"])) ActionSet::AddToInventory($_POST);
		if(isset($_POST["rfidtag"])) ActionSet::RFIDTag();
		if(isset($_POST["getBatchNum"])) ActionSet::getBatchNum();
		
		if(isset($_GET["itx"])) ActionSet::Itx();
		if(isset($_GET["updateexpqty"])) ActionSet::UpdateExpiredQuantity();
		if(isset($_GET["getwhgl"])) ActionSet::GetWarehouseGL();
		if(isset($_GET["transferItem"])) ActionSet::TransferItems();
		if(isset($_GET["defpricebalance"])) ActionSet::DefaultPriceBalance();
		if(isset($_GET["woglaccount"])) ActionSet::WorkorderGLAccount();
		
		//validate tables
		if(Super::hasParam("tbcx")) ValidateTables::tbcx();
		
	}	
}


Action::Run();

?>