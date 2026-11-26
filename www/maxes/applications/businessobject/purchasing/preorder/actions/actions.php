<?php
require_once ("../../../eat.php");

Class ActionSet {
	public static function createSalesQuotation(){
		$createso = $_POST["createsalesquotation"];
		$preordernum = $_POST["pronum"];
		$salesquotenum = $_POST["salesquotenum"];
		$sodesc = $_POST["description"];
		$whereclause = "preordernum='$preordernum'";
		
		$set = new Set();
		$preorderSet = $set->MboSetRemote("preorder")->setWhere($whereclause)
		->MboRemote("description")
		->MboRemote("customercode")
		->MboRemote("customercontact")
		->MboRemote("salesrep")
		->MboRemote("remarks")
		->MboRemote("pretaxtotal")
		->MboRemote("totaltax")
		->MboRemote("discountpercentage")
		->MboRemote("discountprice")
		->MboRemote("totaltax2")
		->MboRemote("totalcost")
		->MboRemote("orgcode")
		->MboRemote("sitecode")
		->getMboSet();
		
		$preorder = $preorderSet->getMbo();
		$customercode = $preorder->getString("customercode");
		$customercontact = $preorder->getString("customercontact");
		$salesrep = $preorder->getString("salesrep");
		$remarks = $preorder->getString("remarks");
		$pretaxtotal = $preorder->getString("pretaxtotal");
		$totaltax = $preorder->getString("totaltax");
		$discountpercentage = $preorder->getString("discountpercentage");
		$discountprice = $preorder->getString("discountprice");
		$totaltax2 = $preorder->getString("totaltax2");
		$totalcost = $preorder->getString("totalcost");
		$orgcode = $preorder->getString("orgcode");
		$sitecode = $preorder->getString("sitecode");
		$enterdate = Xbo::setEnterDate();
		$enterby = Xbo::getLoginUser();
		
		$set = new Set();
		$checkproExist = $set->MboSetRemote("salesquotation")->setWhere($whereclause)->MboRemote("salesquotenum")->getMboSet();
		$cnt = $checkproExist->counts();
		$cnt = $checkproExist->counts();
		if($cnt == 0){
			$set = new Set();
			$set->MboSetRemote("salesquotation")
			->setValue("salesquotenum", $salesquotenum)
			->setValue("description", $sodesc) 
			->setValue("customercode", $customercode)
			->setValue("preordernum", $preordernum)
			->setValue("contactname", $customercontact)
			->setValue("employeesid", $salesrep)
			->setValue("status", "new")
			->setValue("statusdate", $enterdate)
			->setValue("orderdate", $enterdate)
			->setValue("enterdate", $enterdate)
			->setValue("enterby", $enterby)
			->setValue("changeby", $enterby)
			->setValue("changedate", $enterdate)
			->setValue("orgcode", $orgcode)
			->setValue("sitecode", $sitecode)
			->setValue("pretaxtotal", $pretaxtotal)
			->setValue("totaltax", $totaltax)
			->setValue("totalprice", $totalcost)
			->add();
			
			
			$set = new Set();
			$set->MboSetRemote("salesquotationstatus")
			->setValue("salesquotenum", $salesquotenum)
			->setValue("changeby", $enterby)
			->setValue("changedate", $enterdate)
			->setValue("remarks", "created from Preorder: $preordernum")
			->setValue("status", "new")
			->add();
			
			$set = new Set();
			$preorderlineSet = $set->MboSetRemote("preorderline")
			->setWhere($whereclause)
			->MboRemote("preorderlinenum")
			->MboRemote("description")
			->MboRemote("remarks")
			->MboRemote("quantity")
			->MboRemote("taxcode")
			->MboRemote("taxcost")
			->MboRemote("unitcost")
			->MboRemote("linecost")
			->MboRemote("orderunit")
			->getMboSet();
			
			$cntprl = $preorderlineSet->counts();
			for($i=0; $i<$cntprl; $i++){
				$preorderline = $preorderlineSet->getMbo();
				$salesquotationlinenum = $preorderline->getString("preorderlinenum");
				$linetype = "services";
				$description = $preorderline->getString("description");
				$remarks = $preorderline->getString("remarks");
				$taxcode = $preorderline->getString("taxcode");
				$taxprice = $preorderline->getString("taxcost");
				$taxpricediscount  = $taxprice;
				$enterby = $enterby;
				$enterdate = $enterdate;
				$changeby = $enterby;
				$changedate = $enterdate;
				$sellqty = $preorderline->getString("quantity");
				$orderunit = $preorderline->getString("orderunit");
				$customercode = $customercode;
				$unitprice = $preorderline->getString("unitcost");
				$unitpricediscount = $unitprice;
				$lineprice = $preorderline->getString("linecost");
				$linepricediscount = $lineprice;
				$sentstatus = "NONE";
				$set = new Set();
				$set->MboSetRemote("salesquotationline")
				->setValue("salesquotenum", $salesquotenum)
				->setValue("salesquotationlinenum", $salesquotationlinenum)
				->setValue("linetype", $linetype)
				->setValue("description", $description)
				->setValue("remarks", $remarks)
				->setValue("unitprice", $unitprice)
				->setValue("lineprice", $lineprice)
				->setValue("taxcode", $taxcode)
				->setValue("taxprice", $taxprice)
				->setValue("enterby", $enterby)
				->setValue("enterdate", $enterdate)
				->setValue("changeby", $changeby)
				->setValue("changedate", $changedate)
				->setValue("sellqty", $sellqty)
				->setValue("orderunit", $orderunit)
				->setValue("customercode", $customercode)
				->add();
				
			}
			$set = new Set();
			$preorderlineSet = $set->MboSetRemote("preorderline")->setWhere("preordernum='$preordernum'")
			->setValue("salesquotenum", $salesquotenum)->change();
			
			$getAutoNum = new Super();
			$getAutoNum->getAutoNumber("salesquotenum", "update_next_value");
			$msg = "salesquotationcreated";
			$sonum = $sonum;
		}else{
			$checkpro = $checkproExist->getMbo();
			$sonum = $checkpro->getString("salesquotenum");
			$msg = "salesquotationexist";
			$sonum = $sonum;
		}
		
		$ss = new stdClass;
		$ss->msg = $msg;
		$ss->salesquotenum = $salesquotenum;
		Set::throws($ss);
	}
	public static function createSO(){
		$createso = $_POST["createso"];
		$preordernum = $_POST["pronum"];
		$sonum = $_POST["sonum"];
		$sodesc = $_POST["sodesc"];
		$whereclause = "preordernum='$preordernum'";
		$set = new Set();
		$preorderSet = $set->MboSetRemote("preorder")->setWhere($whereclause)
		->MboRemote("description")
		->MboRemote("customercode")
		->MboRemote("customercontact")
		->MboRemote("salesrep")
		->MboRemote("remarks")
		->MboRemote("pretaxtotal")
		->MboRemote("totaltax")
		->MboRemote("discountpercentage")
		->MboRemote("discountprice")
		->MboRemote("totaltax2")
		->MboRemote("totalcost")
		->MboRemote("orgcode")
		->MboRemote("sitecode")
		->getMboSet();
		
		$preorder = $preorderSet->getMbo();
		$customercode = $preorder->getString("customercode");
		$customercontact = $preorder->getString("customercontact");
		$salesrep = $preorder->getString("salesrep");
		$remarks = $preorder->getString("remarks");
		$pretaxtotal = $preorder->getString("pretaxtotal");
		$totaltax = $preorder->getString("totaltax");
		$discountpercentage = $preorder->getString("discountpercentage");
		$discountprice = $preorder->getString("discountprice");
		$totaltax2 = $preorder->getString("totaltax2");
		$totalcost = $preorder->getString("totalcost");
		$orgcode = $preorder->getString("orgcode");
		$sitecode = $preorder->getString("sitecode");
		$enterdate = Xbo::setEnterDate();
		$enterby = Xbo::getLoginUser();
		
		if($totalcost>0) $$totalcost = $totalcost *-1;
		$unpaidprice = $totalcost *-1;
		
		$set = new Set();
		$checkproExist = $set->MboSetRemote("so")->setWhere($whereclause)->MboRemote("sonum")->getMboSet();
		$cnt = $checkproExist->counts();
		if($cnt == 0){
			$paymenttype = "cashonbank";
			$set = new Set();
			$set->MboSetRemote("so")
			->setValue("sonum", $sonum)
			->setValue("description", $sodesc)
			->setValue("customercode", $customercode)
			->setValue("preordernum", $preordernum)
			->setValue("contactname", $customercontact)
			->setValue("employeesid", $salesrep)
			->setValue("status", "new")
			->setValue("statusdate", $enterdate)
			->setValue("orderdate", $enterdate)
			->setValue("enterdate", $enterdate)
			->setValue("enterby", $enterby)
			->setValue("changeby", $enterby)
			->setValue("changedate", $enterdate)
			->setValue("orgcode", $orgcode)
			->setValue("sitecode", $sitecode)
			->setValue("pretaxtotal", $pretaxtotal)
			->setValue("totaltax", $totaltax)
			->setValue("totalprice", $totalcost)
			->setValue("discountpercentage", $discountpercentage)
			->setValue("discountprice", $discountprice)
			->setValue("discountedtotaltax", $totaltax)
			->setValue("discountedtotalprofit", $pretaxtotal)
			->setValue("discountedtotalprice", $totalcost)
			->setValue("unpaidprice", $unpaidprice)
			->setValue("paymenttype", $paymenttype)
			->add();
			
			$set = new Set();
			$set->MboSetRemote("sostatus")
			->setValue("sonum", $sonum)
			->setValue("changeby", $enterby)
			->setValue("changedate", $enterdate)
			->setValue("remarks", "created from Preorder: $preordernum")
			->setValue("status", "new")
			->add();
			
			$set = new Set();
			$preorderlineSet = $set->MboSetRemote("preorderline")
			->setWhere($whereclause)
			->MboRemote("preorderlinenum")
			->MboRemote("description")
			->MboRemote("remarks")
			->MboRemote("quantity")
			->MboRemote("taxcode")
			->MboRemote("taxcost")
			->MboRemote("unitcost")
			->MboRemote("linecost")
			->MboRemote("orderunit")
			->getMboSet();
			
			$cntprl = $preorderlineSet->counts();
			for($i=0; $i<$cntprl; $i++){
				$preorderline = $preorderlineSet->getMbo();
				$solinenum = $preorderline->getString("preorderlinenum");
				$linetype = "services";
				$description = $preorderline->getString("description");
				$remarks = $preorderline->getString("remarks");
				$taxcode = $preorderline->getString("taxcode");
				$taxprice = $preorderline->getString("taxcost");
				$taxpricediscount  = $taxprice;
				$enterby = $enterby;
				$enterdate = $enterdate;
				$changeby = $enterby;
				$changedate = $enterdate;
				$sellqty = $preorderline->getString("quantity");
				$orderunit = $preorderline->getString("orderunit");
				$customercode = $customercode;
				$unitprice = $preorderline->getString("unitcost");
				$unitpricediscount = $unitprice;
				$lineprice = $preorderline->getString("linecost");
				$linepricediscount = $lineprice;
				$sentstatus = "NONE";
				$set = new Set();
				$set->MboSetRemote("soline")
				->setValue("sonum", $sonum)
				->setValue("solinenum", $solinenum)
				->setValue("linetype", $linetype)
				->setValue("description", $description)
				->setValue("remarks", $remarks)
				->setValue("unitprice", $unitprice)
				->setValue("lineprice", $lineprice)
				->setValue("unitpricediscount", $unitpricediscount)
				->setValue("linepricediscount", $linepricediscount)
				->setValue("taxcode", $taxcode)
				->setValue("taxprice", $taxprice)
				->setValue("taxpricediscount", $taxpricediscount)
				->setValue("enterby", $enterby)
				->setValue("enterdate", $enterdate)
				->setValue("changeby", $changeby)
				->setValue("changedate", $changedate)
				->setValue("sellqty", $sellqty)
				->setValue("orderunit", $orderunit)
				->setValue("customercode", $customercode)
				->add();
				
			}
			
			$set = new Set();
			$preorderlineSet = $set->MboSetRemote("preorderline")->setWhere("preordernum='$preordernum'")
			->setValue("sonum", $sonum)->change();
			
			
			$getAutoNum = new Super();
			$getAutoNum->getAutoNumber("so", "update_next_value");
			$msg = "socreated";
			$sonum = $sonum;
		}else{
			$checkpro = $checkproExist->getMbo();
			$sonum = $checkpro->getString("sonum");
			$msg = "soexist";
			$sonum = $sonum;
		}
		
		$ss = new stdClass;
		$ss->msg = $msg;
		$ss->sonum = $sonum;
		Set::throws($ss);
	}
	public static function getItemInfo(){
		$x = $_POST['getItemInfo'];
		$set = new Set();
		$invbalanceSet = $set->MboSetRemote("invbalance")->setRelationship("item", "item.itemnum=invbalance.itemnum ")->setWhere("invbalance.invbalanceid='$x'")->MboRemote("invbalance.itemnum")->MboRemote("invbalance.warehouse")->MboRemote("invbalance.racknum")->MboRemote("invbalance.conditiontype")->MboRemote("invbalance.curbal")->MboRemote("invbalance.orgcode")->MboRemote("invbalance.sitecode")->MboRemote("item.stocktype")->getMboSet("remove_duplicate")->getMbo();
		$itemnum = $invbalanceSet->getString("itemnum");
		$warehouse = $invbalanceSet->getString("warehouse");
		$racknum = $invbalanceSet->getString("racknum");
		$conditiontype = $invbalanceSet->getString("conditiontype");
		$customercode = "";
		$condval = "";
		if (empty($conditiontype)) {
			$condval = "and (conditiontype='' or conditiontype is null)";
		}
		else {
			$condval = "and conditiontype='$conditiontype'";
		}
		$curbal = $invbalanceSet->getString("curbal");
		$stocktype = $invbalanceSet->getString("stocktype");
		$orgcode = $invbalanceSet->getString("orgcode");
		$sitecode = $invbalanceSet->getString("sitecode");
		$costtype = Xbo::InventoryCostType($itemnum, $warehouse, $orgcode);
		$whereclause = "itemnum='$itemnum' and warehouse='$warehouse' $condval and orgcode='$orgcode' and sitecode='$sitecode'";
		if($costtype == "AVG"){
			$set = new Set();
			$invcostSet =  $set->MboSetRemote("invcost")->setWhere($whereclause)->MboRemote("avgcost")->getMboSet();
			$unitcost = $invcostSet->getMbo()->getString("avgcost");
		}else{
			$unitcost = 0;
			 $fifowhereclause = "itemnum='$itemnum' and warehouse='$warehouse' $condval and quantity>0";
			 if($costtype == "FIFO"){
				$orderby = "costdate asc";
			}else{
				if($costtype == "LIFO") {
					$orderby = "costdate desc";
				}else{
					if($orderby == "EXPDATE"){
						$orderby = "expdate asc";
					}else{
						$orderby = "costdate asc";
					}
					
				}
			}
			$ifSet = $set->MboSetRemote("invfifolifocost")->order("")->setWhere($fifowhereclause);
			$iflset = $ifSet->MboRemote("unitcost")->getMboSet();
			$cnt = $iflset->counts();
			if($cnt == 0){
				$unitcost=0;
			}else{
				$ifl = $iflset->getMbo();
				$unitcost = $ifl->getString("unitcost");
			}
			
		}
		
		
		$set = new Set();
		$invprice = $set->MboSetRemote("invprice")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' $condval and defaultprice='Y'")->MboRemote("pricecode")->MboRemote("unitprice")->getMboSet()->getMbo();
		$pricecode = $invprice->getString("pricecode");
		$u = new Xbo();
		$up = $u->getUnitPriceDiscount($orgcode, $itemnum, $warehouse, $conditiontype);
		$unitprice = $u->unitpricediscount;
		if ($unitcost == null) $unitcost = 0;
		$glcreditacct = Xbo::getCustomerRBNIAcct($customercode, $orgcode, $sitecode); 
		$gldebitacct = Xbo::getCashOnBankAcct($orgcode, $sitecode);
		if ($curbal < 1) {
			Set::throws(["msg" => "ZeroBalance"]);
			return null;
		}
		if ($stocktype == "UNSELLABLE") {
			Set::throws(["msg" => "unsellable"]);
			return null;
		}
		
		
		if ($curbal >= 1) {
			Set::throws(["warehouse" => $warehouse, "racknum" => $racknum, "conditiontype" => $conditiontype, "curbal" => $curbal, 
						"costtype" => $costtype, "unitcost" => $unitcost, "gldebitacct" => $gldebitacct, "glcreditacct" => $glcreditacct, 
						"unitprice" => $unitprice, "pricecode" => $pricecode]);
		}
		
		
	}
	public static function sumprocost(){
		$preorderid = $_POST["preorderid"];
		$preordernum = $_POST["sumprocost"];
		
		$set = new Set();
		$proSet = $set->MboSetRemote("preorderline")->setWhere("preordernum = '$preordernum'")->MboRemote("sum(linecost) as sumlinecost")->getMboSet();
		$pro = $proSet->getMbo();
		$sumlinecost = $pro->getString("sumlinecost");
		if($sumlinecost == null) $sumlinecost = 0;
		
		$set = new Set();
		$proSet = $set->MboSetRemote("preorderline")->setWhere("preordernum = '$preordernum'")->MboRemote("sum(taxcost) as sumtaxcost")->getMboSet();
		$pro = $proSet->getMbo();
		$sumtaxcost = $pro->getString("sumtaxcost");
		if($sumtaxcost == null) $sumtaxcost = 0;
		
		$set = new Set();
		$proSet = $set->MboSetRemote("proitem")->setWhere("preordernum = '$preordernum'")->MboRemote("sum(linecost) as sumlinecostitem")->getMboSet();
		$pro = $proSet->getMbo();
		$sumlinecostitem = $pro->getString("sumlinecostitem");
		if($sumlinecostitem == null) $sumlinecostitem = 0;
		
		$set = new Set();
		$proSet = $set->MboSetRemote("proresource")->setWhere("preordernum = '$preordernum'")->MboRemote("sum(margincost) as summargincost")->getMboSet();
		$pro = $proSet->getMbo();
		$summargincost = $pro->getString("summargincost");
		if($summargincost == null) $summargincost = 0;
		
		
		$set = new Set();
		$proSet = $set->MboSetRemote("proresource")->setWhere("preordernum = '$preordernum'")->MboRemote("sum(cost) as sumrescost")->getMboSet();
		$pro = $proSet->getMbo();
		$sumrescost = $pro->getString("sumrescost");
		if($sumrescost == null) $sumrescost = 0;
		
		
		$ob = new stdClass();
		
		$ob->totaltax = $sumtaxcost;
		$ob->pretaxtotal = $sumlinecost;
		$ob->totalcost = $sumtaxcost+$sumlinecost;
		$ob->servicecost = $sumrescost;
		$ob->itemcost = $sumlinecostitem;
		Set::throws($ob);
	}
		
		
	public static function getTaxValue()
	{
		$value = trim($_GET['getTaxValue'], " ");
		$set = new Set();
		echo $set->MboSetRemote("tax")->setWhere("taxcode = '$value'")->MboRemote("value")->getMboSet()->getMbo()->getString("value");
	}
	
	
	
}

Class ValidateTables {	
	
}
	

?>