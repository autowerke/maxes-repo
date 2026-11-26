<?php 
require_once("actions.php");
header('Access-Control-Allow-Origin: *');
		
Class Action  {
	public static function Run(){
		if(isset($_POST["writedatabilling"])) ActionSet::WriteDataBilling();
		if(isset($_POST["domainurl"])) ActionSet::getDomainData();
		if(isset($_POST["rollbackbilling"])) ActionSet::RollBackBilling();
		if(isset($_POST["validatebilling"])) ActionSet::ValidateBilling();
		if(isset($_POST["billingitems"])) ActionSet::WriteBillings(); 
		if(isset($_POST["billingitemsfl"])) ActionSet::WriteBillingsFL(); 
		if(isset($_POST["validatestock"])) ActionSet::ValidateStock();
		if(isset($_POST["approvebilling"])) ActionSet::ApproveBilling();
		if(isset($_GET["sumbillingcost"])) ActionSet::SumBillingCost();
		if(isset($_GET["getTaxValue"])) ActionSet::getTaxValue();
		if(isset($_GET["tbcx"])) ValidateTables::tbcx();
	}	
}
Action::Run();

?>