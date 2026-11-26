<?php

Class CommonInventory{
	public static function AdjustFIFOLIFO($fl){
		$itemnum = $fl->itemnum;
		$warehouse = $fl->warehouse;
		$conditiontype = $fl->conditiontype;
		$racknum = $fl->racknum;
		$conditionrate = $fl->conditionrate;
		$newCurbal = $fl->newcurbal;
		$stdcost = $fl->stdcost;
		$avgcost = $fl->avgcost;
		$financialperiod = $fl->financialperiod;
		$costdate = $fl->costdate;
		$orgcode = $fl->orgcode;
		$sitecode = $fl->sitecode;
		$transtype = $fl->transtype;
		$transdate = Xbo::setEnterDate();
		$enterby = Xbo::getLoginUser();
		$gldebitacct = $fl->gldebitacct;
		$glcreditacct = $fl->glcreditacct;
		$whereexpireddate = $fl->whereexpireddate;
		$batchnum = $fl->batchnum;

		if(empty($costdate)) $costdate = Xbo::setEnterDate();
		if(empty($batchnum)) $batchnum = Xbo::GetBatchNum();
		
		if (empty($conditiontype)) $condval = " and (conditiontype='' or conditiontype is null)";
		else $condval = " and conditiontype='$conditiontype'";
		
		if (empty($racknum)) $rackval = "and (racknum ='' or racknum is null) ";
		else $rackval = "and racknum='$racknum'";
				
		$fifolifowc = "itemnum='$itemnum' and warehouse='$warehouse' and orgcode='$orgcode' and sitecode='$sitecode' $condval $rackval";
		$transdel = null;
		if($transtype == "CURBALADJ") $transdel = "RECBALADJ";
		else $transdel = "CURBALADJ";
			
		$newcostfifo=$avgcost;
		$whereclause = "itemnum='$itemnum' and warehouse='$warehouse' $condval $rackval";
		$invfifolifocostSet = (new Set())->useTransactionManager()->MboSetRemote("invfifolifocost")->setWhere($whereclause)->getMboSet();
		$cntfifo = $invfifolifocostSet->counts();
		
		
		if($cntfifo == 0){
			
			(new Set())->useTransactionManager()->MboSetRemote("invfifolifocost")->setValue("itemnum", $itemnum)->setValue("conditiontype", $conditiontype)
			->setValue("conditionrate", $conditionrate)->setValue("costdate", $costdate)->setValue("financialperiod", $financialperiod)->setValue("unitcost", $newcostfifo)
			->setValue("quantity", $newCurbal)->setValue("batchnum", $batchnum)
			->setValue("warehouse", $warehouse)->setValue("racknum", $racknum)->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)
			->setValue("transtype", $transtype)->setValue("transdate", $transdate)->add();

			(new Set())->useTransactionManager()->MboSetRemote("invfifolifocost")->setWhere($whereclause. " and transtype='$transdel'")->remove();	
			
			$oldquantity = 0;
			$varianceqty = $newCurbal-$oldquantity;
			$oldcost = $oldquantity*$newcostfifo;
			$newcost = $newCurbal*$newcostfifo;
			$variancecost = $newcost-$oldcost;
			
			if($oldcost != $newcost){
				(new Set())->useTransactionManager()->MboSetRemote("invtrans")->setValue("itemnum", $itemnum)->setValue("transtype", "FIFOLIFOADJ")->setValue("warehouse", $warehouse)->setValue("racknum", $racknum)
				->setValue("conditiontype", $conditiontype)->setValue("quantity", $newCurbal)->setValue("oldquantity", $oldquantity)
				->setValue("cost", $newcost)->setValue("oldcost", $oldcost)->setValue("varianceqty", $varianceqty)
				->setValue("variancecost", $variancecost)->setValue("stdcost", $stdcost)->setValue("avgcost", $avgcost)
				->setValue("gldebitacct", $gldebitacct)->setValue("glcreditacct", $glcreditacct)
				->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)->setValue("enterby", $enterby)->setValue("enterdate", $transdate)
				->setValue("financialperiod", $financialperiod)->add();
			}
		}else{
			/*
			(new Set())->useTransactionManager()->MboSetRemote("invfifolifocost")->setWhere($fifolifowc)->remove();
			$costdate = Xbo::setEnterDate();
			(new Set())->useTransactionManager()->MboSetRemote("invfifolifocost")
			->setValue("itemnum", $itemnum)->setValue("conditiontype", $conditiontype)->setValue("costdate", $costdate)
			->setValue("financialperiod", $financialperiod)->setValue("unitcost", $newcostfifo)->setValue("quantity", $newCurbal)->setValue("racknum", $racknum)
			->setValue("conditionrate", $conditionrate)->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)->setValue("warehouse", $warehouse)
			->setValue("batchnum", $batchnum)
			->add();
			*/
			(new Set())->useTransactionManager()->MboSetRemote("invfifolifocost")->setWhere($whereclause)
			->setValue("unitcost", $newcostfifo)->setValue("quantity", $newCurbal)->change();
		}
	}
	
	public static function IssuedFIFOLIFO($fl){
			
		$tablename = $fl->tablename;
		$transtype = $fl->transtype;
		$itemnum = $fl->itemnum;
		$warehouse = $fl->warehouse;
		$conditiontype = $fl->conditiontype;
		$racknum = $fl->racknum;
		$issuedqty = $fl->issuedqty;
		if($issuedqty<0) $issuedqty = $issuedqty*-1;
		$condval = $fl->condval;
		$rackval = $fl->rackval;
		if(isset($fl->unitcommission)) {
			$unitcommission = $fl->unitcommission;
			if(empty($unitcommission)) $unitcommission =0;
		}
		else { 
			$unitcommission = 0; 
		}
		
		
		if(isset($fl->costtype)){
			$costtype = $fl->costtype;
			if($costtype == "AVG"){
				if($tablename == "matsaletrans"){
					$avgtrans = $fl->avgtrans->setValue("issuetype", "AVG")->setValue("fromrack", $racknum);
				}else{
					if($tablename == "matusetrans"){
						$avgtrans = $fl->avgtrans->setValue("issuetype", "AVG");
					}else{
						$avgtrans = $fl->avgtrans->setValue("issuetype", "AVG");
					}
				}
				$avgunitcost = $fl->unitcost;
			}
		}else{
			$avgtrans = $fl->avgtrans;
		}
		
		$fifotrans = $fl->fifotrans;
		
		if(isset($fl->fifowhereclause)) $fifowhereclause = $fl->fifowhereclause; 
		else $fifowhereclause = "itemnum='$itemnum' and warehouse='$warehouse' $condval and quantity>0";
		
		if(isset($fl->unittaxprice)){
			$unittaxprice = $fl->unittaxprice;
		}else{
			$taxprice = 0;
			$unittaxprice = 0;
		}
		$set = new Set();
		$invSet = $set->MboSetRemote("inventory")->setWhere("itemnum='$itemnum' and warehouse='$warehouse'")->MboRemote("issuetype")->getMboSet();
		$inv = $invSet->getMbo();
		$issuetype = $inv->getString("issuetype");
		
		if(isset($fl->costtype))  {
			$issuetype = $fl->costtype;
		}
		$whatissuetype = $issuetype;
		if(empty($whatissuetype)) {
			$whatissuetype = "FIFO";
			$issuetype = "FIFO";
		}
		$flag = 0;
	
		if(empty($issuetype)){
			$issuetype == "FIFO";
			$flag =1;
			$avgtrans->add();
		}
		
		if($issuetype == "FIFO"){
			$orderby = "costdate asc";
		}else{
			if($issuetype == "LIFO") {
				$orderby = "costdate desc";
			}else{
				if($issuetype == "EXPDATE"){
					$orderby = "expdate asc";
				}else{
					$orderby = "costdate asc";
				}
			}
		}
		//CHECK EXPDATE IF NULL
		if($issuetype == "EXPDATE"){
			$set = new Set();
			$cekexpdate = " and (expdate is null or expdate='')";
			$expSet = $set->MboSetRemote("invfifolifocost")->setWhere($fifowhereclause.$cekexpdate)->MboRemote("itemnum")->getMboSet();
			$countExpSet = $expSet->counts();
			if($countExpSet > 0){
				return "expdateisnull";
			}
		}
		$set = new Set();
		if($tablename == "matrectrans"){
			$ifSet = $set->MboSetRemote("invfifolifocost")->order("$orderby")->setWhere($fifowhereclause);
			$iflset = $ifSet->MboRemote("vendorcode")->MboRemote("quantity")->MboRemote("costdate")->MboRemote("unitcost")
			->MboRemote("batchnum")->MboRemote("expdate")->MboRemote("invfifolifocostid")->getMboSet();
		}else{
			$ifSet = $set->MboSetRemote("invfifolifocost")->order("$orderby")->setWhere($fifowhereclause);
			$iflset = $ifSet->MboRemote("vendorcode")->MboRemote("quantity")->MboRemote("costdate")->MboRemote("unitcost")
			->MboRemote("batchnum")->MboRemote("expdate")->MboRemote("invfifolifocostid")->getMboSet();
		}
		
		$cnt = $iflset->counts();
		
		$set2 = new Set();
		$sumifSet = $set2->MboSetRemote("invfifolifocost")->order("$orderby")->setWhere($fifowhereclause)
					->MboRemote("sum(quantity) as totalqty")->getMboSet();
					
		$sumif = $sumifSet->getMbo();
		$totalqty = $sumif->getString("totalqty");	
		
		if($totalqty == null) $totalqty = 0;
		$ar = [];
			
		if($cnt >0){
		
			if($totalqty >= $issuedqty){
				
				for ($i=0; $i<$cnt; $i++){
					
					$ch = new stdClass();
					$ifl = $iflset->getMbo();
					$quantity = $ifl->getString("quantity");
					$costdate = $ifl->getString("costdate");
					$unitcost = $ifl->getString("unitcost");
					$batchnum = $ifl->getString("batchnum");
					$expireddate = $ifl->getString("expdate");
					$invfifolifocostid = $ifl->getString("invfifolifocostid");
					
					$wheredate = Super::whereDate("costdate", $costdate);
					$u = new Set();
					$rackSet = $u->MboSetRemote("invfifolifocost")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' $wheredate")->MboRemote("racknum")->getMboSet();
					$rack = $rackSet->getMbo();
					$newrack = $rack->getString("racknum");
					
					if($issuetype == "FIFO" || $issuetype == "LIFO" || $issuetype == "EXPDATE") {
						$ch->unitcost = $unitcost;
					}
					else {
						$avgunitcost = $fl->unitcost;
						$ch->unitcost = $avgunitcost;
					}
					$ch->newrack = $newrack;
					$ch->quantity = $quantity;
					$ch->costdate = $costdate;
					
					$ch->batchnum = $batchnum;
					$ch->expireddate = $expireddate;
					$ch->id = $invfifolifocostid;
					
					array_push($ar, $ch);
				}
				$countar = count($ar);
				$issuedqtytemp = $issuedqty;
				$resultr = [];
				
				$sumlineprofit = 0;
				for($i=0; $i<$countar; $i++){
						
					$fm = clone $fifotrans;
					$ch = $ar[$i];
					$invfifolifocostid = $ch->id;
					$costdate = $ch->costdate;
					$unitcost = $ch->unitcost;
					$oldqty = $ch->quantity;
					$batchnum = $ch->batchnum;
					$newrack = $ch->newrack;
					$expireddate = $ch->expireddate;
					
					
					$issuedqtytemp = $issuedqtytemp - $oldqty;
					if($issuedqtytemp<0){
						$newqty = $issuedqtytemp*-1;
						$restqty = $oldqty+$issuedqtytemp;
					}else{
						$newqty = $oldqty-$oldqty;
						$restqty = $oldqty;
					}
					
					$set2 = new Set();
					if($tablename == "matrectrans") {
						if($newqty == 0){
							$set2->MboSetRemote("invfifolifocost")->setWhere("invfifolifocostid='$invfifolifocostid'")->remove();
						}else{
							$set2->MboSetRemote("invfifolifocost")->setWhere("invfifolifocostid='$invfifolifocostid'")->setValue("quantity", $newqty)->change();
						}
					}else{
						$set2->MboSetRemote("invfifolifocost")->setWhere("invfifolifocostid='$invfifolifocostid'")->setValue("quantity", $newqty)->change();
					}
					
					$linecost = $restqty*$unitcost;
					$restqtyminus = $restqty*-1;
					$linecostminus = $linecost*-1;
					
					if(empty($whatissuetype)){
						
					}else{
						if(isset($fl->unitprice)) $unitprice =  $fl->unitprice;
						else $unitprice = 0;
						
						if(isset($fl->unitpricediscount)) $unitpricediscount =  $fl->unitpricediscount;
						else $unitpricediscount = 0;
					
						$lineprice = $unitprice*$restqty;
						$linepricediscount = $unitpricediscount*$restqty;
						
						$unitprofit = $unitprice-$unitcost;
						$unitprofitdiscount = $unitpricediscount-$unitcost;
						
						$lineprofit = $unitprofit*$restqty;
						$lineprofitdiscount = $unitprofitdiscount*$restqty;
						
						if($tablename == "matsaletrans"){
							
							if($transtype == "SALE"){
								$setp = new Set();
								$pricecode = $fl->pricecode;
								if($linecost<0) $linecost = $linecost*-1;
								$taxprice = $restqty*$unittaxprice;
								$linecommission = $restqty*$unitcommission;
								$fm->setValue("batchnum", $batchnum)->setValue("quantity", $restqtyminus)->setValue("unitcost", $unitcost)
								->setValue("linecost", $linecostminus)
								->setValue("issuetype", $issuetype)->setValue("costdate", $costdate)->setValue("fromrack", $newrack)
								->setValue("costtype", $issuetype)
								->setValue("lineprice", $lineprice)->setValue("linepricediscount", $linepricediscount)
								->setValue("unitprofit", $unitprofit)->setValue("lineprofit", $lineprofit)
								->setValue("unitprofitdiscount", $unitprofitdiscount)->setValue("lineprofitdiscount", $lineprofitdiscount)
								->setValue("taxprice", $taxprice)
								->setValue("linecommission", $linecommission)
								->add();
								
								$sumlineprofit = $sumlineprofit+$lineprofitdiscount;
								
							}
							
							$retval = new stdClass();
							$retval->unitcost = $unitcost;
							$retval->linecost = $linecostminus;
							$retval->unitprice = $unitprice;
							$retval->lineprice = $lineprice;
							$retval->linepricediscount = $linepricediscount;
							$retval->unitprofit = $unitprofit;
							$retval->lineprofit = $lineprofit;
							$retval->unitprofitdiscount = $unitprofitdiscount;
							$retval->lineprofitdiscount = $lineprofitdiscount;
							
							array_push($resultr, $retval);
							
						}else{
							
							if($tablename == "matusetrans"){
								$linecommission = $restqty*$unitcommission;
								$fm->setValue("batchnum", $batchnum)->setValue("quantity", $restqtyminus)->setValue("unitcost", $unitcost)->setValue("linecost", $linecostminus)
								->setValue("issuetype", $issuetype)->setValue("costdate", $costdate)->setValue("racknum", $newrack)
								->setValue("oldcurbal", $oldqty)->setValue("newcurbal", $newqty)
								->setValue("lineprice", $lineprice)->setValue("linepricediscount", $linepricediscount)
								->setValue("unitprofit", $unitprofit)->setValue("lineprofit", $lineprofit)
								->setValue("unitprofitdiscount", $unitprofitdiscount)->setValue("lineprofitdiscount", $lineprofitdiscount)->setValue("expireddate", $expireddate)
								->setValue("linecommission", $linecommission)
								->add();
							}else{
								$fm->setValue("batchnum", $batchnum)->setValue("racknum", $newrack)->setValue("quantity", $restqtyminus)->setValue("unitcost", $unitcost)->setValue("linecost", $linecostminus)
								->setValue("issuetype", $issuetype)->setValue("costdate", $costdate)->add();
							}
							
							
							$retval = new stdClass();
							$retval->unitcost = $unitcost;
							$retval->linecost = $linecostminus;
							$retval->unitprice = $unitprice;
							$retval->lineprice = $lineprice;
							$retval->linepricediscount = $linepricediscount;
							$retval->unitprofit = $unitprofit;
							$retval->lineprofit = $lineprofit;
							$retval->unitprofitdiscount = $unitprofitdiscount;
							$retval->lineprofitdiscount = $lineprofitdiscount;
							
							array_push($resultr, $retval);
							
						}
					}
					if($issuedqtytemp<=0) break;
				}
				
				if($tablename == "matsaletrans"){
					//check paid invoice
					$qw = new Set();
					$sonum = $fl->sonum;
					$itemnum = $fl->itemnum;
					
					$checkwc = "sonum='$sonum' and itemnum='$itemnum' and (unitcost=0 or unitcost is null or unitcost='')";
					$checkInvoiceSOSet = $qw->MboSetRemote("invoicetrans")->setWhere($checkwc)
					->MboRemote("unitpricediscount")->MboRemote("quantity")->getMboSet();
					$countCheckSO = $checkInvoiceSOSet->counts();
					if($countCheckSO>0){
						for($aa=0; $aa<$countCheckSO; $aa++){
							$checkInvoiceSO = $checkInvoiceSOSet->getMbo();
							$check_unitprice = $checkInvoiceSO->getString("unitpricediscount");
							$check_quantity = $checkInvoiceSO->getString("quantity");
							$check_linecost = $unitcost*$check_quantity;
							$check_unitprofit = $check_unitprice-$unitcost;
							$check_lineprofit = $check_unitprofit*$check_quantity;
							
							$check_unitprofitdiscount = $check_unitprofit;
							$check_lineprofitdiscount = $check_lineprofit;
							$ssq = new Set();
							$ssq->MboSetRemote("invoicetrans")->setWhere($checkwc)
							->setValue("unitcost", $unitcost)
							->setValue("linecost", $check_linecost)
							->setValue("unitprofit", $check_unitprofit)
							->setValue("lineprofit", $check_lineprofit)
							->setValue("unitprofitdiscount", $check_unitprofitdiscount)
							->setValue("lineprofitdiscount", $check_lineprofitdiscount)
							->change();
						}
					}
				}
				return $resultr;
			}else{
				
				if(empty($whatissuetype)){
				
					$avgtrans->add();
				}else{
					if($flag == 0)  {
						if($totalqty<$issuedqty){
							return "notenoughfifoqty";
						}else{
							$avgtrans->add();
						}
					} 
					$u = new Set();
					$u->MboSetRemote("inventory")->setWhere("itemnum='$itemnum' and warehouse='$warehouse'")->setValue("issuetype", "AVG")->change();
					return "notenoughfifoqty";
				}
			}
		}else{
			if($flag == 0) {
				if(!isset($fl->costtype)){
					$set = new Set();
					$costtype = $set->MboSetRemote("inventory")->setWhere("itemnum='$itemnum' and warehouse='$warehouse'")->MboRemote("issuetype")->getMboSet()->getMbo()->getString("issuetype");
				}
				if($costtype == "AVG"){
					$avgtrans->add();
				}else{
					$u = new Set();
					$u->MboSetRemote("inventory")->setWhere("itemnum='$itemnum' and warehouse='$warehouse'")->setValue("issuetype", "AVG")->change();
					return "notenoughfifoqty";
				}
			}
		}
		
	}
}
?>