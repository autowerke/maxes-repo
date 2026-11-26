<?php
require_once ("../../../eat.php");

Class ActionSet {
	public static function SumPettyCash(){
		$pettycashnum = $_GET["sumpettycash"];
		$set = new Set();
		$InvoiceExpense = $set->MboSetRemote("pettycashline")->setWhere("pettycashnum='$pettycashnum'")->MboRemote("sum(linecost) as sumlinecost")->getMboSet()->getMbo()->getString("sumlinecost");
		if($InvoiceExpense==null) $InvoiceExpense = 0;
		
		$set = new Set();
		$otherExpense = $set->MboSetRemote("pettycashline2")->setWhere("pettycashnum='$pettycashnum'")->MboRemote("sum(linecost) as sumlinecost")->getMboSet()->getMbo()->getString("sumlinecost");
		if($otherExpense==null) $otherExpense = 0;
		
		$totalExpenses = $InvoiceExpense+$otherExpense;
		
		$set = new Set();
		$openingBalance =  $set->MboSetRemote("pettycash")->setWhere("pettycashnum='$pettycashnum'")->MboRemote("openbalance")->getMboSet()->getMbo()->getString("openbalance");
		
		$closebalance = $openingBalance-$totalExpenses;
		
		Set::throws(["openingBalance"=>$openingBalance, "totalcost"=>$InvoiceExpense, "totalothercost"=>$otherExpense, "totalexpense" => $totalExpenses, "closebalance"=>$closebalance]);
	}
	public static function getopbal(){
		$orgcode=  $_POST["getopbal"];
		$sitecode = $_POST["sitecode"];
		$finapp = $_POST["finapp"];
		$set = new Set();
		$whereorgcode =null;
		$whereorgcode2=null;
		if($orgcode != null && $sitecode != null) {
			$whereorgcode = "where orgcode='$orgcode' and sitecode='$sitecode'";
			$whereorgcode2 = "and  orgcode='$orgcode' and sitecode='$sitecode'";
		}
		if($finapp == "pettycash"){
			$opSet = $set->MboSetRemote("pettycash")->setWhere("financialperiod in (select max(financialperiod) from pettycash $whereorgcode) $whereorgcode2 ")
			->MboRemote("closebalance")->MboRemote("financialperiod")->getMboSet();
			$op = $opSet->getMbo();
			$closebalance = $op->getString("closebalance");
			if($closebalance == null) $closebalance=0;
			$financialperiod = $op->getString("financialperiod");
			$financialperiod2 = $financialperiod;
			if($financialperiod == null) {
				$financialperiod2 = Xbo::getFinancialPeriod();
			}else{
				if (strpos($financialperiod2, "12") !== false) {
					$financialperiod2 = $financialperiod2+89;
				}else{
					$financialperiod2 = $financialperiod2+1;
				}
			}
			
			Set::throws(["openbalance"=>$closebalance, "financialperiod"=>$financialperiod2]);
		}else{
			if($finapp == "cashreceipt"){
				$opSet = $set->MboSetRemote("cashreceipt")->setWhere("financialperiod in (select max(financialperiod) from cashreceipt $whereorgcode) $whereorgcode2 ")
				->MboRemote("closebalance")->MboRemote("financialperiod")->getMboSet();
				$op = $opSet->getMbo();
				$closebalance = $op->getString("closebalance");
				if($closebalance == null) $closebalance=0;
				$financialperiod = $op->getString("financialperiod");
				$financialperiod2 = $financialperiod;
				if($financialperiod == null) {
					$financialperiod2 = Xbo::getFinancialPeriod();
				}else{
					if (strpos($financialperiod2, "12") !== false) {
						$financialperiod2 = $financialperiod2+89;
					}else{
						$financialperiod2 = $financialperiod2+1;
					}
				}
				Set::throws(["openbalance"=>$closebalance, "financialperiod"=>$financialperiod2]);
			}
		}
	}
	public static function importpetcashexp(){
		
		$pettycashnum = $_POST["importpetcashexp"];
		$financialperiod = $_POST["financialperiod"];
		$openbalance = $_POST["openbalance"];
		$orgcode = $_POST["orgcode"];
		$sitecode = $_POST["sitecode"];
		$s = new Set();
		$s->MboSetRemote("pettycashline")->setWhere("pettycashnum='$pettycashnum'")->remove();
		$set = new Set();
		$whereorgcode = null;
		if($orgcode != null && $sitecode != null){
			$whereorgcode = " and invoice.orgcode='$orgcode' and invoice.sitecode='$sitecode'";
		}
		$invoicetransSet = $set->MboSetRemote("invoicetrans")->setRelationship("invoice", "invoicetrans.invoicenum = invoice.invoicenum")
		->setRelationship("invpaymentline", "invoicetrans.invoicenum = invpaymentline.invoicenum and invoicetrans.invoicelinenum = invpaymentline.invpaymentlinenum")
		->setWhere("invoicetrans.ponum is null and invoicetrans.sonum is null and invoicetrans.transtype='advpay' and invoicetrans.financialperiod='$financialperiod' $whereorgcode")
		->MboRemote("invoicetrans.invoicenum")->MboRemote("invoicetrans.linetype")->MboRemote("invoicetrans.invoicelinenum")->MboRemote("invoicetrans.description")->MboRemote("invoicetrans.quantity")
		->MboRemote("invpaymentline.orderunit")->MboRemote("invoicetrans.unitcost")->MboRemote("invoicetrans.linecost")
		->MboRemote("invoicetrans.financialperiod")->MboRemote("invoicetrans.gldebitacct")->MboRemote("invoicetrans.glcreditacct")
		->MboRemote("invoicetrans.transtype")->MboRemote("invoicetrans.financialperiod")->MboRemote("invoice.statusdate")->getMboSet();
		$count = $invoicetransSet->counts();
		
		$totalexpense = 0;
		for($i=0; $i<$count; $i++){
			$invoicetrans = $invoicetransSet->getMbo();
			$invoicenum = $invoicetrans->getString("invoicenum");
			$linetype = $invoicetrans->getString("linetype");
			$invoicelinenum = $invoicetrans->getString("invoicelinenum");
			$description = $invoicetrans->getString("description");
			$quantity = $invoicetrans->getString("quantity");
			$orderunit = $invoicetrans->getString("orderunit");
			$unitcost = $invoicetrans->getString("unitcost");
			$linecost = $invoicetrans->getString("linecost");
			$financialperiod = $invoicetrans->getString("financialperiod");
			$gldebitacct = $invoicetrans->getString("gldebitacct");
			$glcreditacct = $invoicetrans->getString("glcreditacct");
			$transtype = $invoicetrans->getString("transtype");
			$financialperiod = $invoicetrans->getString("financialperiod");
			$statusdate = $invoicetrans->getString("statusdate");
			
			$set2 = new Set();
			$set2->MboSetRemote("pettycashline")->setValue("pettycashnum", $pettycashnum)->setValue("invoicenum", $invoicenum)->setValue("linetype",$linetype )->setValue("invoicelinenum",$invoicelinenum)
			->setValue("description", $description )->setValue("quantity", $quantity)->setValue("orderunit", $orderunit)->setValue("unitcost",$unitcost )
			->setValue("linecost", $linecost)->setValue("financialperiod", $financialperiod)->setValue("gldebitacct",$gldebitacct)->setValue("glcreditacct",$glcreditacct )->setValue("transtype", $transtype)
			->setValue("statusdate", $statusdate)->add();
			$totalexpense = $totalexpense+$linecost;
		}
		$totalexpense = $totalexpense*-1;		
		$closebalance = $openbalance-$totalexpense;
		
		Set::throws(["closebalance"=>$closebalance, "totalcost"=>$totalexpense]);
	}
} 

Class ValidateTables {
	public static function tbcx(){
		$dl = explode("|", $_GET["tbcx"]);
		$set = new Set();
		if ($dl[0] == "cashreceipt") {
			if ($dl[2] == "cashrecbalance") {
				$colarr = explode("^", $dl[3]);
				for ($i = 0; $i < count($colarr); $i++) {
					$tableid = explode("`", $colarr[$i]) [1];
					$sets = new Set();
					$cashrecbalanceSet = $sets->MboSetRemote("cashrecbalance")->setWhere("cashrecbalanceid='$tableid'")->MboRemote("cashreceiptnum")->MboRemote("pettycashnum")->MboRemote("invoicenum")->MboRemote("invoicelinenum")->getMboSet();
					$cashrecbalance = $cashrecbalanceSet->getMbo();
					$cashreceiptnum = $cashrecbalance->getString("cashreceiptnum");
					$pettycashnum = $cashrecbalance->getString("pettycashnum");
					$invoicenum = $cashrecbalance->getString("invoicenum");
					$invoicelinenum = $cashrecbalance->getString("invoicelinenum");					
					
					$set = new Set();
					$status = $set->MboSetRemote("cashreceipt")->setWhere("cashreceiptnum='$cashreceiptnum'")->MboRemote("status")->getMboSet()->getMbo()->getString("status");
					if($status = "close"){
						Set::throws(["msg"=>"closestatus"]);
						return null;
					}else{
						$sets = new Set();					
						$sets->MboSetRemote("pettycashline")->setWhere("pettycashnum='$pettycashnum' and invoicenum='$invoicenum' and  invoicelinenum='$invoicelinenum'")
						->setValue("isimported", "N")->change();
					}
				}
				Set::throws(["msg" => "omdelete"]);
			}
		}
		
	}
}
	

?>