<?php
require_once ("../../../eat.php");

Class ActionSet {
	
	public static function UpdateTaxPayment(){
		$taxreceiptnum = $_POST["updtaxpayment"];
		XboSet::TaxPayment($taxreceiptnum);
		$a = new Super();
		$b = new stdClass();
		$b->table = "taxreceipt";
		$b->keynum = "taxreceiptnum";
		$b->newstatus = "paid";
		$b->remarks = $remarks;
		$b->colname = "taxreceiptnum";
		$b->colvalue = $taxreceiptnum;
		$b->statusdate = $statusdate;
		$b->tablestatus = "taxreceiptstatus";
		$a->changeStatusRecord($b);
		
		$set2 = new Set();
		$tcSet = $set2->MboSetRemote("taxreceipt")->setWhere("taxreceiptnum='$taxreceiptnum'")->MboRemote("cashreceiptnum")->getMboSet();
		$tc = $tcSet->getMbo();
		$cashreceiptnum = $tc->getString("cashreceiptnum");
		$v = XboSet::sumcrcost($cashreceiptnum);
		$totalexpense = $v->totalexpense;
		$closebalance = $v->closebalance;
		$paidlineprice = $v->paidlineprice;
		$additionalcost = $v->additionalcost;
		$totalcashinbank = $v->totalcashinbank;
		$variancecost = $v->variancecost;
		$variancecost2 = $v->variancecost2;
		$closebalance2 = $v->closebalance;
		
		$set3 = new Set();
		$set3->MboSetRemote("cashreceipt")->setWhere("cashreceiptnum='$cashreceiptnum'")->setValue("totalexpense", $totalexpense)->setValue("closebalance", $closebalance)
		->setValue("paidlineprice", $paidlineprice)->setValue("additionalcost", $additionalcost)->setValue("totalcashinbank", $totalcashinbank)->setValue("variancecost", $variancecost)
		->setValue("variancecost2", $variancecost2)->setValue("closebalance2", $closebalance2)->change();
		
	}
	public static function UpdateTaxGroupPayment(){
		$taxgroupnum = $_POST["updtaxgrouppayment"];
		$set = new Set();
		$enterdate = Xbo::setEnterDate();
		$enterby = Xbo::getLoginUser();
		
		$taxgrouplineSet = $set->MboSetRemote("taxgroupline")->setRelationship("taxgroup", "taxgroupline.taxgroupnum = taxgroup.taxgroupnum")
		->setRelationship("taxgroupstatus", "taxgroupstatus.taxgroupnum = taxgroup.taxgroupnum")
		->setWhere("taxgroupline.taxgroupnum='$taxgroupnum'")
		->MboRemote("taxreceiptnum")->MboRemote("taxgroup.statusdate")
		->MboRemote("taxgroup.financialperiod")
		->MboRemote("remarks")
		->MboRemote("cashreceiptnum")->getMboSet("distinct");
		
		$counts = $taxgrouplineSet->counts();
		$financialperiod = null;
		for($i=0; $i<$counts; $i++){
			$taxgroupline = $taxgrouplineSet->getMbo();
			$taxreceiptnum = $taxgroupline->getString("taxreceiptnum");
			$statusdate = $taxgroupline->getString("statusdate");
			$remarks = $taxgroupline->getString("remarks");
			$financialperiod = $taxgroupline->getString("financialperiod");
			$cashreceiptnum = $taxgroupline->getString("cashreceiptnum");
			XboSet::TaxPayment($taxreceiptnum);
			
			$a = new Super();
			$b = new stdClass();
			$b->table = "taxreceipt";
			$b->keynum = "taxreceiptnum";
			$b->newstatus = "paid";
			$b->remarks = $remarks;
			$b->colname = "taxreceiptnum";
			$b->colvalue = $taxreceiptnum;
			$b->statusdate = $statusdate;
			$b->tablestatus = "taxreceiptstatus";
			$a->changeStatusRecord($b);
			$v = XboSet::sumcrcost($cashreceiptnum);
			$totalexpense = $v->totalexpense;
			$closebalance = $v->closebalance;
			$paidlineprice = $v->paidlineprice;
			$additionalcost = $v->additionalcost;
			$totalcashinbank = $v->totalcashinbank;
			$variancecost = $v->variancecost;
			$variancecost2 = $v->variancecost2;
			$closebalance2 = $v->closebalance;
			
			$set3 = new Set();
			$set3->MboSetRemote("cashreceipt")->setWhere("cashreceiptnum='$cashreceiptnum'")->setValue("totalexpense", $totalexpense)->setValue("closebalance", $closebalance)
			->setValue("paidlineprice", $paidlineprice)->setValue("additionalcost", $additionalcost)->setValue("totalcashinbank", $totalcashinbank)->setValue("variancecost", $variancecost)
			->setValue("variancecost2", $variancecost2)->setValue("closebalance2", $closebalance2)->change();
		}
	}
	public static function ImportTax(){
		$taxgroupnum = $_POST["crimporttax"];
		$importlines = $_POST["importlines"];
		$count = count($importlines);
		$totalamount = 0;
		$taxgrouplinenum=0;
		for($i=0; $i<$count; $i++){
			$taxgrouplinenum = $taxgrouplinenum+10;
			$pt = explode("`", $importlines[$i]);
			$set = new Set();
			$taxrecSet = $set->MboSetRemote("taxreceipt")->setWhere($pt[0]."=".$pt[1])->MboRemote("taxreceiptnum")->MboRemote("lineprice")->getMboSet();
			$taxrec = $taxrecSet->getMbo();
			$taxreceiptnum = $taxrec->getString("taxreceiptnum");
			$lineprice = $taxrec->getString("lineprice");
			$totalamount = $totalamount+$lineprice;
			$set2 = new Set();
			$set2->MboSetRemote("taxgroupline")->setValue("taxgroupnum", $taxgroupnum)->setValue("taxreceiptnum", $taxreceiptnum)->setValue("taxgrouplinenum", $taxgrouplinenum)->add();
			$set3 = new Set();
			$set3->MboSetRemote("taxreceipt")->setWhere($pt[0]."=".$pt[1])->setValue("taxgroupnum", $taxgroupnum)->change();
		}
		
		Set::throws(["totalamount"=>$totalamount]);
	}
} 

Class ValidateTables {
	public static function tbcx(){
		$dl = explode("|", $_GET["tbcx"]);
		$set = new Set();
		if ($dl[0] == "taxgroup") {
			if ($dl[2] == "taxgroupline") {
				$colarr = explode("^", $dl[3]);
				for ($i = 0; $i < count($colarr); $i++) {
					$tableid = explode("`", $colarr[$i]) [1];
					$sets = new Set();
					$taxgrouplineSet = $sets->MboSetRemote("taxgroupline")->setWhere("taxgrouplineid='$tableid'")->MboRemote("taxreceiptnum")->getMboSet();
					$taxgroupline = $taxgrouplineSet->getMbo();
					$taxreceiptnum = $taxgroupline->getString("taxreceiptnum");
					
					$sets = new Set();
					$sets->MboSetRemote("taxreceipt")->setWhere("taxreceiptnum='$taxreceiptnum'")->setValue("taxgroupnum", null)->change();
				}
				Set::throws(["msg" => "omdelete"]);
			}
		}
	}
}
	

?>