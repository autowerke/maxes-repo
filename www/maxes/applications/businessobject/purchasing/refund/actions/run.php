<?php 
require_once("actions.php");
header('Access-Control-Allow-Origin: *');
Class Action  {
	public static function Run(){
		if(isset($_POST["refundtrans2"])) ActionSet::WriteRefundSet2();
		if(isset($_POST["refundtrans"])) ActionSet::WriteRefundSet();
		if(isset($_GET["sumrefundcost"])) ActionSet::SumRefundCost();
		if(isset($_POST["validateqty"])) ActionSet::ValidateQty();
		if(isset($_POST["updaterefund"])) ActionSet::UpdateRefund();
		if(isset($_POST["variancerefund"])) ActionSet::VarianceRefund();
		if(isset($_POST["getItemRefund"])) ActionSet::getItemRefund();
		if(isset($_GET["getTaxValue"])) ActionSet::getTaxValue($_GET["getTaxValue"], "print");
		if(isset($_GET["refunditemisexist"])) ActionSet::RefundItemIsExist();
		
		if(isset($_GET["tbcx"])) ValidateTables::tbcx();
	}	
}
Action::Run();

?>