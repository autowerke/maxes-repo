<?php
require_once ("../../../eat.php");
require_once ("dataloader.php");
Class ActionSet {
	public static function changemaxcost(){
		$customercode = $_POST["customercode"];
		$changemaxcost = $_POST["changemaxcost"];
		$newremarks = $_POST["remarks"];
		$newrequestedby = $_POST["requestedby"];
		$changedate = Xbo::setEnterDate();
		$enterby = Xbo::getLoginUser();
		
		$wc ="customercode = '$customercode'";
		
		$set = new Set();
		$customer = $set->MboSetRemote("customer")->setWhere($wc)->MboRemote("maxcost")->getMboSet()->getMbo();
		$prevmaxcost = $customer->getString("maxcost");
		
		if(empty($prevmaxcost) || $prevmaxcost == null) $prevmaxcost = 0;
		
		$set = new Set();
		$set->MboSetRemote("customer")->setWhere($wc)->setValue("maxcost", $changemaxcost)->change();
		$s = new stdClass();
		$s->msg = "maxcost_updated";
		$s->maxcost = $changemaxcost;
		
		
		$set = new Set();
		$customermaxtransfer = $set->MboSetRemote("customermaxtransfer")
		->setValue("customercode", $customercode)
		->setValue("changedate", $changedate)
		->setValue("requestedby", $newrequestedby)
		->setValue("enterby", $enterby)
		->setValue("newmaxcost", $changemaxcost)
		->setValue("prevmaxcost", $prevmaxcost)
		->setValue("remarks", $newremarks)
		->add();
		
		Set::throws($s);
	}
	public static function changesalesman(){
		
		$customercode = $_POST["customercode"];
		$newsalesman = $_POST["changesalesman"];
		$newrequestedby = $_POST["requestedby"];
		$newremarks = $_POST["remarks"];
		$fromsalesman = $_POST["fromsalesman"];
		$changedate = Xbo::setEnterDate();
		$changeby = Xbo::getLoginUser();
		
		$set = new Set();
		$set->MboSetRemote("customer")->setWhere("customercode='$customercode'")->setValue("salesrep", $newsalesman)->change();
		
		$s = new stdClass();
		$s->msg = "salesman_updated";
		$s->newsalesman = $newsalesman;
		$set = new Set();
		$set->MboSetRemote("salesrephistory")->setValue("fromsalesman", $fromsalesman)->setValue("tosalesman", $newsalesman)->setValue("changeby", $changeby)
		->setValue("changedate", $changedate)->setValue("customercode", $customercode)->setValue("remarks", $newremarks)->setValue("requestedby", $newrequestedby)->add();
		
		
		Set::throws($s);
	}
	public function ProcessInvoiceConsignment($ci){
		
		$itemnum = $ci->itemnum;
		$warehouse = $ci->warehouse;
		$conditiontype = $ci->conditiontype;
		$racknum = $ci->racknum;
		
		$wonum = $ci->wonum;
		$invoicenum = $ci->invoicenum;
		
		if(empty($ci->conditiontype)){
			$condval = " and (conditiontype is null or conditiontype = '') ";
			$condval2 = " and (custbalance.conditiontype is null or custbalance.conditiontype = '') ";
		}else{
			$condval = "and conditiontype='".$ci->conditiontype."'";
			$condval2 = "and custbalance.conditiontype='".$ci->conditiontype."'";
		}
			
		if(empty($ci->racknum)){
			$rackval = " and (racknum is null or racknum = '') ";
			$rackval2 = " and (custbalance.fromracknum is null or custbalance.fromracknum = '') ";
		}else{
			$rackval = "and racknum='".$ci->racknum."'";
			$rackval2 = "and custbalance.fromracknum='".$ci->racknum."' ";
		}
		//PRICE FROM MATUSETRANS - (USE DROPPED PRICE)
		$set = new Set();
		$costdate =Super::whereDate("costdate", $ci->costdate);
		
		$matuseSet = $set->MboSetRemote("matusetrans")->setWhere("wonum='$wonum' and itemnum='$itemnum' and warehouse = '$warehouse' $condval $rackval $costdate")
		->MboRemote("unitcost")->MboRemote("unitprice")->MboRemote("unitpricediscount")
		->getMboSet();
		
		$matuse = $matuseSet->getMbo();
		$unitcost = $matuseSet->getString("unitcost");
		//END
		
		//PRICE FROM INVENTORY (ALWAYS USE PRICE UPDATE
		$bo = new Xbo();
		$pricing = $bo->getUnitPriceDiscount($ci->orgcode, $ci->itemnum, $ci->warehouse, $ci->conditiontype);
		$ispriceexist = $pricing->msg;
		if($ispriceexist == "cantfounddefaultprice"){
			$this->msg =  $ispriceexist;
			$this->itemnum = $ci->itemnum;
			$this->warehouse = $ci->warehouse;
			$this->conditiontype = $ci->conditiontype;
		}else{
			$useupdateprice = $pricing->useupdateprice;
			//END
			if(empty($useupdateprice)) $useupdateprice = "N";
			
			if($useupdateprice == "N" || $useupdateprice != "Y"){
				$unitprice = $matuse->getString("unitprice");
				$unitpricediscount = $matuse->getString("unitpricediscount");
				
			}else{
				$unitprice = $pricing->unitprice;
				$unitpricediscount = $pricing->unitpricediscount;
			}
			$quantity = $ci->quantity;
			$linecost = $unitcost*$quantity;
			$tax = $ci->tax;
			
			if(empty($unitprice) || empty($unitpricediscount)){
				$unitprice = $pricing->unitprice;
				$unitpricediscount = $pricing->unitpricediscount;
				if(empty($unitpricediscount)) $unitpricediscount = $unitprice;
			}
			if($unitprice == 0){
				$this->msg =  "cantfounddefaultprice";
				$this->itemnum = $ci->itemnum;
				$this->warehouse = $ci->warehouse;
				$this->conditiontype = $ci->conditiontype;
			}else{
				/* CALCULATE PRICE */
				$lineprice = $unitprice*$quantity;
				$linepricediscount = $unitpricediscount*$quantity;
				
				$unitprofit = $unitprice-$unitcost;
				$unitprofitdiscount = $unitpricediscount-$unitcost;
				
				$lineprofit = $unitprofit*$quantity;
				$lineprofitdiscount = $unitprofitdiscount*$quantity;
				
				/* END CALCULATE PRICE */
				
				$glcreditacct = Xbo::getwogl($wonum);
				
				if($tax == null) $taxcost = 0;
				else {
					$taxvalue = XboSet::getTaxValue($tax);
					$taxcost = ($taxvalue*$linepricediscount)/100;
				}
				
				$priceaftertax = $taxcost+$lineprice;
				$remarks = "Consignment item";
				$customercode = $ci->customercode;
				
				//ADD TO INVOICELINE
				if($quantity <= 0 ){
				}else{
					$sc = new Set();
					$ilSet = $sc->MboSetRemote("invoiceline")->setWhere("invoicenum='".$ci->invoicenum."' and itemnum='".$ci->itemnum."'")->MboRemote("invoicelinenum")->getMboSet();
					$cnt = $ilSet->counts();
					if($cnt == 0){
						$invoicelinenum = Xbo::getLineNumber("invoiceline", "invoicelinenum", "invoicenum='$invoicenum'");
						$invoicechargelinenum = Xbo::getLineNumber("invoicecharge", "invoicechargelinenum", "invoicenum='$invoicenum'");
					}else{
						$ils = $ilSet->getMbo();
						$invoicelinenum = $ils->getString("invoicelinenum");
						
						$sic = new Set();
						$icSet = $sic->MboSetRemote("invoicecharge")->setWhere("invoicenum='".$ci->invoicenum."'")->MboRemote("max(invoicechargelinenum) as invoicechargelinenum")->getMboSet();
						$ics = $icSet->getMbo();
						
						$invoicechargelinenum = $ics->getString("invoicechargelinenum");
					}
					
					$set2 = new Set();
					if($linecost > 0) $linecost = $linecost*-1;
					$whereclause = "itemnum='".$ci->itemnum."' and warehouse='".$ci->warehouse."' and unitcost2='".$unitcost."' and wonum='".$ci->wonum."' $rackval $condval $costdate ";
					
					$set2->MboSetRemote("invoiceline")->setValue("invoicenum", $ci->invoicenum)->setValue("invoicelinenum", $invoicelinenum)->setValue("linetype", "item")
					->setValue("itemnum", $ci->itemnum)->setValue("conditiontype", $ci->conditiontype)->setValue("description", $ci->itemdesc)->setValue("quantity", $quantity)->setValue("saleunit", $ci->saleunit)
					->setValue("unitcost", $unitprice)->setValue("linecost", $lineprice)->setValue("unitprofit", $unitprofit)->setValue("lineprofit", $lineprofit)
					->setValue("conversionfactor", 1)->setValue("tax", $tax)->setValue("taxcost", $taxcost)->setValue("warehouse", $ci->warehouse)->setValue("racknum", $ci->racknum)->setValue("glcreditacct", $glcreditacct)
					->setValue("enterby", $ci->enterby)->setValue("enterdate", $ci->enterdate)->setValue("changeby", $ci->changeby)->setValue("changedate", $ci->changedate)->setValue("remarks", $remarks)
					->setValue("unitpricediscount", $unitpricediscount)->setValue("linepricediscount", $linepricediscount)->setValue("unitprofitdiscount", $unitprofitdiscount)->setValue("lineprofitdiscount", $lineprofitdiscount)
					->setValue("unitcost2", $unitcost)->setValue("linecost2", $linecost)->setValue("customercode", $customercode)
					->setValue("consignment", "Y")->setValue("wonum", $ci->wonum)->setValue("costdate", $ci->costdate)->add();
					
					$st= new Set();
					
					$invoicelineSet = $st->MboSetRemote("invoiceline")->setWhere($whereclause)->MboRemote("sum(quantity) as invoicedqty")->getMboSet();
					$invoiceline = $invoicelineSet->getMbo();
					$invoicedqty = $invoiceline->getString("invoicedqty");
					
					$st2 = new Set();
					$whereclause2 = "itemnum='".$ci->itemnum."' and warehouse='".$ci->warehouse."' and unitcost='".$unitcost."' and wonum='".$ci->wonum."' $rackval $condval $costdate and matusetrans.shipstatus='DELIVERED'";
					$matusetransSet = $st2->MboSetRemote("matusetrans")->setWhere($whereclause2)->MboRemote("sum(quantity) as droppedqty")->getMboSet();
				
					$matuse = $matusetransSet->getMbo();
					$droppedqty = $matuse->getString("droppedqty");
					if($droppedqty <0) $droppedqty = $droppedqty*-1;
					
					$remainingqty = $droppedqty-$invoicedqty;
					
					$st3 = new Set();
					$whereclause3 = "itemnum='".$ci->itemnum."' and fromwarehouse='".$ci->warehouse."' and wonum='".$ci->wonum."' $rackval2 $condval2 $costdate ";
					//$st3->MboSetRemote("custbalance")->setWhere($whereclause3)->setValue("issuedqty", 0)->setValue("droppedqty", $remainingqty)->setValue("curbal", $remainingqty)->change();
					$st3->MboSetRemote("custbalance")->setWhere($whereclause3)->setValue("issuedqty", 0)->setValue("curbal", $remainingqty)->change();
				}	
					
				//END INVOICELINE
				if($taxcost >0 ){
					$setd = new Set();
					$setd->MboSetRemote("invoicecharge")->setWhere("invoicenum='".$ci->invoicenum."'")->remove();
					
					$st = new set();
					$invoicelineSet = $st->MboSetRemote("invoiceline")->setWhere("invoicenum='".$ci->invoicenum."'")->MboRemote("sum(taxcost) as taxcost")->getMboSet();
					$invoiceline = $invoicelineSet->getMbo();
					$taxcost = $invoiceline->getString("taxcost");
					
					$setic = new Set();
					$setic->MboSetRemote("invoicecharge")->setValue("invoicenum", $ci->invoicenum)->setValue("invoicechargelinenum", $invoicechargelinenum)->setValue("customercode", $ci->customercode)
					->setValue("description", "Consignment Tax from distribution process")->setValue("quantity", "1")
					->setValue("unitcost", $taxcost)->setValue("linecost", $taxcost)->setValue("origin", 'tax')->setValue("chargekey", '9999')->setValue("enterdate", $ci->enterdate)
					->setValue("enterby", $ci->enterby)->setValue("changeby", $ci->changeby)->setValue("changedate", $ci->changedate)->setValue("consignment", "Y")->add();
				}
				
				
				$this->msg = $ci->msg;
			}
		}
		
		return $this;
	}
	public function ValidateInvoiceConsignment($ci){
		$arr = [];
		$unitprice = Xbo::getUnitPrice($ci->orgcode, $ci->itemnum, $ci->warehouse, $ci->conditiontype);
		$cancreate = true;
		if($unitprice <0) {
			$this->msg = "cantfounddefaultprice";
			$this->itemnum = $ci->itemnum;
			$this->warehouse = $ci->warehouse;
			$this->conditiontype = $ci->conditiontype;
			$this->cancreate  = false;
			
			//roll back changes
			if(empty($ci->conditiontype)){
				$condval = " and (conditiontype is null or conditiontype = '') ";
				
			}else{
				$condval = "and conditiontype='".$ci->conditiontype."'";
			}
				
			if(empty($ci->racknum)){
				$rackval = " and (fromracknum is null or fromracknum = '') ";
			}else{
				$rackval = "and fromracknum='".$ci->racknum."'";
			}
		
		
			$set = new Set();
			/*$custbalanceSet = $set->MboSetRemote("custbalance")->setWhere("itemnum='".$ci->itemnum."' and fromwarehouse='".$ci->warehouse."' and wonum = '".$ci->wonum."' $condval $rackval")
			->setValue('issuedqty', 0)->setValue('droppedqty', $ci->droppedqty)->setValue('curbal', $ci->droppedqty)->change();
			*/
			$custbalanceSet = $set->MboSetRemote("custbalance")->setWhere("itemnum='".$ci->itemnum."' and fromwarehouse='".$ci->warehouse."' and wonum = '".$ci->wonum."' $condval $rackval")
			->setValue('issuedqty', 0)->setValue('curbal', $ci->droppedqty)->change();
			// end roll back
		}else{
			
			$this->cancreate = true;
			$this->msg = "willbeadded";			
		}
		
		return $this;
		
	}
	public static function GetAllUninvoicedItems(){
		$customercode = $_POST["alluninvoiceditems"];
		$set = new Set();
		$customercode = $_POST["alluninvoiceditems"];
		$invoicenum = $_POST["invoicenum"];
		$description = $_POST["description"];
		$orgcode = $_POST["orgcode"];
		$sitecode = $_POST["sitecode"];
		$tax = $_POST["taxcode"];
		$invoicedate = $_POST["invoicedate"];
		$enterby = Xbo::getLoginUser();
		$enterdate = Xbo::setEnterDate();
		$changeby = $enterby;
		$changedate = $enterdate;
		
		$custbalanceSet = $set->MboSetRemote("custbalance")->setWhere("customercode='$customercode'")->MboRemote("custbalance.itemnum")
		->MboRemote("custbalance.fromwarehouse")->MboRemote("custbalance.droppedqty")
		->MboRemote("custbalance.custbalanceid")->MboRemote("custbalance.fromracknum")->MboRemote("custbalance.conditiontype")->MboRemote("custbalance.costdate")
		->MboRemote("custbalance.issuedqty")->MboRemote("custbalance.curbal")->MboRemote("custbalance.wonum")->getMboSet();
		$countcust = $custbalanceSet->counts();
		$subtotalcost= 0;
		$totalcost = 0;
		$msg = null;
		
		if($countcust>0){
			$arr = [];
			for($i=0; $i<$countcust; $i++){
				$ci = new stdClass();
				$custbalance = $custbalanceSet->getMbo();
				$ci->itemnum = $custbalance->getString("itemnum");
				$ci->warehouse = $custbalance->getString("fromwarehouse");
				$ci->racknum = $custbalance->getString("fromracknum");
				$ci->conditiontype = $custbalance->getString("conditiontype");
				$ci->quantity = $custbalance->getString("issuedqty");
				$ci->droppedqty = $custbalance->getString("droppedqty");
				$ci->curbal = $custbalance->getString("curbal");
				$ci->saleunit = Xbo::getIssueUnit($ci->itemnum);
				$ci->wonum = $custbalance->getString("wonum");
				
				$sid = new Set();
				$itemSet = $sid->MboSetRemote("item")->setWhere("itemnum='".$ci->itemnum."'")->MboRemote("description as itemdesc")->getMboSet();
				$item = $itemSet->getMbo();
				$ci->itemdesc = $itemSet->getString("itemdesc");
				
				$ci->invoicenum = $invoicenum;
				$ci->invoicelinenum = Xbo::getLineNumber("invoiceline", "invoicelinenum", "invoicenum='$invoicenum'");
				$ci->custbalanceid = $custbalance->getString("custbalanceid"); 
				$ci->tax = $tax;
				$ci->orgcode = $orgcode;
				$ci->sitecode = $sitecode;
				$ci->enterby = $enterby;
				$ci->enterdate = $enterdate;
				$ci->changeby = $changeby;
				$ci->changedate = $changedate;
				$ci->customercode = $customercode;
				$ci->costdate = $custbalance->getString("costdate");
				
				$a = new ActionSet();
				
				if($ci->quantity>0){
					$o = $a->ValidateInvoiceConsignment($ci);
					$cancreate = $o->cancreate;
					if($cancreate == true) {
						$st = new Set();
						$invoiceSet = $st->MboSetRemote("invoice")->setWhere("invoicenum='".$invoicenum."'")->MboRemote("invoicenum")->MboRemote("status")->getMboSet();
						$invoice = $invoiceSet->getMbo();
						$invoiceisexist = $invoice->getString("invoicenum");
						$status = $invoice->getString("status");
						
						if(empty($invoiceisexist)){
							$msg = "invoiceadded";
							$ci->msg = $msg;
							array_push($arr, $ci);
						}else{
							if($status == "paid"){
								$msg = "invoicehasbeenpaid";
								break;
							}else{
								$msg = "invoicechanged";
								$ci->msg = $msg;
								array_push($arr, $ci);
							}
						}
					}else{
						$msg = $o->msg;
						$itemnum = $o->itemnum;
						$conditiontype = $o->conditiontype;
						$warehouse = $o->warehouse;
						break;
					}
				}else{
					$msg = "zerosale";
					continue;
				}
			}
			
		}else{
			$msg = "nouninvoicedqty";
		}
		
		$cntar = count($arr);
		if($msg == "cantfounddefaultprice"){
			$msg = $msg;
		}else{
			if($cntar >0){
				if($msg == "invoicehasbeenpaid"){
				}else{
					for($i=0; $i<$cntar; $i++){
						$oc = $arr[$i];
						$o = $a->ProcessInvoiceConsignment($oc);						
						$msg = $o->msg;
						if($msg == "cantfounddefaultprice"){
							$itemnum = $o->itemnum;
							$warehouse = $o->warehouse;
							$conditiontype = $o->conditiontype;
						}
					}
					$st = new Set();
					$subtotalcostSet = $st->MboSetRemote("invoiceline")->setWhere("invoicenum = '$invoicenum' ")->MboRemote("sum(linepricediscount) as subtotalcost")->getMboSet();
					$subtotalcost = $subtotalcostSet->getMbo()->getString("subtotalcost");
					if(empty($subtotalcost)) $subtotalcost = 0;
					
					$sumtaxcostSet =  $st->MboSetRemote("invoicecharge")->setWhere("invoicenum = '$invoicenum' ")->MboRemote("sum(linecost) as sumtaxcost")->getMboSet();
					$sumtaxcost = $sumtaxcostSet->getMbo()->getString("sumtaxcost");
					if(empty($sumtaxcost)) $sumtaxcost = 0;
					
					$totalcost = $subtotalcost+$sumtaxcost;
					$set3 =new Set();
					
					$set3->MboSetRemote("invoice")->setValue("invoicenum", $invoicenum)->setValue("description", $description)->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)
					->setValue("invoicedate", $invoicedate)->setValue("customercode", $customercode)->setValue("isconsignment", "Y")->setValue("refund", "N")->setValue("totalcost", $totalcost)
					->setValue("subtotalcost", $subtotalcost)->setValue("status", $status)->setValue("statusdate", $enterdate)->setValue("enterby", $enterby)
					->setValue("enterdate", $enterdate)->setValue("totaltax", $sumtaxcost)->setValue("changeby", $changeby)->setValue("changedate", $changedate);
					
					if($msg == "invoiceadded"){
						$set3->add();
						$status = Xbo::	getStatus("invoice", 1);
						$set4 = new Set();
						$set4->MboSetRemote("invoicestatus")->setValue("invoicenum", $invoicenum)->setValue("status", $status)->setValue("changeby", $changeby)->setValue("changedate", $changedate)
						->add();
						$getAutoNum = new Super();
						$getAutoNum->getAutoNumber("invoice", "update_next_value");
					}else{
						if($msg == "invoicechanged"){
							$set3->setWhere("invoicenum='$invoicenum'")->change();
						}
					}
				}
			}else{
				$msg = $msg;
			}
		}
		
		if(isset($itemnum) ){
			if($itemnum != null){
				Set::throws (["msg"=>$msg, "itemnum"=>$itemnum, "warehouse" => $warehouse, "conditiontype" =>$conditiontype]);
			}else{
				if($cntar >0) {
					$msg = "invoiceadded";
				}
				Set::throws (["msg"=>$msg]);
			}
		}else{
			if($cntar >0) {
				if($msg == "nouninvoicedqty"){
				}else{
					$msg = $msg;
				}
			}
			Set::throws (["msg"=>$msg]);
		}
	}
	
} 

Class ValidateTables {
	public static function tbcx(){
		$dl = explode("|", $_GET["tbcx"]);
		$set = new Set();
		if ($dl[2] == "servsaletrans") {
			Set::throws(["msg" => "relateddata", "rltbl" => "soline"]);
		}
		if ($dl[2] == "matsaletrans") {
			Set::throws(["msg" => "relateddata", "rltbl" => "soline"]);
		}
		if ($dl[2] == "soshipment") {
			$colarr = explode("^", $dl[3]);
			$flag = 0;
			for ($i = 0; $i < count($colarr); $i++) {
				$tableid = explode("`", $colarr[$i]) [1];
				$soshipment = $set->MboSetRemote("soshipment")->setWhere("soshipmentid='$tableid'")->MboRemote("sonum")->getMboSet()->getMbo();
				$sonum = $soshipment->getString("sonum");
				$invchset = new Set();
				$invoicechargeSet = $invchset->MboSetRemote("invoicecharge")->setWhere("sonum='$sonum' and chargekey='$tableid'")->MboRemote("sonum")->getMboSet();
				if ($invoicechargeSet->counts() > 0) {
					$flag = $flag + 1;
				}
				else {
					$flag = 0;
				}
			}
			if ($flag > 0) {
				Set::throws(["msg" => "relateddata", "rltbl" => "invoicecharge"]);
			}
			else {
				Set::throws(["msg" => "omdelete"]);
			}
		}
		if ($dl[2] == "sotracking") {
			$colarr = explode("^", $dl[3]);
			$flag = 0;
			for ($i = 0; $i < count($colarr); $i++) {
				$tableid = explode("`", $colarr[$i]) [1];
				$sotracking = $set->MboSetRemote("sotracking")->setWhere("sotrackingid='$tableid'")->MboRemote("sonum")->getMboSet()->getMbo();
				$sonum = $sotracking->getString("sonum");
				$invchset = new Set();
				$invoicechargeSet = $invchset->MboSetRemote("invoicecharge")->setWhere("sonum='$sonum' and chargekey='$tableid'")->MboRemote("sonum")->getMboSet();
				if ($invoicechargeSet->counts() > 0) {
					$flag = $flag + 1;
				}
				else {
					$flag = 0;
				}
			}
			if ($flag > 0) {
				Set::throws(["msg" => "relateddata", "rltbl" => "invoicecharge"]);
			}
			else {
				Set::throws(["msg" => "omdelete"]);
			}
		}
	}
}
	

?>