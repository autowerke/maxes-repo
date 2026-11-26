<?php
require_once ("../../../eat.php");

Class ActionSet {
	
	public static function CheckTaxReceiptNum(){
		$crnum = $_POST["checktaxreceiptnum"];
		$msg = true;
		$set = new Set();
		$CRSet = $set->MboSetRemote("taxreceipt")->setWhere("cashreceiptnum='$crnum'")->MboRemote("taxreceiptnum")->getMboSet();
		$count = $CRSet->counts();
		if($count == 0){
			$msg = true;
		}else{
			for($i=0; $i<$count; $i++){
				$cr = $CRSet->getMbo();
				$trnum = $cr->getString("taxreceiptnum");
			
				if($trnum == ""){
					$msg = false;
					break;
				}else{
					$msg = true;
				}
			}
		}
		
		$ss = new stdClass();
		$ss->msg = $msg;
		Set::throws($ss);
	}
	public static function crimportexp(){
		$taxreceiptnum = $_POST["crimportexp"];
		$cashreceiptnum = $_POST["cashreceiptnum"];
		$importlines = $_POST["importlines"];
		$count = count($importlines);
		$totalexpense = 0;
		if($taxreceiptnum == "pettycash"){
			$table = "pettycash";
			
		}else{
			if($taxreceiptnum == "pettycashline"){
				$table = "pettycashline";
				for($i=0; $i<$count; $i++){
					$pt = explode("`", $importlines[$i]);
					$set = new Set();
					$pettycashlineSet = $set->MboSetRemote($table)->setWhere($pt[0]."=".$pt[1])->MboRemote("pettycashnum")->MboRemote("invoicenum")->MboRemote("invoicelinenum")->MboRemote("quantity")->MboRemote("unitcost")
					->MboRemote("linecost")->MboRemote("financialperiod")->MboRemote("gldebitacct")->MboRemote("glcreditacct")->getMboSet();
					$pc = $pettycashlineSet->getMbo();
					$pettycashnum = $pc->getString("pettycashnum");
					$invoicenum = $pc->getString("invoicenum");
					$invoicelinenum = $pc->getString("invoicelinenum");
					$quantity = $pc->getString("quantity");
					$unitcost = $pc->getString("unitcost");
					$linecost = $pc->getString("linecost");
					$financialperiod = $pc->getString("financialperiod");
					$gldebitacct = $pc->getString("gldebitacct");
					$glcreditacct = $pc->getString("glcreditacct");
					$totalexpense = $totalexpense+$linecost;
					
					$set2 = new Set();
					$set2->MboSetRemote("cashrecbalance")->setValue("cashreceiptnum", $cashreceiptnum)->setValue("pettycashnum", $pettycashnum)->setValue("invoicenum", $invoicenum)->setValue("invoicelinenum", $invoicelinenum)
					->setValue("quantity", $quantity)->setValue("unitcost", $unitcost)->setValue("linecost", $linecost)->setValue("financialperiod", $financialperiod)
					->setValue("gldebitacct", $gldebitacct)->setValue("glcreditacct", $glcreditacct)->add();
					
					$set3 = new Set();
					$set3->MboSetRemote($table)->setWhere($pt[0]."=".$pt[1])->setValue("isimported", "Y")->change();
				}
			}
		}
		Set::throws(["totalexpense"=>$totalexpense]);
	}
	
	public static function importcashout(){
		$cashreceiptnum = $_POST["importcashout"];
		$financialperiod = $_POST["financialperiod"];
		$openbalance = $_POST["openbalance"];
		$orgcode = $_POST["orgcode"];
		$sitecode = $_POST["sitecode"];
		
		$s = new Set();
		$s->MboSetRemote("cashoutline")->setWhere("cashreceiptnum='$cashreceiptnum'")->remove();
		$s->MboSetRemote("taxreceipt")->setWhere("cashreceiptnum='$cashreceiptnum' and taxtype='in' and invoicenum not in (select invoicenum from taxtrans)")->remove();
		$set = new Set();
		$whereorgcode = null;
		if($orgcode != null && $sitecode != null){
			$whereorgcode = " and invoice.orgcode='$orgcode' and invoice.sitecode='$sitecode'";
		}
		
		$invoicetransSet = $set->MboSetRemote("invoicetrans")
		->setRelationship("invoice", "invoicetrans.invoicenum = invoice.invoicenum")
		->setRelationship("invoiceline", "invoicetrans.invoicenum = invoiceline.invoicenum and invoicetrans.invoicelinenum = invoiceline.invoicelinenum")
		->setRelationship("invoicecharge", "invoicetrans.invoicenum = invoicecharge.invoicenum and invoicetrans.invoicelinenum = invoicecharge.invoicechargelinenum")
		->setWhere("(invoicetrans.sonum is null or invoice.customercode is not null) and (invoicetrans.ponum is not null or invoice.vendorcode is not null or invoicetrans.wonum is not null) and invoicetrans.financialperiod='$financialperiod' $whereorgcode")
		->MboRemote("invoicetrans.invoicenum")->MboRemote("invoicetrans.linetype")->MboRemote("invoicetrans.invoicelinenum")->MboRemote("invoicetrans.description")->MboRemote("invoicetrans.quantity")
		->MboRemote("invoiceline.orderunit")->MboRemote("invoicetrans.unitcost")->MboRemote("invoicetrans.linecost")
		->MboRemote("invoicetrans.financialperiod")->MboRemote("invoicetrans.gldebitacct")->MboRemote("invoicetrans.glcreditacct")
		->MboRemote("invoicetrans.transtype")->MboRemote("invoicetrans.financialperiod")->MboRemote("invoice.statusdate")
		->MboRemote("invoicecharge.taxreceiptnum")->getMboSet();
		
		
		
		$count = $invoicetransSet->counts();
		$enterdate = Xbo::setEnterDate();
		$totalcashout = 0;
		$totaltaxreceipt = 0;
		$paidlineprice = 0;
		
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
			$taxreceiptnum = $invoicetrans->getString("taxreceiptnum");
			if($linetype == "tax-in"){
				$setr = new Set();
				$taxReceiptSet = $setr->MboSetRemote("taxreceipt")->setWhere("invoicenum='$invoicenum'")->MboRemote("invoicenum")->MboRemote("status")->MboRemote("lineprice")->getMboSet();
				
				$countr = $taxReceiptSet->counts();
				$taxReceipt = $taxReceiptSet->getMbo();
				$taxstatus = $taxReceipt->getString("status");
				$linepricetax = $taxReceipt->getString("lineprice");
				if($linepricetax == null)$linepricetax =0;
				if($countr==0){
					$set2 = new Set();
					$set2->MboSetRemote("taxreceipt")->setValue("cashreceiptnum", $cashreceiptnum)->setValue("linetype",$linetype )->setValue("invoicenum", $invoicenum)->setValue("invoicelinenum",$invoicelinenum)
					->setValue("description", $description )->setValue("quantity", $quantity)->setValue("taxreceiptnum", $taxreceiptnum)->setValue("unitprice",$unitcost )
					->setValue("lineprice", $linecost)->setValue("financialperiod", $financialperiod)->setValue("gldebitacct",$gldebitacct)->setValue("glcreditacct",$glcreditacct )->setValue("transtype", $transtype)
					->setValue("statusdate2", $statusdate)->setValue("statusdate", $enterdate)->setValue("status", "paid")->setValue("taxtype", "in")->add();
					$totaltaxin = $totaltaxin+$linecost;	
				}else{
					if($taxstatus == "paid"){
						$paidlineprice = $paidlineprice+$linepricetax;
					}else{}
					$totaltaxin = $totaltaxin+$linecost;	
				}
				$totalcashout =0;
			}else{
				$set2 = new Set();
				$set2->MboSetRemote("cashoutline")->setValue("cashreceiptnum", $cashreceiptnum)->setValue("invoicenum", $invoicenum)->setValue("linetype",$linetype )->setValue("invoicelinenum",$invoicelinenum)
				->setValue("description", $description )->setValue("quantity", $quantity)->setValue("orderunit", $orderunit)->setValue("unitcost",$unitcost )
				->setValue("linecost", $linecost)->setValue("financialperiod", $financialperiod)->setValue("gldebitacct",$gldebitacct)->setValue("glcreditacct",$glcreditacct )->setValue("transtype", $transtype)
				->setValue("statusdate", $statusdate)->add();
				$totalcashout = $totalcashout+$linecost;
				$totaltaxin=0;
			}
			
		}
		Set::throws(["totalcashout"=>$totalcashout, "taxin"=>$totaltaxin]);
	}
	public static function importcashreceipt(){
		$cashreceiptnum = $_POST["importcashreceipt"];
		$financialperiod = $_POST["financialperiod"];
		$openbalance = $_POST["openbalance"];
		$orgcode = $_POST["orgcode"];
		$sitecode = $_POST["sitecode"];
		$s = new Set();
		$s->MboSetRemote("cashreceiptline")->setWhere("cashreceiptnum='$cashreceiptnum'")->remove();
		$s->MboSetRemote("taxreceipt")->setWhere("cashreceiptnum='$cashreceiptnum' and taxtype = 'out' and invoicenum not in (select invoicenum from taxtrans)")->remove();
		$set = new Set();
		$whereorgcode = null;
		if($orgcode != null && $sitecode != null){
			$whereorgcode = " and invoice.orgcode='$orgcode' and invoice.sitecode='$sitecode'";
		}
		$invoicetransSet = $set->MboSetRemote("invoicetrans")
		->setRelationship("invoice", "invoicetrans.invoicenum = invoice.invoicenum")
		->setRelationship("invoiceline", "invoicetrans.invoicenum = invoiceline.invoicenum and invoicetrans.invoicelinenum = invoiceline.invoicelinenum")
		->setRelationship("invoicecharge", "invoicetrans.invoicenum = invoicecharge.invoicenum and invoicetrans.invoicelinenum = invoicecharge.invoicechargelinenum")
		->setWhere("(invoicetrans.ponum is null or invoice.vendorcode is not null) and (invoicetrans.sonum is not null or invoice.customercode is not null) and invoicetrans.financialperiod='$financialperiod' $whereorgcode")
		->MboRemote("invoicetrans.invoicenum")->MboRemote("invoicetrans.linetype")->MboRemote("invoicetrans.invoicelinenum")->MboRemote("invoicetrans.description")->MboRemote("invoicetrans.quantity")
		->MboRemote("invoiceline.saleunit")->MboRemote("invoicetrans.unitprice")->MboRemote("invoicetrans.lineprice")
		->MboRemote("invoicetrans.financialperiod")->MboRemote("invoicetrans.gldebitacct")->MboRemote("invoicetrans.glcreditacct")
		->MboRemote("invoicetrans.transtype")->MboRemote("invoicetrans.financialperiod")->MboRemote("invoice.statusdate")->MboRemote("invoicecharge.taxreceiptnum")->getMboSet();
		
		$count = $invoicetransSet->counts();
		$enterdate = Xbo::setEnterDate();
		$totalcashreceipt = 0;
		$totaltaxreceipt = 0;
		$paidlineprice = 0;
		for($i=0; $i<$count; $i++){
			$invoicetrans = $invoicetransSet->getMbo();
			$invoicenum = $invoicetrans->getString("invoicenum");
			$linetype = $invoicetrans->getString("linetype");
			$invoicelinenum = $invoicetrans->getString("invoicelinenum");
			$description = $invoicetrans->getString("description");
			$quantity = $invoicetrans->getString("quantity");
			$saleunit = $invoicetrans->getString("saleunit");
			$unitprice = $invoicetrans->getString("unitprice");
			$lineprice = $invoicetrans->getString("lineprice");
			$financialperiod = $invoicetrans->getString("financialperiod");
			$gldebitacct = $invoicetrans->getString("gldebitacct");
			$glcreditacct = $invoicetrans->getString("glcreditacct");
			$transtype = $invoicetrans->getString("transtype");
			$financialperiod = $invoicetrans->getString("financialperiod");
			$statusdate = $invoicetrans->getString("statusdate");
			$taxreceiptnum = $invoicetrans->getString("taxreceiptnum");
			
			//echo "$linetype >> ";
			if($linetype == "tax-out"){
				$setr = new Set();
				$taxReceiptSet = $setr->MboSetRemote("taxreceipt")->setWhere("invoicenum='$invoicenum'")->MboRemote("invoicenum")->MboRemote("status")->MboRemote("lineprice")->getMboSet();
				$countr = $taxReceiptSet->counts();
				$taxReceipt = $taxReceiptSet->getMbo();
				$taxstatus = $taxReceipt->getString("status");
				$linepricetax = $taxReceipt->getString("lineprice");
				if($linepricetax == null)$linepricetax =0;
				if($countr==0){
					$set2 = new Set();
					$set2->MboSetRemote("taxreceipt")->setValue("cashreceiptnum", $cashreceiptnum)->setValue("linetype",$linetype )->setValue("invoicenum", $invoicenum)->setValue("invoicelinenum",$invoicelinenum)
					->setValue("description", $description )->setValue("quantity", $quantity)->setValue("taxreceiptnum", $taxreceiptnum)->setValue("unitprice",$unitprice )
					->setValue("lineprice", $lineprice)->setValue("financialperiod", $financialperiod)->setValue("gldebitacct",$gldebitacct)->setValue("glcreditacct",$glcreditacct )->setValue("transtype", $transtype)
					->setValue("statusdate2", $statusdate)->setValue("statusdate", $enterdate)->setValue("status", "wait")->setValue("taxtype", "out")->add();
					$totaltaxreceipt = $totaltaxreceipt+$lineprice;	
				}else{
					if($taxstatus == "paid"){
						$paidlineprice = $paidlineprice+$linepricetax;
					}else{}
					$totaltaxreceipt = $totaltaxreceipt+$lineprice;	
				}
				
			}else{
				$set2 = new Set();
				$set2->MboSetRemote("cashreceiptline")->setValue("cashreceiptnum", $cashreceiptnum)->setValue("invoicenum", $invoicenum)->setValue("linetype",$linetype )->setValue("invoicelinenum",$invoicelinenum)
				->setValue("description", $description )->setValue("quantity", $quantity)->setValue("saleunit", $saleunit)->setValue("unitprice",$unitprice )
				->setValue("lineprice", $lineprice)->setValue("financialperiod", $financialperiod)->setValue("gldebitacct",$gldebitacct)->setValue("glcreditacct",$glcreditacct )->setValue("transtype", $transtype)
				->setValue("statusdate", $statusdate)->add();
				$totalcashreceipt = $totalcashreceipt+$lineprice;
			}
			
		}
		
		$closebalancetax = $openbalance+$totalcashreceipt+$totaltaxreceipt;
		
		$setr = new Set();
		$cashreceiptSet = $setr->MboSetRemote("cashreceipt")->setWhere("cashreceiptnum='$cashreceiptnum'")->MboRemote("totalcashinbank")->MboRemote("totalcashinbank")->getMboSet();
		$cashreceipt = $cashreceiptSet->getMbo();
		$totalcashinbank = $cashreceipt->getString("totalcashinbank");
		$closebalance = $totalcashinbank-$paidlineprice;
		Set::throws(["paidlineprice"=>$paidlineprice, "closebalance"=>$closebalance, "totalcashreceipt"=>$totalcashreceipt, "totaltaxreceipt"=>$totaltaxreceipt, "closebalancetax"=>$closebalancetax]);
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
					$cashreceiptSet = $set->MboSetRemote("cashreceipt")->setWhere("cashreceiptnum='$cashreceiptnum'")->MboRemote("status")->getMboSet();
					$cashreceipt = $cashreceiptSet->getMbo();
					
					$status = $cashreceipt->getString("status");
					
					if($status == "close"){
						
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