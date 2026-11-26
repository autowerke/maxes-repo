<?php 
require_once("actions.php");
header('Access-Control-Allow-Origin: *');
		
Class Action  {
	public static function Run(){
		
		if(Super::hasParam("deliverItemSales")) ActionSet::deliverItemSales();
		if(Super::hasParam("returnItemSales")) ActionSet::returnItemSales();
		if(Super::hasParam("deliverServiceSales")) ActionSet::deliverServiceSales();
		if(Super::hasParam("returnServiceSales")) ActionSet::returnServiceSales();

		if(Super::hasParam("getPriceCodeValue")) ActionSet::getPriceCodeValue();
		if(Super::hasParam("getWoGLAccount")) ActionSet::getWoGLAccount();
		if(Super::hasParam("getItemOrderInfo")) ActionSet::getItemOrderInfo();
		if(Super::hasParam("getServiceOrderInfo")) ActionSet::getServiceOrderInfo();

		if(Super::hasParam("validateStatusChange")) ActionSet::validateStatusChange();

		if(isset($_GET["validateshowchildren"])) ActionSet::ValidateShowChildren();
		if(Super::hasParam("sumsocost")) ActionSet::SumSOCost();
		if(isset($_GET["defaultsalesrep"])) ActionSet::DefaultSalesRepresentative();
		if(isset($_GET["validatediscountprice"])) ActionSet::validatediscountprice();	
		if(isset($_GET["getCostTypeValue"])) ActionSet::getCostTypeValue();	
		if(isset($_GET["validateSellItem"])) ActionSet::validateSellItem();
		if(isset($_GET["issolineqtyexist"])) ActionSet::IsSOLineQtyExist();
		if(isset($_GET["getTaxValue"])) ActionSet::getTaxValue();
		
		if(isset($_POST["createwofromservicedistribution"])) ActionSet::CreateWOFromServiceDistribution(); 
		if(isset($_POST["assignwoforsoline"])) ActionSet::CreateWOforSOLine();
		if(isset($_POST["createpo"])) ActionSet::CreatePO();
		if(isset($_POST["addAdditionalRequestFromInvoice"])) ActionSet::addAdditionalRequestFromInvoice();
		if(isset($_POST["createwo"])) ActionSet::CreateWO();
		if(isset($_POST["getcustglaccount"])) ActionSet::GetCustomerGLAccount();
		if(isset($_POST["updatesolinegl"])) ActionSet::UpdateSOLineGL();
		if(isset($_POST["postexpense"])) ActionSet::PostExpense();
		if(isset($_POST["unitpricecode"]) && isset ($_POST["sellqty"])) ActionSet::CalculateUnitPrice();
		if(isset($_POST["sellqty"]) && 
				!isset($_POST["sochangeorderqty"]) && 
				!isset($_POST["unitpricecode"]) &&
				!isset($_POST["sochangeorderserv"])
			) ActionSet::SellQty();
		if(isset($_POST["sorectrans"])) ActionSet::SORectrans();
		if(isset($_POST["solditems"])) ActionSet::SoldItems();
		if(isset($_POST["vri"])) ActionSet::VRI();		
		if(isset($_POST["sochangeorderqty"]) && isset ($_POST["sellqty"])) ActionSet::SOChangeOrderQty();
		if(isset($_POST["sochangeorderserv"])) ActionSet::SOChangeOrderService();
		
		//validate tables
		if(Super::hasParam("tbcx")) ValidateTables::tbcx();
	}	
}


Action::Run();

?>