<?php 
require_once("actions.php");
header('Access-Control-Allow-Origin: *');
		
Class Action  {


	public static function Run(){
		
		if(isset($_POST["ABCAnalysis"])) Charts::ABCAnalysis(); 
		if(isset($_POST["ROPAnalysis"])) Charts::ROPAnalysis(); 
		

		
		
		if(Super::hasParam("actionName")) {
			$actionName = Super::getParam("actionName");
			if($actionName == "getInvCart"){
				ActionSet::getInvCart();
			}
		}

		if(Super::hasParam("abc_stl")) ActionSet::abc_stl();
		if(Super::hasParam("executeAssetConversion")) ActionSet::executeAssetConversion();
		if(Super::hasParam("executeToolConversion")) ActionSet::executeToolConversion();

		if(Super::hasParam("getUnitCostConversion")) ActionSet::getUnitCostConversion();

		if(isset($_POST["assetconversion"])) ActionSet::AssetConversion(); 
		if(isset($_POST["toolconversion"])) ActionSet::ToolConversion(); 

		if(isset($_POST["applyStockingParamChange"])) ActionSet::applyStockingParamChange(); 
		
		if(isset($_POST["addToCartRecomorder"])) ActionSet::addToCartRecomorder(); 
		if(isset($_POST["generatePRNum"])) ActionSet::generatePRNum(); 
		if(isset($_POST["createprabc"])) ActionSet::createprabc(); 
		if(isset($_POST["addtoCart"])) ActionSet::addtoCart(); 
		if(isset($_POST["listItemStruct"])) ActionSet::listItemStruct();	
		
		if(isset($_POST["disassembleItem"])) ActionSet::disassembleItem();	
		if(isset($_POST["assembleItems"])) ActionSet::assembleItems();	
		if(isset($_POST["getMaxAssembleQty"])) ActionSet::getMaxAssembleQty();	
		if(isset($_POST["requestitemnumber"])) ActionSet::requestitemnumber();	
		if(isset($_POST["recomorder"])) ActionSet::recomorder();
		if(isset($_POST["updateInvReserveOnItemnum"])) ActionSet::updateInvReserveOnItemnum();
		
		if(isset($_POST["downloadloader"])) DataLoader::DownloadLoader();
		if(isset($_POST["changeconditiontype"])) ActionSet::ChangeConditionType();
		if(isset($_POST["stockopnamefl"])) ActionSet::StockOpnameFL();
		if(isset($_POST["selectui"])) ActionSet::SelectTransferUI();
		if(isset($_POST["transferfl"])) ActionSet::TransferItemFIFOLIFO();
		if(isset($_POST["loaddata"])) DataLoader::Load();
		if(isset($_POST["newbatchnum"])) ActionSet::NewBatchNum();
		if(isset($_POST["qrcodeinfo"])) ActionSet::QRCodeInfo();
		if(isset($_POST["synfifolifoadditem"])) ActionSet::SynFifoLifoAdditem();
		if(isset($_POST["chkcondition"])) ActionSet::CheckItemConditionEnabled();
		
		if(isset($_POST["issuecuritemui"])) LookupUI::IssueCurrentItem();
		if(isset($_POST["defpricebalance"])) LookupUI::DefaultPriceBalance();
		if(isset($_POST["transferItemUI"])) LookupUI::TransferItemUI();
		if(isset($_POST["changecondratecost2"])) ActionSet::ChangeConditionRateCost2();
		if(isset($_POST["changecondratecost"])) ActionSet::ChangeConditionRateCost();
		if(isset($_POST["getitemcondcost"])) ActionSet::GetItemConditionCost();
		if(isset($_POST["checkitemcost"])) ActionSet::CheckItemConditionCost();
		if(isset($_POST["rfidupdate"])) ActionSet::RFIDUpdate();
		if(isset($_POST["changeconversion"])) ActionSet::ChangeConversion();
		if(isset($_POST["multiconversions"])) ActionSet::MultiConversion();		
		if(isset($_POST["updaterfqline"])) ActionSet::UpdateRFQLine();	
		if(isset($_POST["retcustbal"])) ActionSet::ReturnCustomerBalance();		
		if(isset($_POST["issuereserverditems"])) ActionSet::issueReservedItemsForWorkOrder();	//Deprecated	
		//if(isset($_POST["issuereserverditems"])) ActionSet::IssueReservedItems();	//Deprecated	
		if(isset($_POST["writeconsigninvoice"])) ActionSet::WriteConsignmentInvoice();		
		if(isset($_POST["setinvprice"])) ActionSet::SetInventoryPrice();
		if(isset($_POST["getconsigninvoice"])) ActionSet::GetConsignmentInvoice();
		if(isset($_POST["setconsignqty"])) ActionSet::SetConsignmentQuantity();
		if(isset($_POST["invSellItem"])) ActionSet::SellItem();
		if(isset($_POST["transferItem"])) ActionSet::TransferItems();
		if(isset($_POST["checkiskit"])) ActionSet::CheckIsKit();
		if(isset($_POST["changeinspitems"])) ActionSet::ChangeInspectionStatus();
		if(isset($_POST["checkrefundinvoice"])) ActionSet::CheckRefundInvoice();
		if(isset($_POST["adjustphysicalcount"])) ActionSet::AdjustPhysicalCount();
		if(isset($_POST["reconcilebalance"])) ActionSet::ReconcileBalance();
		if(isset($_POST["costAdj"])) ActionSet::CostAdjustment();
		if(isset($_POST["costAdj2"])) ActionSet::CostAdjustment($_POST["costAdj2"]);
		
		if(isset($_POST["maximoUpdateCurrentBalance"])) XboSet::CurrentBalanceAdjustment();
		if(isset($_POST["curbalAdj"])) XboSet::CurrentBalanceAdjustment();
		if(isset($_POST["curbalAdj2"])) XboSet::CurrentBalanceAdjustment($_POST["curbalAdj2"]);
		
		if(isset($_POST["issuecuritem"])) ActionSet::quickIssueCurrentItem(); 

		if(isset($_POST["invflphyscountAdj"])) ActionSet::invflphyscountAdj(); 
		if(isset($_POST["invflcurbalAdj"])) ActionSet::invflcurbalAdj(); 
		if(isset($_POST["invflCostAdj"])) ActionSet::invflCostAdj(); 

		
		if(isset($_POST["mapreconcileCurrentQty"])) ActionSet::mapreconcileCurrentQty();
		if(isset($_POST["mapProcessReservedItems"])) ActionSet::mapProcessReservedItems();
		if(isset($_POST["mapIssueCurrentItem"])) ActionSet::mapIssueCurrentItem();
		if(isset($_POST["mapProcessReturnFIFOLIFO"])) ActionSet::mapProcessReturnFIFOLIFO();
		if(isset($_POST["mapPhysCountQty"])) ActionSet::mapPhysCountQty();
		if(isset($_POST["updateFifoLifoPhysQty"])) ActionSet::mapUpdateFifoLifoPhysQty();
		if(isset($_POST["mapTransferItems"]))ActionSet::mapTransferItems();
		
		if(isset($_POST["returnfifolifo"])) ActionSet::ReturnFIFOLIFO();
		if(isset($_POST["matusereturnfifolifo"])) ActionSet::MatuseReturnFIFOLIFO();
		
		if(Super::hasParam("getItemImages")) ActionSet::getItemImages();
		if(isset($_GET["updateexpqty"])) ActionSet::UpdateExpiredQuantity();		
		if(isset($_GET["woglaccount"])) ActionSet::WorkorderGLAccount();
		if(isset($_GET["assetglaccount"])) ActionSet::AssetGLAccount();
		if(isset($_GET["locglaccount"])) ActionSet::LocationGLAccount();		
		if(isset($_GET["initAction"])) ActionSet::InitAction();
		
		//validate tables
		if(Super::hasParam("tbcx")) ValidateTables::tbcx();
	}	
}


Action::Run();

?>