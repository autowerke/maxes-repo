<?php 
require_once("actions.php");
require_once("../../salesorders/actions/run.php");
require_once("../../purchaseorders/actions/run.php");
header('Access-Control-Allow-Origin: *');
		
Class InvoiceAction  {
	public static function Run(){
		if(Super::hasParam("importWO")) InvoiceActionSet::importWO();
		if(Super::hasParam("validateStatusChangeForInvoice")) InvoiceActionSet::validateStatusChangeForInvoice();
		if(isset($_POST["recalculatePaymentTerm"])) InvoiceActionSet::RecalculatePaymentTerm();
		if(isset($_POST["addgetglcreditacct"])) InvoiceActionSet::GetGLCreditAcct();
		if(isset($_POST["addDonation"])) InvoiceActionSet::addDonation();
		if(isset($_POST["changesalesman"])) InvoiceActionSet::changesalesman();
		if(isset($_POST["getCustomerBank"])) InvoiceActionSet::getCustomerBank();
		if(isset($_POST["getCustomerBankAccount"])) InvoiceActionSet::getCustomerBankAccount();
		if(isset($_POST["addothercharge"])) InvoiceActionSet::AddOtherCharge();
		if(isset($_POST["othercharges"])) InvoiceActionSet::othercharges();
		if(isset($_POST["applycommissionprice"])) InvoiceActionSet::ApplyCommissionPrice();
		if(isset($_POST["changecommissionpercent"]) || isset($_POST["changecommissionprice"])) InvoiceActionSet::ChangeCommission();
		if(isset($_POST["viewcommission"])) InvoiceActionSet::ViewCommission();
		if(isset($_POST["importbillings"])) InvoiceActionSet::ImportBillings();
		if(isset($_POST["previewdiscount"])) InvoiceActionSet::PreviewDiscount();
		if(isset($_POST["applydiscount"])) InvoiceActionSet::ApplyDiscount();
		if(isset($_POST["matsaleimport"])) InvoiceActionSet::ImportMaterialSales();
		if(isset($_POST["writeinvline"])) InvoiceActionSet::WriteInvoiceSet();
		if(isset($_POST["applytermtable"])) InvoiceActionSet::ApplyTermTables();
		if(isset($_POST["updatepayment"])) InvoiceActionSet::UpdatePayment();
		if(isset($_GET["isconsignrow"])) InvoiceActionSet::IsConsignmentRecord();
		if(isset($_GET["validateinvoiceline"])) InvoiceActionSet::ValidateInvoiceLines();
		if(isset($_GET["suminvoicecost"])) InvoiceActionSet::SumInvoiceCost();
		if(isset($_GET["validatelncostinvoiced"])) InvoiceActionSet::ValidateInvoicedCost();
		if(isset($_GET["validateqtyinvoiced"])) InvoiceActionSet::ValidateInvoicedQuantity();
		
		//validate tables
		if(Super::hasParam("invoice_tbcx")) {
			InvoiceValidateTables::invoice_tbcx();
		}
	}	
}


InvoiceAction::Run();

?>