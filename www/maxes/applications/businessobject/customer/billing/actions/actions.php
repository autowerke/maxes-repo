<?php
require_once ("../../../eat.php");

Class ActionSet {
	
	public static function WriteDataBilling(){
		
		$data = $_POST["writedatabilling"];
		$billingnum = $data["billingnum"];
		$customercode = $data["customercode"];
		$personid = $data["personid"];
		$description = $data["description"];
		$status = $data["status"];
		$statusdate = $data["statusdate"];
		$enterby = $data["enterby"];
		$enterdate = $data["enterdate"];
		$gldebitacct = $data["gldebitacct"];
		$glcreditacct = $data["glcreditacct"];
		$changeby = $data["changeby"];
		$changedate = $data["changedate"];
		$orderdate = $data["orderdate"];
		$totalcost = $data["totalcost"];
		$subtotalprice = $data["subtotalprice"];
		$subtotalpricediscount = $data["subtotalpricediscount"];
		$invoicediscountprice = $data["invoicediscountprice"];
		$discountprice = $data["discountprice"];
		$totaltax = $data["totaltax"];
		$totaltax2 = $data["totaltax2"];
		$totalprice = $data["totalprice"];
		$paidprice = $data["paidprice"];
		$bldiscountprice = $data["bldiscountprice"];
		$unpaidprice = $data["unpaidprice"];
		$orgcode = $data["orgcode"];
		$sitecode = $data["sitecode"];
		$downloaded = $data["downloaded"];
		$billingline =  $data["billingline"];
		
		$set = new Set();
		$billingSet = $set->MboSetRemote("billing")
		->setValue("billingnum", $billingnum)
		->setValue("description", $description)
		->setValue("customercode", $customercode)
		->setValue("personid", $personid)
		->setValue("status", $status)
		->setValue("statusdate", $statusdate)
		->setValue("enterby", $enterby)
		->setValue("enterdate", $enterdate)
		->setValue("gldebitacct", $gldebitacct)
		->setValue("glcreditacct", $glcreditacct)
		->setValue("changeby", $changeby)
		->setValue("changedate", $changedate)
		->setValue("orderdate", $orderdate)
		->setValue("totalcost", $totalcost)
		->setValue("subtotalprice", $subtotalprice)
		->setValue("subtotalpricediscount", $subtotalpricediscount)
		->setValue("invoicediscountprice", $invoicediscountprice)
		->setValue("discountprice", $discountprice)
		->setValue("totaltax", $totaltax)
		->setValue("totaltax2", $totaltax2)
		->setValue("totalprice", $totalprice)
		->setValue("paidprice", $paidprice)
		->setValue("bldiscountprice", $bldiscountprice)
		->setValue("unpaidprice", $unpaidprice)
		->setValue("orgcode", $orgcode)
		->setValue("sitecode", $sitecode)
		->setValue("downloaded", $downloaded)
		->add();
		
		$countbillingline = count($billingline);
		for($i=0; $i<$countbillingline; $i++){
			$databl = $billingline[$i];
			$billingnum = $databl["billingnum"];
			$rollback = $databl["rollback"];
			$billinglinenum = $databl["billinglinenum"];
			$itemnum = $databl["itemnum"];
			$billdesc = $databl["description"];
			$spectext = $databl["spectext"];
			$unitcost = $databl["unitcost"];
			$linecost = $databl["linecost"];
			$unitprice = $databl["unitprice"];
			$lineprice = $databl["lineprice"];
			$unitprofit = $databl["unitprofit"];
			$lineprofit = $databl["lineprofit"];
			$unitpricediscount = $databl["unitpricediscount"];
			$linepricediscount = $databl["linepricediscount"];
			$unitprofitdiscount = $databl["unitprofitdiscount"];
			$lineprofitdiscount = $databl["lineprofitdiscount"];
			$quantity = $databl["quantity"];
			$issueunit = $databl["issueunit"];
			$primarydiscount = $databl["primarydiscount"];
			$secondarydiscount = $databl["secondarydiscount"];
			$discountprice = $databl["discountprice"];
			$tax = $databl["tax"];
			$taxcost = $databl["taxcost"];
			$eventnum = $databl["eventnum"];
			$gldebitacct = $databl["gldebitacct"];
			$glcreditacct = $databl["glcreditacct"];
			$actualdate = $databl["actualdate"];
			$remarks = $databl["remarks"];
			$conditiontype = $databl["conditiontype"];
			$wonum = $databl["wonum"];
			$fromwarehouse = $databl["fromwarehouse"];
			$fromracknum = $databl["fromracknum"];
			$costtype = $databl["costtype"];
			$costdate = $databl["costdate"];
			$expdate = $databl["expdate"];
			$billinglineid = $databl["billinglineid"];
			$enterby = $databl["enterby"];
			$enterdate = $databl["enterdate"];
			$changeby = $databl["changeby"];
			$changedate = $databl["changedate"];
			$unitcommission  = $databl["unitcommission"];
			$linecommission  = $databl["linecommission"];
			
			
			$set2 = new Set();
			$set2->MboSetRemote("billingline")
			->setValue("billingnum", $billingnum)
			->setValue("rollback", $rollback)
			->setValue("billinglinenum", $billinglinenum)
			->setValue("itemnum", $itemnum)
			->setValue("description", $billdesc)
			->setValue("spectext", $spectext)
			->setValue("unitcost", $unitcost)
			->setValue("linecost", $linecost)
			->setValue("unitprice", $unitprice)
			->setValue("lineprice", $lineprice)
			->setValue("unitprofit", $unitprofit)
			->setValue("lineprofit", $lineprofit)
			->setValue("unitpricediscount", $unitpricediscount)
			->setValue("linepricediscount", $linepricediscount)
			->setValue("unitprofitdiscount", $unitprofitdiscount)
			->setValue("lineprofitdiscount", $lineprofitdiscount)
			->setValue("quantity", $quantity)
			->setValue("issueunit", $issueunit)
			->setValue("primarydiscount", $primarydiscount)
			->setValue("secondarydiscount", $secondarydiscount)
			->setValue("discountprice", $discountprice)
			->setValue("tax", $tax)
			->setValue("taxcost", $taxcost)
			->setValue("eventnum", $eventnum)
			->setValue("gldebitacct", $gldebitacct)
			->setValue("glcreditacct", $glcreditacct)
			->setValue("actualdate", $actualdate)
			->setValue("remarks", $remarks)
			->setValue("conditiontype", $conditiontype)
			->setValue("wonum", $wonum)
			->setValue("fromwarehouse", $fromwarehouse)
			->setValue("fromracknum", $fromracknum)
			->setValue("costtype", $costtype)
			->setValue("costdate", $costdate)
			->setValue("expdate", $expdate)
			->setValue("enterby", $enterby)
			->setValue("enterdate", $enterdate)
			->setValue("changeby", $changeby)
			->setValue("changedate", $changedate)
			->setValue("unitcommission", $unitcommission)
			->setValue("linecommission", $linecommission)
			->add();
		}
		Set::throws($data);
		
	}
	public static function getDomainData(){
		$url = $_POST["domainurl"];
		$set  = new Set();
		$billingSet = $set->MboSetRemote("billing")->setWhere("downloaded='N'")->getMboSet();
		$bcount = $billingSet->counts();
		
		$arr = [];
		for($i=0; $i<$bcount; $i++){
			$bill = $billingSet->getMbo();
			$obj = new stdClass();
			$obj->billingnum = $bill->getString("billingnum");
			$obj->customercode = $bill->getString("customercode");
			$obj->personid = $bill->getString("personid");
			$obj->description = $bill->getString("description");
			$obj->status = $bill->getString("status");
			$obj->statusdate = $bill->getString("statusdate");
			$obj->enterby = $bill->getString("enterby");
			$obj->enterdate = $bill->getString("enterdate");
			$obj->gldebitacct = $bill->getString("gldebitacct");
			$obj->glcreditacct = $bill->getString("glcreditacct");
			$obj->changeby = $bill->getString("changeby");
			$obj->changedate = $bill->getString("changedate");
			$obj->orderdate = $bill->getString("orderdate");
			$obj->totalcost = $bill->getString("totalcost");
			$obj->subtotalprice = $bill->getString("subtotalprice");
			$obj->subtotalpricediscount = $bill->getString("subtotalpricediscount");
			$obj->invoicediscountprice = $bill->getString("invoicediscountprice");
			$obj->discountprice = $bill->getString("discountprice");
			$obj->totaltax = $bill->getString("totaltax");
			$obj->totaltax2 = $bill->getString("totaltax2");
			$obj->totalprice = $bill->getString("totalprice");
			$obj->paidprice = $bill->getString("paidprice");
			$obj->bldiscountprice = $bill->getString("bldiscountprice");
			$obj->unpaidprice = $bill->getString("unpaidprice");
			$obj->orgcode = $bill->getString("orgcode");
			$obj->sitecode = $bill->getString("sitecode");
			$obj->downloaded = $bill->getString("downloaded");
			
			$set2 = new Set();
			$billinglineSet = $set2->MboSetRemote("billingline")->setWhere("billingnum='{$obj->billingnum}'")->getMboSet();
			$billlinecount = $billinglineSet->counts();
			
			$billliner = [];
			
			for($j=0; $j<$billlinecount; $j++){
				
				$billineob = new stdClass();
				$billline = $billinglineSet->getMbo();
				$billineob->billingnum = $billline->getString("billingnum");
				$billineob->rollback = $billline->getString("rollback");
				$billineob->billinglinenum = $billline->getString("billinglinenum");
				$billineob->itemnum = $billline->getString("itemnum");
				$billineob->description = $billline->getString("description");
				$billineob->spectext = $billline->getString("spectext");
				$billineob->unitcost = $billline->getString("unitcost");
				$billineob->linecost = $billline->getString("linecost");
				$billineob->unitprice = $billline->getString("unitprice");
				$billineob->lineprice = $billline->getString("lineprice");
				$billineob->unitprofit = $billline->getString("unitprofit");
				$billineob->lineprofit = $billline->getString("lineprofit");
				$billineob->unitpricediscount = $billline->getString("unitpricediscount");
				$billineob->linepricediscount = $billline->getString("linepricediscount");
				$billineob->unitprofitdiscount = $billline->getString("unitprofitdiscount");
				$billineob->lineprofitdiscount = $billline->getString("lineprofitdiscount");
				$billineob->quantity = $billline->getString("quantity");
				$billineob->issueunit = $billline->getString("issueunit");
				$billineob->primarydiscount = $billline->getString("primarydiscount");
				$billineob->secondarydiscount = $billline->getString("secondarydiscount");
				$billineob->discountprice = $billline->getString("discountprice");
				$billineob->tax = $billline->getString("tax");
				$billineob->taxcost = $billline->getString("taxcost");
				$billineob->eventnum = $billline->getString("eventnum");
				$billineob->gldebitacct = $billline->getString("gldebitacct");
				$billineob->glcreditacct = $billline->getString("glcreditacct");
				$billineob->actualdate = $billline->getString("actualdate");
				$billineob->remarks = $billline->getString("remarks");
				$billineob->conditiontype = $billline->getString("conditiontype");
				$billineob->wonum = $billline->getString("wonum");
				$billineob->fromwarehouse = $billline->getString("fromwarehouse");
				$billineob->fromracknum = $billline->getString("fromracknum");
				$billineob->costtype = $billline->getString("costtype");
				$billineob->costdate = $billline->getString("costdate");
				$billineob->expdate = $billline->getString("expdate");
				$billineob->billinglineid = $billline->getString("billinglineid");
				$billineob->enterby = $billline->getString("enterby");
				$billineob->enterdate = $billline->getString("enterdate");
				$billineob->changeby = $billline->getString("changeby");
				$billineob->changedate = $billline->getString("changedate");
				$billineob->unitcommission = $billline->getString("unitcommission");
				$billineob->linecommission = $billline->getString("linecommission");
				
				array_push($billliner, $billineob);
			}
			$obj->billingline = $billliner;
			array_push($arr, $obj);
			
			
		}
		
		
		Set::throws($arr);
	}
	public static function RollBackBilling(){
		$billingnum = $_POST["rollbackbilling"];
		$set = new Set();
		$enterdate = Xbo::setEnterDate();
		$enterby  = Xbo::getLoginUser();
		
		$billingSet = $set->MboSetRemote("billingline bl")
		->setRelationship("billing b", "b.billingnum = bl.billingnum")
		->setWhere("bl.billingnum='$billingnum'")
		->MboRemote("b.customercode")->MboRemote("bl.itemnum")->MboRemote("bl.wonum")->MboRemote("bl.fromwarehouse")->MboRemote("bl.conditiontype")
		->MboRemote("bl.fromracknum")->MboRemote("bl.costdate")->MboRemote("bl.quantity")->MboRemote("bl.billinglinenum")->MboRemote("bl.costtype")
		->MboRemote("bl.billinglineid")
		->getMboSet();
		
		$cnt = $billingSet->counts();
		for($i=0; $i<$cnt; $i++){
			$bl = $billingSet->getMbo();
			$customercode = $bl->getString("customercode");
			$itemnum = $bl->getString("itemnum");
			$wonum = $bl->getString("wonum");
			$fromwarehouse = $bl->getString("fromwarehouse");
			$conditiontype = $bl->getString("conditiontype");
			$fromracknum = $bl->getString("fromracknum");
			$costdate = $bl->getString("costdate");
			$quantity = $bl->getString("quantity");
			$billinglinenum = $bl->getString("billinglinenum");
			$billinglineid = $bl->getString("billinglineid");
			$costtype = $bl->getString("costtype");
			if(empty($conditiontype)) $condval = " and (custbalance.conditiontype is null or custbalance.conditiontype = '')";
			else $condval = " and custbalance.conditiontype = '$conditiontype'"; 
			
			if(empty($fromracknum)) $rackval = " and (custbalance.fromracknum is null or custbalance.fromracknum = '')";
			else $rackval = " and custbalance.fromracknum = '$fromracknum'"; 
			
			
			$costdateval = Super::whereDate("costdate", $costdate);
			$set2 = new Set();
			$wc = "custbalance.itemnum = '$itemnum' and custbalance.fromwarehouse = '$fromwarehouse' $rackval $condval $costdateval";
			$set2->MboSetRemote("custbalance")->setWhere($wc)->setValue("curbal", $quantity)->change();
			
			
			$custob = new stdClass();
			$custob->customercode = $customercode;
			$custob->itemnum = $itemnum;
			
			$is = new Set();
			$itemSet = $is->MboSetRemote("item")->setWhere("itemnum='$itemnum'")->MboRemote("description")->getMboSet();
			$item = $itemSet->getMbo();
			$custob->description = $item->getString("description");
			$custob->fromwarehouse = $fromwarehouse;
			$custob->fromracknum = $fromracknum;
			$custob->conditiontype = $conditiontype;
			$custob->costtype = $costtype;
			$custob->costdate = $costdate;
			$custob->transtype = "ROLLBACK";
			$custob->refund = "N";			
			
			$quantitymin = $quantity*-1;
			$custob->quantity = $quantitymin;
			$quantity = $quantity;
			$custob->wonum = $wonum;
			$custob->billingnum = $billingnum;
			$custob->billinglinenum = $billinglinenum;
			$custob->billinglineid = $billinglineid;
			
			//GET COST & PRICE
			$cfl = new Set();
			if(empty($conditiontype)) $condval = " and (conditiontype is null or conditiontype = '')";
			else $condval = " and conditiontype = '$conditiontype'"; 
			
			if(empty($fromracknum)) $rackval = " and (fromracknum is null or fromracknum = '')";
			else $rackval = " and fromracknum = '$fromracknum'";
			
			$costdateval = Super::whereDate("costdate", $costdate);
			$wc = "itemnum ='$itemnum' and wonum='$wonum' and customercode='$customercode' and fromwarehouse = '$fromwarehouse' $condval $costdateval $rackval";
			
			$custbalancepriceSet = $cfl->MboSetRemote("custbalanceprice")->setWhere($wc)
			->MboRemote("unitcost")->MboRemote("unitprice")->MboRemote("unitpricediscount")
			->MboRemote("discountprice")->MboRemote("unitprofit")->MboRemote("unitprofitdiscount")
			->MboRemote("unitcommission")
			->getMboSet();
			$custbalanceprice = $custbalancepriceSet->getMbo();
			
			$custob->unitcost = $custbalanceprice->getString("unitcost");
			$custob->linecost = $quantity*$custob->unitcost;
			
			$custob->unitprice = $custbalanceprice->getString("unitprice");
			$custob->lineprice = $quantitymin*$custob->unitprice;
			
			$custob->unitprofit = $custbalanceprice->getString("unitprofit");
			$custob->lineprofit = $quantitymin*$custob->unitprofit;
			
			$custob->unitpricediscount = $custbalanceprice->getString("unitpricediscount");
			$custob->linepricediscount = $quantitymin*$custob->unitpricediscount;
		
			$custob->unitprofitdiscount = $custbalanceprice->getString("unitprofitdiscount");
			$custob->lineprofitdiscount = $quantitymin*$custob->unitprofitdiscount;
			
			$custob->discountprice = $custbalanceprice->getString("discountprice");
			
			$custob->unitcommission = $custbalanceprice->getString("unitcommission");
			$custob->linecommission = $quantitymin*$custob->unitcommission;
			
			//END GET COST & PRICE
			
			$custob->actualdate = $enterdate;
			$custob->enterdate = $enterdate;
			$custob->changedate = null;
			$custob->enterby = $enterby;
			$custob->changeby = null;
			$custob->importkey = $billinglineid;
			
			
			ActionSet::InsertCustUseTrans($custob);
		
		}
		
		
		
		
		Set::throws(["msg"=>"rolledback"]);
	}
	public static function ValidateBilling(){
		$billingnum = $_POST["validatebilling"];
		$set = new Set();
		
		$billingStatusSet = $set->MboSetRemote("billingstatus")->setWhere("status='approved' and billingnum='$billingnum'")->getMboSet();
		$bilcount = $billingStatusSet->counts();
		
		$info = new stdClass();
		$info->invoicenum = null;
		$info->invoicelinenum = null;
		$ilr = [];
		if($bilcount>=1){
			$msg = "hasapproved";
			$set = new Set();
			$ilset = $set->MboSetRemote("invoiceline")->setWhere("billingnum='$billingnum'")->MboRemote("invoicenum")->MboRemote("invoicelinenum")->getMboSet();
			
			$ilcont = $ilset->counts();
			if($ilcont >= 1){
				$msg = "invoiceexist";
				$ilr = [];
				for($i=0; $i<$ilcont; $i++){
					$info = new stdClass();
					$il = $ilset->getMbo();
					$invoicenum = $il->getString("invoicenum");
					$invoicelinenum = $il->getString("invoicelinenum");
					$info->invoicenum = $invoicenum;
					$info->invoicelinenum = $invoicelinenum;
					
					array_push($ilr, $info);
				}
			}else{
				$msg = "hasapproved";
				
			}
			
		}else{
			$msg = "hasnotbeenapproved";
		}
		
		
		Set::throws(["msg"=>$msg, "invoiceline"=>$ilr]);
	}
	public static function getTaxValue(){
		$value = trim($_GET['getTaxValue'], " ");
		$set = new Set();
		echo $set->MboSetRemote("tax")->setWhere("taxcode = '$value'")->MboRemote("value")->getMboSet()->getMbo()->getString("value");
	}
	public static function SumBillingCost(){
		$set = new Set();
		$billingnum = $_GET["sumbillingcost"];
		
		$billingSet = $set->MboSetRemote("billingline")->setWhere("billingnum='$billingnum'")
		->MboRemote("sum(lineprice) as subtotalprice")
		->MboRemote("sum(linepricediscount) as subtotalpricediscount")
		->MboRemote("sum(taxcost) as totaltax")
		->MboRemote("sum(linecost) as totalcost")
		->MboRemote("sum(discountprice) as bldiscountprice")
		->getMboSet();
		
		$billing = $billingSet->getMbo();
		$subtotalprice = $billingSet->getString("subtotalprice");
		if(empty($subtotalprice)) $subtotalprice = 0;
		$subtotalpricediscount = $billingSet->getString("subtotalpricediscount");
		if(empty($subtotalpricediscount) )$subtotalpricediscount = 0;
		$totaltax = $billingSet->getString("totaltax");
		if(empty($totaltax)) $totaltax = 0;
		$totalcost = $billingSet->getString("totalcost"); //totalbiaya
		if(empty($totalcost)) $totalcost = 0;
		$totalprice = $subtotalpricediscount+$totaltax;
		$bldiscountprice = $billingSet->getString("bldiscountprice");
		if(empty($bldiscountprice)) $bldiscountprice = 0;
		
		// BILLING PAYMENT
		$set2= new Set();
		$billpaySet = $set2->MboSetRemote("billingpayment")->setWhere("billingnum='$billingnum'")
		->MboRemote("sum(paidprice) as paidprice")
		->MboRemote("sum(discountprice) as discountprice")
		->getMboSet();
		
		$billcount = $billpaySet->counts();
		
		$billpay = $billpaySet->getMbo();
		$paidprice = $billpay->getString("paidprice");
		if(empty($paidprice)) $paidprice = 0;
		
		$discountprice = $billpay->getString("discountprice");
		if(empty($discountprice)) $discountprice = 0;
		
		if($billcount > 0){
			
			$subtotalprice = $subtotalprice; //sub total
			$subtotalpricediscount = $subtotalpricediscount-$discountprice; // sub total diskon
			
			$totaltax2 = $subtotalpricediscount*10/100;
			if($totaltax == 0) $totaltax2 = 0;
			
			$totalprice = $subtotalpricediscount+$totaltax2; // total harga
			$paidprice = $paidprice;
		}
		
		$unpaidprice = $paidprice-$totalprice;
		
		Set::throws(["paidprice"=>$paidprice, "unpaidprice"=>$unpaidprice, 
		"totalcost"=>$totalcost, "subtotalprice"=>$subtotalprice, 
		"totalprice"=>$totalprice, "subtotalpricediscount"=>$subtotalpricediscount, 
		"totaltax"=>$totaltax, "totaltax2"=>$totaltax2, "discountprice"=>$discountprice, "bldiscountprice"=>$bldiscountprice]);
	}
	public static function ApproveBilling(){
		$billingnum = $_POST["approvebilling"];
		$actualdate = $_POST["actualdate"];
		$enterdate = Xbo::setEnterDate();
		$enterby  = Xbo::getLoginUser();
		
		$sc = new Set();
		$customerSet = $sc->MboSetRemote("billing")->setWhere("billingnum='$billingnum'")->getMboSet();
		$customer = $customerSet->getMbo();
		$customercode = $customer->getString("customercode");
		
		$set = new Set();
		$billinglineSet = $set->MboSetRemote("billingline bl")
		->setRelationship("custbalance cb", 
		"cb.itemnum = bl.itemnum 
		and cb.wonum = bl.wonum
		and cb.fromwarehouse = bl.fromwarehouse
		and (cb.costdate = bl.costdate or coalesce(bl.costdate, '') = coalesce (cb.costdate, '') )"
		)
		->setWhere("bl.billingnum='$billingnum'")
		->group("cb.itemnum, cb.wonum, cb.fromwarehouse, cb.costdate")
		->MboRemote("bl.billingnum")->MboRemote("bl.billinglinenum")->MboRemote("bl.billinglineid")
		->MboRemote("
		(select sum(quantity) from billingline bl2 where 
			bl2.itemnum = bl.itemnum
			and bl2.wonum = bl.wonum 
			and bl2.fromwarehouse = bl.fromwarehouse and (bl2.costdate = bl.costdate or coalesce(bl.costdate, '') = coalesce (bl2.costdate, '') ) 
			and bl2.billingnum = '$billingnum'
			) soldqty
		")->MboRemote("cb.itemnum")
		->MboRemote("(
			select sum(curbal) from custbalance cb2
			where cb2.itemnum = bl.itemnum 
			and cb2.wonum = bl.wonum 
			and cb2.fromwarehouse = bl.fromwarehouse 
			and (cb2.costdate = bl.costdate or coalesce(bl.costdate, '') = coalesce (cb2.costdate, '') ) 
			and cb2.customercode = '$customercode' 
			) curbal")
		->MboRemote("cb.fromwarehouse")->MboRemote("cb.wonum")->MboRemote("cb.costdate")->MboRemote("cb.conditiontype")
		->MboRemote("cb.fromracknum")->MboRemote("cb.customercode")->MboRemote("cb.costtype")
		->getMboSet();
		
		$changedate = Xbo::setEnterDate();
		$changeby  = Xbo::getLoginUser();
		
		$cnt = $billinglineSet->counts();
		for($i=0; $i<$cnt; $i++){
			$bill = $billinglineSet->getMbo();
			$itemnum = $bill->getString("itemnum");
			$billingnum = $bill->getString("billingnum");
			$billinglinenum = $bill->getString("billinglinenum");
			$billinglineid = $bill->getString("billinglineid");
			$curbal = $bill->getString("curbal");
			$soldqty = $bill->getString("soldqty");
			$fromwarehouse = $bill->getString("fromwarehouse");
			$wonum = $bill->getString("wonum");
			$costdate = $bill->getString("costdate");
			$conditiontype = $bill->getString("conditiontype");
			$fromracknum = $bill->getString("fromracknum");
			$costdateval = Super::whereDate("costdate", $costdate);
			$remainingqty = $curbal-$soldqty;
			$costtype =  $bill->getString("costtype");
			if(empty($conditiontype)) $condval = " and (custbalance.conditiontype is null or custbalance.conditiontype = '')";
			else $condval = " and custbalance.conditiontype = '$conditiontype'"; 
			
			if(empty($fromracknum)) $rackval = " and (custbalance.fromracknum is null or custbalance.fromracknum = '')";
			else $rackval = " and custbalance.fromracknum = '$fromracknum'"; 
			
			
			
			/* update custbalance */
			$set2=  new Set();
			$set2->MboSetRemote("custbalance")->setWhere("itemnum='$itemnum' and wonum = '$wonum' and customercode='$customercode' and fromwarehouse = '$fromwarehouse' $condval $rackval $costdateval")
			->setValue("curbal", $remainingqty)->setValue("changedate", $changedate)->setValue("changeby", $changeby)->change();
		
		
			$custob = new stdClass();
			$custob->customercode = $customercode;
			$custob->itemnum = $itemnum;
			
			$is = new Set();
			$itemSet = $is->MboSetRemote("item")->setWhere("itemnum='$itemnum'")->MboRemote("description")->getMboSet();
			$item = $itemSet->getMbo();
			$custob->description = $item->getString("description");
			$custob->fromwarehouse = $fromwarehouse;
			$custob->fromracknum = $fromracknum;
			$custob->conditiontype = $conditiontype;
			$custob->costtype = $costtype;
			$custob->costdate = $costdate;
			$custob->transtype = "ISSUE";
			$custob->refund = "N";			
			$custob->quantity = $soldqty;
			$quantitymin = $soldqty*-1;
			$quantity = $soldqty;
			$custob->wonum = $wonum;
			$custob->billingnum = $billingnum;
			$custob->billinglinenum = $billinglinenum;
			$custob->invoicenum = null;
			$custob->invoicelinenum = null;
			
			//GET COST & PRICE
			$cfl = new Set();
			if(empty($conditiontype)) $condval = " and (conditiontype is null or conditiontype = '')";
			else $condval = " and conditiontype = '$conditiontype'"; 
			
			if(empty($fromracknum)) $rackval = " and (fromracknum is null or fromracknum = '')";
			else $rackval = " and fromracknum = '$fromracknum'";
			
			$costdateval = Super::whereDate("costdate", $costdate);
			$wc = "itemnum ='$itemnum' and wonum='$wonum' and customercode='$customercode' and fromwarehouse = '$fromwarehouse' $condval $costdateval $rackval";
			$custbalancepriceSet = $cfl->MboSetRemote("custbalanceprice")->setWhere($wc)
			->MboRemote("unitcost")->MboRemote("unitprice")->MboRemote("unitpricediscount")
			->MboRemote("discountprice")->MboRemote("unitprofit")->MboRemote("unitprofitdiscount")
			->MboRemote("unitcommission")->MboRemote("linecommission")
			->getMboSet();
			$custbalanceprice = $custbalancepriceSet->getMbo();
			
			$custob->unitcost = $custbalanceprice->getString("unitcost");
			$custob->linecost = $quantitymin*$custob->unitcost;
			
			$custob->unitprice = $custbalanceprice->getString("unitprice");
			$custob->lineprice = $quantity*$custob->unitprice;
			
			$custob->unitprofit = $custbalanceprice->getString("unitprofit");
			$custob->lineprofit = $quantity*$custob->unitprofit;
			
			$custob->unitpricediscount = $custbalanceprice->getString("unitpricediscount");
			$custob->linepricediscount = $quantity*$custob->unitpricediscount;
		
			$custob->unitprofitdiscount = $custbalanceprice->getString("unitprofitdiscount");
			$custob->lineprofitdiscount = $quantity*$custob->unitprofitdiscount;
			
			$custob->discountprice = $custbalanceprice->getString("discountprice");
			
			$custob->unitcommission = $custbalanceprice->getString("unitcommission");
			$custob->linecommission = $custbalanceprice->getString("linecommission");
			//END GET COST & PRICE
			
			$custob->actualdate = $actualdate;
			$custob->enterdate = $enterdate;
			$custob->changedate = null;
			$custob->enterby = $enterby;
			$custob->changeby = null;
			$custob->importkey = $billinglineid;
			ActionSet::InsertCustUseTrans($custob);
			
			
			
		}
	}
	public static function InsertCustUseTrans($custob){
		
		/*
		customercode itemnum description fromwarehouse fromracknum conditiontype costtype costdate transtype refund quantity wonum billingnum billinglinenum invoicenum invoicelinenum
		unitcost linecost unitprice lineprice unitprofit lineprofit unitpricediscount linepricediscount unitprofitdiscount lineprofitdiscount
		actualdate enterdate changedate enterby changeby
		*/
		
		$p = new Set();
		$s = new Set();
		$custusetransSet = $p->MboSetRemote("custusetrans")
		->setWhere("billingnum='".$custob->billingnum."' and billinglinenum='".$custob->billinglinenum."' and transtype='".$custob->transtype."'")->getMboSet();
		$cnt = $custusetransSet->counts();
		
		if($cnt > 0){
			$custusetransSet = $s->MboSetRemote("custusetrans")->setWhere("billingnum='".$custob->billingnum."' and billinglinenum='".$custob->billinglinenum."' and transtype='".$custob->transtype."'")
			->setValue("customercode", $custob->customercode)->setValue("itemnum", $custob->itemnum)->setValue("description", $custob->description)->setValue("fromwarehouse", $custob->fromwarehouse)
			->setValue("fromracknum", $custob->fromracknum)->setValue("conditiontype", $custob->conditiontype)
			->setValue("costtype", $custob->costtype)->setValue("costdate", $custob->costdate)->setValue("transtype", $custob->transtype)->setValue("refund", $custob->refund)
			->setValue("quantity", $custob->quantity)->setValue("wonum", $custob->wonum)->setValue("billingnum", $custob->billingnum)->setValue("billinglinenum", $custob->billinglinenum)
			->setValue("unitcost", $custob->unitcost)->setValue("linecost", $custob->linecost)
			->setValue("unitprice", $custob->unitprice)->setValue("lineprice", $custob->lineprice)->setValue("unitprofit", $custob->unitprofit)->setValue("lineprofit", $custob->lineprofit)
			->setValue("unitpricediscount", $custob->unitpricediscount)->setValue("linepricediscount", $custob->linepricediscount)->setValue("unitprofitdiscount", $custob->unitprofitdiscount)
			->setValue("lineprofitdiscount", $custob->lineprofitdiscount)->setValue("actualdate", $custob->actualdate)->setValue("importkey", $custob->importkey) 
			->setValue("enterdate", $custob->enterdate)->setValue("changedate", $custob->changedate)->setValue("enterby", $custob->enterby)->setValue("changeby", $custob->changeby)
			->setValue("unitcommission", $custob->unitcommission)->setValue("linecommission", $custob->linecommission)
			->change();
		}else{
			$custusetransSet = $s->MboSetRemote("custusetrans")
			->setValue("customercode", $custob->customercode)->setValue("itemnum", $custob->itemnum)->setValue("description", $custob->description)->setValue("fromwarehouse", $custob->fromwarehouse)
			->setValue("fromracknum", $custob->fromracknum)->setValue("conditiontype", $custob->conditiontype)
			->setValue("costtype", $custob->costtype)->setValue("costdate", $custob->costdate)->setValue("transtype", $custob->transtype)->setValue("refund", $custob->refund)
			->setValue("quantity", $custob->quantity)->setValue("wonum", $custob->wonum)->setValue("billingnum", $custob->billingnum)->setValue("billinglinenum", $custob->billinglinenum)
			->setValue("unitcost", $custob->unitcost)->setValue("linecost", $custob->linecost)
			->setValue("unitprice", $custob->unitprice)->setValue("lineprice", $custob->lineprice)->setValue("unitprofit", $custob->unitprofit)->setValue("lineprofit", $custob->lineprofit)
			->setValue("unitpricediscount", $custob->unitpricediscount)->setValue("linepricediscount", $custob->linepricediscount)->setValue("unitprofitdiscount", $custob->unitprofitdiscount)
			->setValue("lineprofitdiscount", $custob->lineprofitdiscount)->setValue("actualdate", $custob->actualdate)->setValue("importkey", $custob->importkey) 
			->setValue("enterdate", $custob->enterdate)->setValue("changedate", $custob->changedate)->setValue("enterby", $custob->enterby)->setValue("changeby", $custob->changeby)
			->setValue("unitcommission", $custob->unitcommission)->setValue("linecommission", $custob->linecommission)
			->add();
		}
		
		
		
		
	}
	public static function ValidateStock(){
		$billingnum = $_POST["validatestock"];
		$sc = new Set();
		$customerSet = $sc->MboSetRemote("billing")->setWhere("billingnum='$billingnum'")->getMboSet();
		$customer = $customerSet->getMbo();
		$customercode = $customer->getString("customercode");
		$info = new stdClass();
		
		$ss = new Set();
		$billineSet = $ss->MboSetRemote("billingline")->setWhere("billingnum='$billingnum'")->MboRemote("sum(lineprice) as sumlineprice")->getMboSet();
		$billline = $billineSet->getMbo();
		$sumlineprice = $billline->getString("sumlineprice");
		if(empty($sumlineprice)) {
			$msg = "zeroprice";
		}else{
			$set = new Set();
			$billinglineSet = $set->MboSetRemote("billingline bl")
			->setRelationship("custbalance cb", 
			"cb.itemnum = bl.itemnum 
			and cb.wonum = bl.wonum
			and cb.fromwarehouse = bl.fromwarehouse
			and (cb.costdate = bl.costdate or coalesce(bl.costdate, '') = coalesce (cb.costdate, '') )"
			)
			->setWhere("bl.billingnum='$billingnum' ")
			->group("cb.itemnum, cb.wonum, cb.fromwarehouse, cb.costdate")
			->MboRemote("bl.billingnum")->MboRemote("(select sum(quantity) from billingline bl2 where 
				bl2.itemnum = bl.itemnum
				and bl2.wonum = bl.wonum 
				and bl2.fromwarehouse = bl.fromwarehouse and (bl2.costdate = bl.costdate or coalesce(bl.costdate, '') = coalesce (bl2.costdate, '') ) 
				and bl2.billingnum = '$billingnum'
				) soldqty")->MboRemote("cb.itemnum")
			->MboRemote("(
				select sum(curbal) from custbalance cb2
				where cb2.itemnum = bl.itemnum 
				and cb2.wonum = bl.wonum 
				and cb2.fromwarehouse = bl.fromwarehouse 
				and (cb2.costdate = bl.costdate or coalesce(bl.costdate, '') = coalesce (cb2.costdate, '') ) 
				and cb2.customercode = '$customercode' 
				) curbal")
			->MboRemote("cb.fromwarehouse")->MboRemote("cb.wonum")->MboRemote("cb.costdate")
			->getMboSet();
			$cnt = $billinglineSet->counts();
			
			for($i=0; $i<$cnt; $i++){
				$bill = $billinglineSet->getMbo();
				$itemnum = $bill->getString("itemnum");
				$billingnum = $bill->getString("billingnum");
				$curbal = $bill->getString("curbal");
				$soldqty = $bill->getString("soldqty");
				$fromwarehouse = $bill->getString("fromwarehouse");
				$wonum = $bill->getString("wonum");
				$costdate = $bill->getString("costdate");
				
				$info->itemnum = $itemnum;
				$info->wonum = $wonum;
				$info->fromwarehouse = $fromwarehouse;
				$info->costdate = $costdate;
				$info->soldqty = $soldqty;
				$info->curbal = $curbal;
					
				if($soldqty > $curbal){
					$msg = "false";				
					break;
				}else{
					if($soldqty < 0){
						$msg = "false";
						break;
					}else{
						$msg = "true";
					}
				}
			}
		}
		
		Set::throws(["msg"=>$msg, "info"=>$info]);
	}
	public static function CustomerIssuedFIFOLIFO($fl){
		
		$billingnum = $fl->billingnum;
		$itemnum = $fl->itemnum;
		$customercode = $fl->customercode;
		$conditiontype = $fl->conditiontype;
		$issuedqty = $fl->issuedqty;
		$issuetype = $fl->issuetype;
		$actualdate  = $fl->actualdate;
		$enterdate = Xbo::setEnterDate();
		$enterby  = Xbo::getLoginUser();
		
		if($issuedqty<0) $issuedqty = $issuedqty*-1;
		
		if(empty($conditiontype)) $condval = " and (custbalance.conditiontype is null or custbalance.conditiontype = '')";
		else $condval = " and custbalance.conditiontype = '$conditiontype'"; 
		
		if(empty($racknum)) $rackval = " and (custbalance.racknum is null or custbalance.racknum = '')";
		else $rackval = " and custbalance.racknum = 'racknum'"; 
		
		$fifowhereclause = "custbalance.itemnum='$itemnum' and custbalance.customercode='$customercode' $condval and custbalance.curbal>0";
				
		$set2 = new Set();
		$sumifSet = $set2->MboSetRemote("custbalance")
					->setRelationship("matusetrans", 
					"custbalance.itemnum = matusetrans.itemnum 
					and custbalance.wonum = matusetrans.wonum
					and custbalance.fromwarehouse = matusetrans.warehouse
					and (custbalance.costdate = matusetrans.costdate or coalesce(custbalance.costdate, '') = coalesce (matusetrans.costdate, '') )"
					)->setWhere($fifowhereclause)
					->MboRemote("sum(custbalance.curbal) as totalqty")->getMboSet();
		
		$sumif = $sumifSet->getMbo();
		$totalqty = $sumif->getString("totalqty");	
		
		if($issuetype == "FIFO") $orderby = "asc";
		if($issuetype == "LIFO") $orderby = "desc";
		if($issuetype == "AVG") $orderby = null;
		
		$set3 = new Set();
		$mtsSet = $set3->MboSetRemote("custbalance")->order("custbalance.costdate $orderby")
		->setRelationship("matusetrans", 
			"custbalance.itemnum = matusetrans.itemnum 
			and custbalance.wonum = matusetrans.wonum
			and custbalance.fromwarehouse = matusetrans.warehouse
			and (custbalance.costdate = matusetrans.costdate or coalesce(custbalance.costdate, '') = coalesce (matusetrans.costdate, '') )
			")
		->setWhere("custbalance.customercode = '$customercode' and custbalance.itemnum='$itemnum' and custbalance.curbal>0")
		->MboRemote("custbalance.curbal")->MboRemote("custbalance.fromracknum")->MboRemote("custbalance.costtype")->MboRemote("custbalance.costdate")->MboRemote("matusetrans.unitcost")->MboRemote("custbalance.batchnum")
		->MboRemote("custbalance.expdate")->MboRemote("custbalance.wonum")->MboRemote("custbalance.customercode")->MboRemote("matusetrans.description")->MboRemote("matusetrans.unitprice")
		->MboRemote("matusetrans.unitpricediscount")->MboRemote("matusetrans.glcreditacct")->MboRemote("matusetrans.gldebitacct")
		->MboRemote("matusetrans.primarydiscount")->MboRemote("matusetrans.secondarydiscount")->MboRemote("matusetrans.discountprice")
		->MboRemote("matusetrans.unitcommission")
		->MboRemote("custbalance.fromwarehouse")
		->MboRemote("custbalance.custbalanceid")->getMboSet();
		
		$cnt = $mtsSet->counts();
		
		if($totalqty == null) $totalqty = 0;
		$ar = [];
		if($cnt >0){
				for ($i=0; $i<$cnt; $i++){
					$ch = new stdClass();
					$mts = $mtsSet->getMbo();
					$quantity = $mts->getString("curbal");
					$costdate = $mts->getString("costdate");
					$unitcost = $mts->getString("unitcost");
					$batchnum = $mts->getString("batchnum");
					$expireddate = $mts->getString("expdate");
					$description = $mts->getString("description");
					$unitprice = $mts->getString("unitprice");
					$unitpricediscount = $mts->getString("unitpricediscount");
					$unitcommission = $mts->getString("unitcommission");
					$primarydiscount = $mts->getString("primarydiscount");
					$secondarydiscount = $mts->getString("secondarydiscount");
					$discountprice = $mts->getString("discountprice");
					$gldebitacct = $mts->getString("gldebitacct");
					$glcreditacct = $mts->getString("glcreditacct");
					$costtype = $mts->getString("costtype");
					$custbalanceid = $mts->getString("custbalanceid");
					
					$ch->unitcost = $unitcost;
					$ch->fromracknum = $mts->getString("fromracknum");			
					$ch->fromwarehouse = $mts->getString("fromwarehouse");						
					$ch->quantity = $quantity;
					$ch->description = $description;
					$ch->costdate = $costdate;					
					$ch->batchnum = $batchnum;
					$ch->expdate = $expireddate;
					$ch->unitprice = $unitprice;
					$ch->unitpricediscount = $unitpricediscount;
					$ch->primarydiscount = $primarydiscount;
					$ch->secondarydiscount = $secondarydiscount;
					$ch->discountprice = $discountprice;
					$ch->unitcommission = $unitcommission;
					$ch->id = $custbalanceid;
					$ch->customercode = $mts->getString("customercode");
					$ch->wonum = $mts->getString("wonum");
					$ch->actualdate = $actualdate;
					$ch->costtype = $costtype;
					array_push($ar, $ch);
				}
				
				$countar = count($ar);
				$issuedqtytemp = $issuedqty;
				
				for($i=0; $i<$countar; $i++){
					
					$ch = $ar[$i];
					$custbalanceid = $ch->id;
					$costdate = $ch->costdate;
					$unitcost = $ch->unitcost;
					$oldqty = $ch->quantity;
					$batchnum = $ch->batchnum;
					$expireddate = $ch->expdate;
					$customercode = $ch->customercode;
					$wonum = $ch->wonum;
					$description = $ch->description;
					$unitprice = $ch->unitprice;
					$unitpricediscount = $ch->unitpricediscount;
					$primarydiscount = $ch->primarydiscount;
					$secondarydiscount = $ch->secondarydiscount;
					$discountprice = $ch->discountprice;
					$fromwarehouse = $ch->fromwarehouse;
					$fromracknum = $ch->fromracknum;
					$expdate = $ch->expdate;
					$actualdate = $ch->actualdate;
					$costtype = $ch->costtype;
					$unitcommission = $ch->unitcommission;
					$ss = new Set();
					$itemSet = $ss->MboSetRemote("item")->setWhere("itemnum='$itemnum'")->MboRemote("spectext")->MboRemote("issueunit")->getMboSet();
					$item = $itemSet->getMbo();
					
					$spectext = $item->getString("spectext");
					$issueunit = $item->getString("issueunit");
					
					$issuedqtytemp = $issuedqtytemp - $oldqty;
					
					if($issuedqtytemp<0){
						$newqty = $issuedqtytemp*-1;
						$restqty = $oldqty+$issuedqtytemp;
						
					}else{
						$newqty = $oldqty-$oldqty;
						$restqty = $oldqty;
					}
					
					$newqty = $oldqty-$issuedqty;
					$linecost = $restqty*$unitcost;
					
					$billinglinenum = Xbo::getLineNumber("billingline", "billinglinenum", "billingnum='$billingnum'");
					
					if($restqty<=0) {
						break;
					}else{
						$lineprice = $unitprice*$restqty;
						$linepricediscount = $unitpricediscount*$restqty;
						
						$unitprofit = $unitprice-$unitcost;
						$unitprofitdiscount = $unitpricediscount-$unitcost;
						
						$lineprofit = $unitprofit*$restqty;
						$lineprofitdiscount = $unitprofitdiscount*$restqty;
						
						$linecommission = $unitcommission*$restqty;
						
						$st = new Set();
						
						$st->MboSetRemote("billingline")->setValue("billingnum", $billingnum)->setValue("billinglinenum", $billinglinenum)
						->setValue("itemnum", $itemnum)->setValue("description", $description)
						->setValue("spectext", $spectext)->setValue("unitcost", $unitcost)->setValue("linecost", $linecost)
						->setValue("unitprice", $unitprice)->setValue("lineprice", $lineprice)
						->setValue("unitprofit", $unitprofit)->setValue("lineprofit", $lineprofit)
						->setValue("unitpricediscount", $unitpricediscount)->setValue("linepricediscount", $linepricediscount)
						->setValue("unitprofitdiscount", $unitprofitdiscount)->setValue("lineprofitdiscount", $lineprofitdiscount)
						->setValue("quantity", $restqty)->setValue("issueunit", $issueunit)
						->setValue("primarydiscount", $primarydiscount)->setValue("secondarydiscount", $secondarydiscount)->setValue("discountprice", $discountprice)
						->setValue("conditiontype", $conditiontype)->setValue("wonum", $wonum)->setValue("fromwarehouse", $fromwarehouse)->setValue("fromracknum", $fromracknum)
						->setValue("expdate", $expireddate)->setValue("costdate", $costdate)->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)
						->setValue("actualdate", $actualdate)->setValue("gldebitacct", $gldebitacct)->setValue("glcreditacct", $glcreditacct)
						->setValue("costtype", $costtype)->setValue("unitcommission", $unitcommission)->setValue("linecommission", $linecommission)
						->add();
						
					}
				}
		}else{
		}
	}
	public static function ProcessWriteBillings($value){
		$retval = new stdClass();
		$billingnum  = $value["billingnum"];
		$itemnum = $value["itemnum"];
		$customercode = $value["customercode"];
		$conditiontype = $value["conditiontype"];
		$expireddate = $value["expdate"];
		$costdate = $value["costdate"];
		$wonum = $value["wonum"];
		$unitprice = $value["unitprice"];
		$unitpricediscount = $value["unitpricediscount"];
		$curbal = $value["curbal"];
		$soldqty = $value["soldqty"];
		$actualdate = $value["actualdate"];
		if($soldqty == 0 ){
			$retval->msg = "zerosoldqty";
		}else{
			if($soldqty>$curbal){
				$retval->msg = "zerosoldqty";
			}else{
				$billinglinenum = Xbo::getLineNumber("billingline", "billinglinenum", "billingnum='$billingnum'");
				if(empty($conditiontype)) $condval = " and (conditiontype is null or conditiontype= '')";
				else $condval = " and conditiontype = '$conditiontype'";
				
				$costdateval = Super::whereDate("costdate", $costdate);
				$expdateval = Super::whereDate("expireddate", $expireddate);	
				
				$enterdate = Xbo::setEnterDate();
				$enterby  = Xbo::getLoginUser();
				
				$set = new Set();
				
				
				$matuseSet = $set->MboSetRemote("matusetrans")->setWhere("wonum='$wonum' and itemnum='$itemnum' and unitprice='$unitprice' and unitpricediscount='$unitpricediscount' $condval $costdateval $expdateval ")
				->MboRemote("description")->MboRemote("unitcost")->MboRemote("linecost")->MboRemote("unitprice")->MboRemote("lineprice")->MboRemote("unitprofit")->MboRemote("lineprofit")
				->MboRemote("unitpricediscount")->MboRemote("linepricediscount")->MboRemote("unitprofitdiscount")->MboRemote("lineprofitdiscount")
				->MboRemote("primarydiscount")->MboRemote("secondarydiscount")->MboRemote("discountprice")->MboRemote("conditiontype")
				->MboRemote("wonum")->MboRemote("warehouse")->MboRemote("racknum")->MboRemote("costdate")->MboRemote("expireddate")
				->MboRemote("gldebitacct")->MboRemote("glcreditacct")->MboRemote("issuetype")->MboRemote("unitcommission")->MboRemote("linecommission")
				->getMboSet();
				
				
				$matuse = $matuseSet->getMbo();
				$description = $matuse->getString("description");
				$unitcost = $matuse->getString("unitcost");
				$linecost = $unitcost*$soldqty;
				$unitprice = $matuse->getString("unitprice");
				$lineprice = $unitprice*$soldqty;
				$unitprofit = $matuse->getString("unitprofit");
				$lineprofit = $unitprofit*$soldqty;
				$unitpricediscount = $matuse->getString("unitpricediscount");
				$linepricediscount = $unitpricediscount*$soldqty;
				$unitprofitdiscount = $matuse->getString("unitprofitdiscount");
				$lineprofitdiscount = $unitprofitdiscount*$soldqty;
				$primarydiscount = $matuse->getString("primarydiscount");
				$secondarydiscount = $matuse->getString("secondarydiscount");
				$discountprice = $matuse->getString("discountprice");
				$wonum = $matuse->getString("wonum");
				$warehouse = $matuse->getString("warehouse");
				$racknum = $matuse->getString("racknum");
				$costdate = $matuse->getString("costdate");
				$expdate = $matuse->getString("expireddate");
				$conditiontype = $matuse->getString("conditiontype");
				$gldebitacct =  $matuse->getString("gldebitacct");
				$glcreditacct =  $matuse->getString("glcreditacct");
				$issuetype =  $matuse->getString("issuetype");
				$unitcommission =  $matuse->getString("unitcommission");
				$linecommission =  $matuse->getString("linecommission");
				$iset = new Set();
				$itemSet = $iset->MboSetRemote("item")->setWhere("itemnum='$itemnum'")->MboRemote("spectext")->MboRemote("issueunit")->getMboSet();
				$item = $itemSet->getMbo();
				
				$spectext = $item->getString("spectext");
				$issueunit = $item->getString("issueunit");
				
				$set = new Set();
				$set->MboSetRemote("billingline")
				->setValue("billingnum", $billingnum)->setValue("billinglinenum", $billinglinenum)->setValue("itemnum", $itemnum)
				->setValue("description", $description)->setValue("spectext", $spectext)->setValue("unitcost", $unitcost)->setValue("issueunit", $issueunit)
				->setValue("linecost", $linecost)->setValue("unitprice", $unitprice)->setValue("lineprice", $lineprice)->setValue("quantity", $soldqty)
				->setValue("unitprofit", $unitprofit)->setValue("unitpricediscount", $unitpricediscount)
				->setValue("linepricediscount", $linepricediscount)->setValue("lineprofit", $lineprofit)
				->setValue("unitprofitdiscount", $unitprofitdiscount)->setValue("lineprofitdiscount", $lineprofitdiscount)
				->setValue("primarydiscount", $primarydiscount)->setValue("secondarydiscount", $secondarydiscount)
				->setValue("discountprice", $discountprice)->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)
				->setValue("wonum", $wonum)->setValue("fromwarehouse", $warehouse)->setValue("fromracknum", $racknum)
				->setValue("costdate", $costdate)->setValue("expdate", $expdate)->setValue("conditiontype", $conditiontype)
				->setValue("gldebitacct", $gldebitacct)->setValue("glcreditacct", $glcreditacct)->setValue("actualdate", $actualdate)
				->setValue("costtype", $issuetype)->setValue("unitcommission", $unitcommission)->setValue("linecommission", $linecommission)
				->add();
				
				$retval->msg = "written";
			}
			
		}
		
		return $retval;
	}
	
	public static function WriteBillings(){
		$value = $_POST["billingitems"];
		$cnt = count($value);
		$newmsg = null;
		for($i=0; $i<$cnt; $i++){
			$get = ActionSet::ProcessWriteBillings($value[$i]);
			$msg = $get->msg;
			if($msg == "notwritten"){
				$newmsg = $msg;
				break;
			}else{
				$newmsg = "written";
			}
		}
		Set::throws(["msg"=>$newmsg]);
	}
	
	public static function ProcessWriteBillingsFL($value){
		$retval = new stdClass();
		$billingnum  = $value["billingnum"];
		$itemnum = $value["itemnum"];
		$customercode = $value["customercode"];
		$conditiontype = $value["conditiontype"];
		$unitprice = $value["unitprice"];
		$unitpricediscount = $value["unitpricediscount"];
		$curbal = $value["curbal"];
		$soldqty = $value["soldqty"];
		$issuetype = $value["issuetype"];
		$actualdate = $value["actualdate"];
		
		$fl = new stdClass();
		$fl->billingnum = $billingnum;
		$fl->itemnum = $itemnum;
		$fl->customercode = $customercode;
		$fl->conditiontype = $conditiontype;		
		$fl->issuedqty = $soldqty;
		$fl->issuetype = $issuetype;
		$fl->actualdate = $actualdate;
		
		ActionSet::CustomerIssuedFIFOLIFO($fl);
		$retval->msg = "written";
		return $retval;
	}
	public static function WriteBillingsFL(){
		$value = $_POST["billingitemsfl"];
		$cnt = count($value);
		$newmsg = null;
		for($i=0; $i<$cnt; $i++){
			$get = ActionSet::ProcessWriteBillingsFL($value[$i]);
			$msg = $get->msg;
			if($msg == "notwritten"){
				$newmsg = $msg;
				break;
			}else{
				$newmsg = "written";
			}
		}
		Set::throws(["msg"=>$newmsg]);
	}
} 

Class ValidateTables {
	public static function tbcx(){
		$dl = explode("|", $_GET["tbcx"]);
		$set = new Set();
	
		if ($dl[0] == "billing") {
			if ($dl[2] == "billingpayment") echo json_encode(["msg" => "relateddata", "rltbl" => "invoice"]);
			if ($dl[2] == "billingline") {
				$colarr = explode("^", $dl[3]);
				$flag = 0;
				$ss = new stdClass();
				for ($i = 0; $i < count($colarr); $i++) {
					$tableid = explode("`", $colarr[$i]) [1];
					$popayment = $set->MboSetRemote("billingline")->setWhere("billinglineid='$tableid'")->MboRemote("billingnum")->MboRemote("billinglineid")->getMboSet()->getMbo();
					$billingnum = $popayment->getString("billingnum");
					$billinglineid = $popayment->getString("billinglineid");
					$set2 = new Set();
					$invoicelineSet = $set2->MboSetRemote("invoiceline")->setWhere("billingnum='$billingnum'")->MboRemote("billingnum")->MboRemote("invoicenum")->MboRemote("invoicelinenum")->getMboSet();
					$invoiceline = $invoicelineSet->getMbo();
					$invoicenum = $invoiceline->getString("invoicenum");
					$invoicelinenum = $invoiceline->getString("invoicelinenum");
					
					
					if ($invoicelineSet->counts() > 0) {
						$flag = $flag + 1;
						$ss->invoicenum = $invoicenum;
						$ss->invoicelinenum = $invoicelinenum;
						Set::throws(["msg" => "relateddata", "rltbl" => "invoiceline", "data"=> $ss]);
						return null;
					}
					else {
						$flag = 0;
					}
				}
				
				
				if ($flag > 0) {
					Set::throws(["msg" => "relateddata", "rltbl" => "invoiceline"]);
				}
				else {
					Set::throws(["msg" => "omdelete"]);
				}
				
			}
		}
	}
}
	

?>