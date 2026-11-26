<?php
require_once ("../../../eat.php");

Class ActionSet {
	public static function ImportTransactions(){
		$ledgernum = $_POST["importtrans"];
		$startdate = $_POST["startdate"];
		$enddate = $_POST["enddate"];
		
		$set = new Set();
		$startdate =Super::whereDate("enterdate", $startdate, ">=");
		$enddate =Super::whereDate("enterdate", $enddate, "<=");
		$invoicetransSet = $set->MboSetRemote("invoicetrans")->setWhere("transtype is not null $startdate $enddate")->getMboSet();
		$cnt = $invoicetransSet->counts();
		$lset = new Set();
		$lset->MboSetRemote("ledgerline")->setWhere("ledgernum='$ledgernum'")->remove();
		for($i=0; $i<$cnt; $i++){
			$invt = $invoicetransSet->getMbo();
			$invoicenum = $invt->getString("invoicenum");
			$ponum = $invt->getString("ponum");
			$sonum = $invt->getString("sonum");
			$wonum = $invt->getString("wonum");
			$costdate = $invt->getString("costdate");
			$customercode = $invt->getString("customercode");
			$linetype = $invt->getString("linetype");
			$invoicelinenum = $invt->getString("invoicelinenum");
			$itemnum = $invt->getString("itemnum");
			$description = $invt->getString("description");
			$exchangerate = $invt->getString("exchangerate");
			$quantity = $invt->getString("quantity");
			$unitprice = $invt->getString("unitprice");
			$lineprice = $invt->getString("lineprice");
			$taxprice = $invt->getString("taxprice");
			$unitcost = $invt->getString("unitcost");
			$linecost = $invt->getString("linecost");
			$taxcost = $invt->getString("taxcost");
			$unitprofit = $invt->getString("unitprofit");
			$lineprofit = $invt->getString("lineprofit");
			$unitpricediscount = $invt->getString("unitpricediscount");
			$linepricediscount = $invt->getString("linepricediscount");
			$unitprofitdiscount = $invt->getString("unitprofitdiscount");
			$lineprofitdiscount = $invt->getString("lineprofitdiscount");
			$invoicedate = $invt->getString("invoicedate");
			$transtype = $invt->getString("transtype");
			$gldebitacct = $invt->getString("gldebitacct");
			$glcreditacct = $invt->getString("glcreditacct");
			$financialperiod = $invt->getString("financialperiod");
			$enterdate = $invt->getString("enterdate");
			$enterby = $invt->getString("enterby");
			$sonum = $invt->getString("sonum");
			$ponum = $invt->getString("ponum");
			$wonum = $invt->getString("wonum");
			
			//SALES
			
			$ledgerlinenum = Xbo::getLineNumber("ledgerline", "ledgerlinenum", "ledgernum='$ledgernum'");
			$lset2 = new Set();
			if ($ponum == null && $wonum == null){
				if($sonum == null) {
					$refnum = $customercode;
					$reftype = "DIRECTSALES";
				}else{
					$refnum = $sonum;
					$reftype = "SOSALES";
				}
				if($lineprice > 0 ){
					if($linetype == "item" || $linetype == "services"){
						$lset2->MboSetRemote("ledgerline")->setValue("ledgernum", $ledgernum)->setValue("ledgerlinenum", $ledgerlinenum)
						->setValue("refnum", $refnum)->setValue("reftype", $reftype)->setValue("refdescription",$description)->setValue("ref",$transtype)
						->setValue("cost", $linepricediscount)->setValue("enterdate", $enterdate)
						->setValue("enterby", $enterby)->setValue("financialperiod", $financialperiod)->add();
					}else{
						if($linetype == "tax"){
							$lset2->MboSetRemote("ledgerline")->setValue("ledgernum", $ledgernum)->setValue("ledgerlinenum", $ledgerlinenum)
							->setValue("refnum", $refnum)->setValue("reftype", $reftype)->setValue("refdescription",$description)->setValue("ref","so-tax")
							->setValue("cost", $lineprice)->setValue("enterdate", $enterdate)
							->setValue("enterby", $enterby)->setValue("financialperiod", $financialperiod)->add();
						}
					}
				}
			}else{
				if($ponum == null) {
					$refnum = $wonum;
					$reftype = "CASHREC";
				}else{
					$refnum = $ponum;
					$reftype = "PO";
				}
				
				if($linecost < 0 ){
					if($linetype == "item" || $linetype == "services"){
						$lset2->MboSetRemote("ledgerline")->setValue("ledgernum", $ledgernum)->setValue("ledgerlinenum", $ledgerlinenum)
						->setValue("refnum", $refnum)->setValue("reftype", $reftype)->setValue("refdescription",$description)->setValue("ref",$transtype)
						->setValue("cost", $linecost)->setValue("enterdate", $enterdate)
						->setValue("enterby", $enterby)->setValue("financialperiod", $financialperiod)->add();
					}else{
						if($linetype == "tax"){
							if($linetype == "tax"){
								$lset2->MboSetRemote("ledgerline")->setValue("ledgernum", $ledgernum)->setValue("ledgerlinenum", $ledgerlinenum)
								->setValue("refnum", $refnum)->setValue("reftype", $reftype)->setValue("refdescription",$description)->setValue("ref","po-tax")
								->setValue("cost", $linecost)->setValue("enterdate", $enterdate)
								->setValue("enterby", $enterby)->setValue("financialperiod", $financialperiod)->add();
							}
						}
					}
				}
			}
		}
		Set::throws(["msg"=>"ok"]);
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