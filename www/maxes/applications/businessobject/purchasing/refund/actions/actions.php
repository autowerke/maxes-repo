<?php
require_once ("../../../eat.php");

Class ActionSet {
	public static function getItemRefund(){
		$rowid = $_POST["getItemRefund"];
		$itemnum = $_POST["itemnum"];
		$ss = new Set();
		
		$itemsellableSet = $ss->MboSetRemote("itemsellable")->setWhere("itemsellableid='$rowid'")
		->MboRemote("description")->MboRemote("spectext")->MboRemote("manufacturer")
		->MboRemote("itemgroup")->MboRemote("warehouse")->MboRemote("racknum")
		->MboRemote("conditiontype")->MboRemote("unitprice")->MboRemote("primarydiscount")
		->MboRemote("secondarydiscount")
		->getMboSet();
		
		$is = $itemsellableSet->getMbo();
		
		$asd = new stdClass();
		$asd->description = $is->getString("description");
		$asd->spectext = $is->getString("spectext");
		$asd->manufacturer = $is->getString("manufacturer");
		$asd->itemgroup = $is->getString("itemgroup");
		$asd->warehouse = $is->getString("warehouse");
		$asd->racknum = $is->getString("racknum");
		$asd->conditiontype = $is->getString("conditiontype");
		$asd->unitprice = $is->getString("unitprice");
		$asd->primarydiscount = $is->getString("primarydiscount");
		$asd->secondarydiscount = $is->getString("secondarydiscount");
		
		
		$ss=new Set();
		
		$inventorySet = $ss->MboSetRemote("inventory")->setWhere("itemnum='{$itemnum}' and warehouse='{$asd->warehouse}'")->MboRemote("issuetype")->getMboSet();
		$iv = $inventorySet->getMbo();
		
		if(empty($asd->conditiontype)) $condval = " and (conditiontype is null or conditiontype = '') ";
		else $condval = " and conditiontype = '{$asd->conditiontype}' ";
		$ss= new Set();
		$invpriceSet = $ss->MboSetRemote("invprice")->setWhere("itemnum='{$itemnum}' and warehouse='{$asd->warehouse}' $condval and defaultprice='Y'")->MboRemote("pricecode")->getMboSet();
		$ip = $invpriceSet->getMbo();
		
		$asd->costtype = $iv->getString("issuetype");
		$asd->pricecode = $ip->getString("pricecode");
		
		Set::throws($asd);
	}
	public static function getTaxValue($taxcode, $json = null){
		$ts = new Set();
		if(empty($taxcode)){
			$taxvalue = 0;
		}else{
			$taxSet = $ts->MboSetRemote("tax")->setWhere("taxcode='$taxcode'")->MboRemote("value")->getMboSet();
			$tax = $taxSet->getMbo();
			$taxvalue = $tax->getString("value");
			$taxvalue = $taxvalue/100;
		}
		
		if($json!=null){
			Set::throws(["taxvalue"=>$taxvalue]);
		}
		return $taxvalue;
	}
	public static function RefundItemIsExist(){
		$refundnum = $_GET["refunditemisexist"];
		$set =  new Set();
		$rf2Set = $set->MboSetRemote("refundline2")->setWhere("refundnum='$refundnum'")
		->MboRemote("itemnum")->MboRemote("sellqty")->MboRemote("warehouse")->MboRemote("racknum")->MboRemote("conditiontype")
		->MboRemote("refundlinenum")->MboRemote("torefundlinenum")->MboRemote("linetype")->MboRemote("varunitprice")
		->getMboSet();
		$cnt = $rf2Set->counts();
		
		$cn = new stdClass();
		$msgr = [];
		for($i=0; $i<$cnt; $i++){
			$rf2 = $rf2Set->getMbo();
			$itemnum = $rf2->getString("itemnum");
			$sellqty = $rf2->getString("sellqty");
			$warehouse = $rf2->getString("warehouse");
			$racknum = $rf2->getString("racknum");
			$conditiontype = $rf2->getString("conditiontype");
			$refundlinenum = $rf2->getString("refundlinenum");
			$torefundlinenum = $rf2->getString("torefundlinenum");
			$varunitprice = $rf2->getString("varunitprice");
			$linetype = $rf2->getString("linetype");
			
			if($linetype == "services"){
				
			}else{
				if(empty($conditiontype)) $condval = " and (invbalance.conditiontype is null or invbalance.conditiontype = '') ";
				else $condval = " and invbalance.conditiontype = '$conditiontype' ";

				if(empty($racknum)) $fromrack = " and (invbalance.racknum is null or invbalance.racknum = '') ";
				else $fromrack = " and invbalance.racknum = '$racknum' ";
							
				$ss = new Set();
				$invbalSet = $ss->MboSetRemote("invbalance")
				->setWhere("invbalance.itemnum='$itemnum' and invbalance.warehouse='$warehouse' $condval $fromrack")
				->setRelationship("item", "invbalance.itemnum = item.itemnum")
				->MboRemote("invbalance.curbal")
				->MboRemote("item.issueunit")
				->getMboSet();
				$invbal = $invbalSet->getMbo();
				$curbal = $invbal->getString("curbal");
				$issueunit = $invbal->getString("issueunit");
				
				if($sellqty > $curbal){
					$content = "<li>No. $refundlinenum - Item: $itemnum, Gudang: $warehouse, Rak:$racknum, Condition Type: $conditiontype jumlah stok tidak cukup. Stok di gudang hanya $curbal $issueunit </li>";
					array_push($msgr, $content);
				}
				
				
				if(empty($torefundlinenum)){
					$content = "<li>Mohon di-isi kolom <b>Refund untuk Baris Ke </b> pada No. $refundlinenum di bagian Penggantian Barang/Jasa </li>";
					array_push($msgr, $content);
				}
				
				
				if($varunitprice!=0){
					$ssq = new Set();
					$refundlineSet = $ssq->MboSetRemote("refundline")->setWhere("refundnum='$refundnum' and refundlinenum='$torefundlinenum'")->MboRemote("invoicenum")->getMboSet();
					$refundline = $refundlineSet->getMbo();
					$invoicenum = $refundline->getString("invoicenum");
					
					$sq = new Set();
					$invoicetransSet = $sq->MboSetRemote("invoicetrans")->setWhere("invoicenum='$invoicenum' and linetype like '%tax%'")->getMboSet();
					$cntss = $invoicetransSet->counts();
					
					if( $cntss > 0 ){
						$sic = new Set();
						$invoicechargeSet = $sic->MboSetRemote("invoicecharge")->setWhere("invoicenum='$invoicenum'")->MboRemote("taxreceiptnum")->getMboSet();
						$invoicecharge = $invoicechargeSet->getMbo();
						$taxreceiptnum = $invoicecharge->getString("taxreceiptnum");
						
						$ssa = new Set();
						$invoicetransSet = $ssa->MboSetRemote("invoicetrans")->setWhere("invoicenum='$invoicenum'");
						$content = "<li>No. $refundlinenum tidak bisa refund karena invoice ( <b>$invoicenum </b>)ini adalah PKP ( No. E-Faktur: $taxreceiptnum ) 
						<br>dan memiliki selisih harga sebesar <b>$varunitprice</b> </li>";
						array_push($msgr, $content);
					}
					
				}
				
				
			}
			
		}
		if (count($msgr) > 0) {
			$msgc = implode(" ", $msgr);
			$cont = "<ul>" . $msgc . "</ul>";
			$cn->msg = false;
			$cn->content = $cont;
		}
		else {
			$cn->msg = true;
			$cn->content = null;
		}
		Set::throws($cn);
	}
	public static function VarianceRefund(){
		
		$set = new Set();
		$refundnum = $_POST['variancerefund'];
		$tolinenum = $_POST['tolinenum'];
		
		
		if(empty($tolinenum)) {
			Set::throws(["msg"=>"emptytorefundlinenum"]);
		}else{
			$refundlinenum = $_POST["refundlinenum"];
			$rlSet = $set->MboSetRemote("refundline")->setWhere("refundnum='$refundnum' and refundlinenum='$tolinenum'")
			->MboRemote("unitcost")->MboRemote("linecost")->getMboSet();
			$rl = $rlSet->getMbo();
			$unitcost = $rl->getString("unitcost");
			$linecost = $rl->getString("linecost");
			
			$rl2Set = $set->MboSetRemote("refundline2")->setWhere("refundnum='$refundnum' and refundlinenum='$refundlinenum'")
			->MboRemote("unitprice")->MboRemote("lineprice")->getMboSet();
			$rl2 = $rl2Set->getMbo();
			if(isset($_POST["unitprice"])) {
				$unitprice = $_POST["unitprice"];
			}else{
				$unitprice = $rl->getString("unitprice");
			}
			
			
			if(isset($_POST["lineprice"])) {
				$lineprice = $_POST["lineprice"];
			}else{
				$lineprice = $rl->getString("lineprice");
			}
			if(isset($_POST["unitpricediscount"])) {
				if($_POST["unitpricediscount"] <= $unitprice) $unitprice = $_POST["unitpricediscount"];
			} 
			
			$varunitprice = $unitprice-$unitcost;
			$varlineprice = $lineprice-$linecost;
			Set::throws(["msg"=>"ok", "varunitprice"=> "$varunitprice", "varlineprice"=> "$varlineprice"]);
		}
		
	}
	
	public static function InsertOrUpdateINVENTORY($ob){
		$itemnum = $ob->itemnum;
		$towarehouse = $ob->towarehouse;
		$toracknum = $ob->toracknum;
		$enterby = $ob->enterby;
		$enterdate = $ob->enterdate;
		$orgcode = $ob->orgcode;
		$sitecode = $ob->sitecode;
		
		$set = new Set();
		$wcinv = "itemnum='$itemnum' and warehouse='$towarehouse'";
		$invSet = $set->MboSetRemote("inventory")->setWhere($wcinv)->MboRemote("inventoryid")->getMboSet();
	
		$invcnt = $invSet->counts();
		if($invcnt > 0){
		}else{
			$set = new Set();
			$set->MboSetRemote("inventory")->setValue("itemnum", $itemnum)->setValue("warehouse", $towarehouse)->setValue("racknum", $toracknum)
			->setValue("reorderqty", 1)->setValue("leadtime", 0)->setValue("eoqqty", 1)->setValue("safetystock", 0)->setValue("reorder", "Y")->setValue("changeby", $enterby)
			->setValue("changedate", $enterdate)->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)->add();
		}
	}
	
	public static function InsertOrUpdateINVCOST($ob){
		$itemnum = $ob->itemnum;
		$towarehouse = $ob->towarehouse;
		$conditiontype = $ob->conditiontype;
		$conditionrate = $ob->conditionrate;
		$unitcost = $ob->unitcost;
		$orgcode = $ob->orgcode;
		$sitecode = $ob->sitecode;
		
		$glaccount = Xbo::getGLAccount($itemnum);
		$invcostadjacc = Xbo::getInvCostAdjAcct($towarehouse, $orgcode, $sitecode);
		$shrinkageacc = Xbo::getShrinkageAcct($towarehouse, $orgcode, $sitecode);
		
		$set = new Set();
		$invCostSet = $set->MboSetRemote("invcost")->setWhere("itemnum = '$itemnum' and warehouse = '$towarehouse' and conditiontype='$conditiontype'")->getMboSet();
		$cnt = $invCostSet->counts();
		if($cnt > 0){
		}else{
			$setic = new Set();
			$setic->MboSetRemote("invcost")->setValue("itemnum", $itemnum)->setValue("warehouse", $towarehouse)
			->setValue("conditiontype", $conditiontype)->setValue("conditionrate", $conditionrate)
			->setValue("stdcost", $unitcost)->setValue("avgcost", $unitcost)->setValue("oldcost", $unitcost)
			->setValue("lastcost", $unitcost)->setValue("glaccount", $glaccount)->setValue("shrinkageacc", $shrinkageacc)
			->setValue("invcostadjacc", $invcostadjacc)->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)->add();
		}
	}
	
	public static function InsertOrUpdateINVBALANCE($ob){
		$itemnum = $ob->itemnum;
		$towarehouse = $ob->towarehouse;
		$toracknum = $ob->toracknum;
		$conditiontype = $ob->conditiontype;
		$enterby = $ob->enterby;
		$enterdate = $ob->enterdate;
		$orgcode = $ob->orgcode;
		$sitecode = $ob->sitecode;
		$retqty = $ob->retqty;
		
		if (empty($conditiontype)) $condval = " and (conditiontype is null or conditiontype='')";
		else $condval = " and conditiontype='$conditiontype'";
			
		if(empty($toracknum)) $rackval = " and (racknum is null or racknum='')"; 
		else $rackval = " and racknum='$toracknum'";
					
		$set = new Set();
		$wcinv = "itemnum='$itemnum' and warehouse='$towarehouse'";
		$wcinvbal = $wcinv." $condval $rackval";
		$invbalset = $set->MboSetRemote("invbalance")->setWhere($wcinvbal)->MboRemote("curbal")->getMboSet();
		$invbalcnt = $invbalset->counts();
		if($invbalcnt >0){
			$invbal = $invbalset->getMbo();
			$oldcurbal = $invbal->getString("curbal");
			if($oldcurbal == null) $oldcurbal = 0;
			$newcurbal = $oldcurbal+$retqty;
			
			$set3 = new Set();
			$set3->MboSetRemote("invbalance")->setWhere($wcinvbal)->setValue("curbal", $newcurbal)->setValue("oldcurbal", $oldcurbal)->change();
		}else{
			$oldcurbal = 0;
			$newcurbal = $oldcurbal+$retqty;
			$set3 = new Set();
			$set->MboSetRemote("invbalance")->setValue("itemnum", $itemnum)->setValue("conditiontype", $conditiontype)->setValue("warehouse", $towarehouse)
			->setValue("racknum", $toracknum)->setValue("curbal", $newcurbal)->setValue("oldcurbal", $oldcurbal)->setValue("enterby", $enterby)
			->setValue("enterdate", $enterdate)->setValue("changeby", $enterby)->setValue("changedate", $enterdate)->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)->add();
		}
	}
	
	public static function InsertOrUpdateINVFIFOLIFOCOST($ob){
		$itemnum = $ob->itemnum;
		$towarehouse = $ob->towarehouse;
		$toracknum = $ob->toracknum;
		$conditiontype = $ob->conditiontype;
		$enterby = $ob->enterby;
		$enterdate = $ob->enterdate;
		$orgcode = $ob->orgcode;
		$sitecode = $ob->sitecode;
		$quantity = $ob->quantity;
		$costdate = $ob->costdate;
		$financialperiod = $ob->financialperiod;
		$conditionrate = $ob->conditionrate;
		$unitcost = $ob->unitcost;
		$batchnum = $ob->batchnum;
		$expdate = $ob->expdate;
		$retqty = $ob->retqty;
		if(isset($ob->fromwarehouse)){
			$fromwarehouse = $ob->fromwarehouse;
		}else{
			$fromwarehouse = $ob->warehouse;
		}
		
		if(isset($ob->fromracknum)){
			$fromracknum = $ob->fromracknum;
		}else{
			$fromracknum = $ob->racknum;
		}
		
		if(isset($ob->issuedate)){
			$costdate = $ob->issuedate;
		}else{
			$costdate = $ob->costdate;
		}
		
		
		if (empty($conditiontype)) $condval = " and (conditiontype is null or conditiontype='')";
		else $condval = " and conditiontype='$conditiontype'";
			
		if(empty($toracknum)) $rackval = " and (racknum is null or racknum='')"; 
		else $rackval = " and racknum='$toracknum'";
		
		if(empty($fromracknum)) $fromrackval = " and (racknum is null or racknum='')"; 
		else $fromrackval = " and racknum='$fromracknum'";
		
		
		$set = new Set();
		$wccostdate = Super::whereDate("costdate", $costdate);
		$wcinv = "itemnum='$itemnum' and warehouse='$towarehouse'";
		$wcinv2 = "itemnum='$itemnum' and warehouse='$fromwarehouse'";
		$wcfifolifo = "$wcinv $condval $wccostdate $rackval";
		$wcfifolifopovendor = "$wcinv2 $condval $wccostdate ";
		
		
		if(empty($expdate)){
			$wcfifolifo = "$wcinv $condval $wccostdate $rackval ";
			$wcfifolifopovendor = "$wcinv2 $condval $wccostdate $fromrackval";
		}else{
			$wcexpdate = Super::whereDate("expdate", $expdate);
			$wcfifolifo = "$wcinv $condval $wccostdate $rackval $wcexpdate";
			$wcfifolifopovendor = "$wcinv2 $condval $wccostdate $fromrackval";
		}
		$iflSet = $set->MboSetRemote("invfifolifocost")->setWhere($wcfifolifo)->MboRemote("quantity")->getMboSet();
		
		$iflcnt = $iflSet->counts();
		if($iflcnt > 0){
			$ifl = $iflSet->getMbo();
			$oldflqty = $ifl->getString("quantity");
			$newflqty = $oldflqty+$retqty;
			$set4 = new Set();
			$set4->MboSetRemote("invfifolifocost")->setWhere($wcfifolifo)->setValue("quantity", $newflqty)->change();
		}else{
			$pv = new Set();
			$povendorSet = $pv->MboSetRemote("invfifolifocost")->setWhere($wcfifolifopovendor)->MboRemote("vendorcode")->MboRemote("ponum")->MboRemote("wonum")->getMboSet();
			$povendor = $povendorSet->getMbo();
			$vendorcode = $povendorSet->getString("vendorcode");
			$ponum = $povendorSet->getString("ponum");
			$wonum = $povendorSet->getString("wonum");
			$set4 = new Set();
			if($quantity < 0 ) $quantity = $quantity*-1;
			$quantity = $retqty;
			
			if(empty($costdate)){
				$set2 = new Set();
				$invbalanceSet = $set2->MboSetRemote("invbalance")->setWhere("itemnum='$itemnum' and warehouse = '$towarehouse' $condval $rackval")->MboRemote("enterdate")->getMboSet();
				$invbalance = $invbalanceSet->getMbo();
				$costdate = $invbalance->getString("enterdate");
			}
			$set4->MboSetRemote("invfifolifocost")->setValue("itemnum", $itemnum)->setValue("conditiontype", $conditiontype)->setValue("costdate", $costdate)->setValue("expdate", $expdate)
			->setValue("batchnum", $batchnum)->setValue("financialperiod", $financialperiod)->setValue("unitcost", $unitcost)->setValue("quantity", $quantity)
			->setValue("conditionrate", $conditionrate)->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)->setValue("warehouse", $towarehouse)->setValue("racknum", $toracknum)
			->setValue("ponum", $ponum)->setValue("vendorcode", $vendorcode)->setValue("wonum", $wonum)->add();
			
		}
	}
	public static function UpdateAllInventoryObject($ob){
		ActionSet::InsertOrUpdateINVENTORY($ob);
		ActionSet::InsertOrUpdateINVBALANCE($ob);
		ActionSet::InsertOrUpdateINVCOST($ob);
		ActionSet::InsertOrUpdateINVFIFOLIFOCOST($ob);
		/*
		requires this information:
		
		$ob->itemnum 
		$ob->towarehouse;
		$ob->conditiontype;
		$ob->conditionrate;
		$ob->unitcost;
		$ob->orgcode;
		$ob->sitecode;
		$ob->toracknum;
		$ob->enterby;
		$ob->enterdate;
		$ob->retqty;
		$ob->quantity;
		$ob->costdate;
		$ob->financialperiod;
		$ob->batchnum;
		$ob->expdate;
		
		*/
	}
	public static function RefundInvoiceTrans($value, $type){
		
		$value->billinglinenum = $value->keylinenum;
		if(!isset($value->taxcost)) $value->taxcost = 0;
		if(!isset($value->taxprice)) $value->taxprice = 0;
		
		if($type == "return"){
			$gldebitacct = $value->glcreditacct;
			$glcreditacct = $value->gldebitacct;
		}else{
			$gldebitacct = $value->glcreditacct;
			$glcreditacct = $value->gldebitacct;
		}
		
		
		if(!empty($value->sonum)) {
			$value->billinglinenum = null;
			$value->billingnum = null;
		}
		$ss = new Set();
		$invtransSet = $ss->MboSetRemote("invoicetrans")->setWhere("invoicetransid='{$value->transid}'")
		->MboRemote("pricecode")->MboRemote("invoicedate")->MboRemote("consignment")
		->getMboSet();
		
		$invtrans = $invtransSet->getMbo();
		$value->pricecode = $invtrans->getString("pricecode");
		$value->invoicedate = $invtrans->getString("invoicedate");
		$value->consignment = $invtrans->getString("consignment");
		
		$set =new Set();
		$set->MboSetRemote("invoicetrans")
		->setValue("invoicenum", $value->invoicenum)
		->setValue("ponum", $value->ponum)
		->setValue("sonum", $value->sonum)
		->setValue("wonum", $value->wonum)
		->setValue("costtype", $value->costtype)
		->setValue("costdate", $value->costdate)
		->setValue("customercode", $value->customercode)
		->setValue("linetype", $value->linetype)
		->setValue("invoicelinenum", $value->invoicelinenum)
		->setValue("keylinenum", $value->keylinenum)
		->setValue("itemnum", $value->itemnum)
		->setValue("description", $value->description)
		->setValue("exchangerate", $value->exchangerate)
		->setValue("quantity", $value->quantity)
		->setValue("unitprice", $value->unitprice)
		->setValue("lineprice", $value->lineprice)
		->setValue("taxprice", $value->taxprice)
		->setValue("unitcost", $value->unitcost)
		->setValue("linecost", $value->linecost)
		->setValue("taxcost", $value->taxcost)
		->setValue("unitprofit", $value->unitprofit)
		->setValue("lineprofit", $value->lineprofit)
		->setValue("unitpricediscount", $value->unitpricediscount)
		->setValue("linepricediscount", $value->linepricediscount)
		->setValue("unitprofitdiscount", $value->unitprofitdiscount)
		->setValue("lineprofitdiscount", $value->lineprofitdiscount)
		->setValue("tax", $value->taxcode)
		->setValue("invoicedate", $value->invoicedate)
		->setValue("consignment", $value->consignment)
		->setValue("transtype", $value->transtype)
		->setValue("paymenttype", $value->paymenttype)
		->setValue("conditiontype", $value->conditiontype)
		->setValue("warehouse", $value->warehouse)
		->setValue("pricecode", $value->pricecode)
		->setValue("gldebitacct", $gldebitacct)
		->setValue("glcreditacct", $glcreditacct)
		->setValue("financialperiod", $value->financialperiod)
		->setValue("enterby", $value->enterby)
		->setValue("salesman", $value->salesman)
		->setValue("billingnum", $value->billingnum)
		->setValue("billinglinenum", $value->billinglinenum)
		->setValue("transnum", $value->transnum)
		->setValue("enterdate", $value->enterdate)
		->setValue("changeby", $value->changeby)
		->setValue("changedate", $value->changedate)
		->setValue("refundnum", $value->refundnum)
		->setValue("refundlinenum", $value->refundlinenum)
		->setValue("refundlinenum2", $value->refundlinenum2)
		->setValue("orgcode", $value->orgcode)
		->setValue("sitecode", $value->sitecode)
		->setValue("unitcommission", $value->unitcommission)
		->setValue("linecommission", $value->linecommission)
		->add();
	}
	
	public static function ReturnTransaction($value=null)

	{
		$set = new Set();
		
		if($value->transtable == "matsaletrans"){
			if(empty($value->ponum)){
				$set = new Set();
				if(empty($value->sonum)) {
					$wc = "transnum = '{$value->transnum}' and transtype = 'SALE'";
				}else{
					$wc = "sonum='{$value->sonum}' and solinenum='{$value->keylinenum}' and transtype = 'SALE'";
				}
				$matsaleSet = $set->MboSetRemote("matsaletrans")->setWhere($wc)
				->MboRemote("taxcode")->MboRemote("expdate")->MboRemote("batchnum")->MboRemote("unitprice")->MboRemote("unitprofit")
				->MboRemote("primarydiscount")->MboRemote("secondarydiscount")->MboRemote("discountprice")->MboRemote("fromrack")
				->MboRemote("customercode")->MboRemote("unitcost")->MboRemote("salesman")->MboRemote("pricecode")
				->MboRemote("unitcommission")
				->getMboSet();				
				$matsale = $matsaleSet->getMbo();
				$value->taxcode = $matsale->getString("taxcode");
				$value->expdate = $matsale->getString("expdate");
				$value->batchnum = $matsale->getString("batchnum");
				$value->unitprofit = $matsale->getString("unitprofit");
				$value->unitprice = $matsale->getString("unitprice");
				$value->primarydiscount = $matsale->getString("primarydiscount");
				$value->secondarydiscount = $matsale->getString("secondarydiscount");
				$value->matsalediscountprice = $matsale->getString("discountprice");
				$value->discountprice = $value->discountprice + $value->matsalediscountprice;
				$value->lineprice = $value->unitprice*$value->quantity;
				$value->lineprofit = $value->unitprofit*$value->quantity;
				$taxvalue = XboSet::getTaxValue($value->taxcode);
				$value->linepricediscount = $value->unitpricediscount*$value->quantity;
				$value->taxprice = $value->linepricediscount*$taxvalue/100;
				$value->lineprofitdiscount = $value->unitprofitdiscount*$value->quantity;
				$value->fromrack  =  $matsale->getString("fromrack");
				$value->customercode  =  $matsale->getString("customercode");
				$value->unitcost  =  $matsale->getString("unitcost");
				$value->linecost = $value->unitcost*$value->quantity;
				$value->salesman = $matsale->getString("salesman");
				$value->pricecode = $matsale->getString("pricecode");
				$value->unitcommission = $matsale->getString("unitcommission");
				$value->linecommission = $value->unitcommission*$value->quantity;
				
				
				//get origin info of returned Item
				$set = new Set();
				$matrecSet = $set->MboSetRemote("matrectrans")->setRelationship("po", "matrectrans.ponum = po.ponum")
				->setWhere("batchnum='".$value->batchnum."'")
				->MboRemote("matrectrans.ponum")->MboRemote("matrectrans.polinenum")->MboRemote("po.vendor")
				->getMboSet("distinct");
				
				$matrec = $matrecSet->getMbo();
				$value->ponum = $matrec->getString("ponum");
				$value->polinenum = $matrec->getString("polinenum");
				$value->vendor = $matrec->getString("vendor");
				
				//end get origin of Returned Item
			}
			
			
			$value->lineprice = $value->lineprice*-1;
			$value->linepricediscount = $value->linepricediscount*-1;
			$value->taxprice = $value->taxprice*-1;
			$value->lineprofit = $value->lineprofit*-1;
			$value->lineprofitdiscount = $value->lineprofitdiscount*-1;
			
			
			$value->toracknum = $value->torack;
			$value->retqty = $value->quantity;
			$value->enterdate = $value->transdate;
			$value->changedate = $value->transdate;
			$value->changeby = $value->enterby;
			
			$value->cashcreditacct = $value->gldebitacct;
			$value->whgldebitacct  =  Xbo::getwhgl($value->warehouse, "noprint");
			
			
			$set->MboSetRemote("matsaletrans")
			->setValue("financialperiod", $value->financialperiod)
			->setValue("itemnum", $value->itemnum)
			->setValue("conditiontype", $value->conditiontype)
			->setValue("description", $value->description)
			->setValue("invoicenum", $value->invoicenum)
			->setValue("sonum", $value->sonum)
			->setValue("solinenum", $value->keylinenum)
			->setValue("quantity", $value->quantity)
			->setValue("unitcost", $value->unitcost)
			->setValue("linecost", $value->linecost)
			->setValue("unitprofit", $value->unitprofit)
			->setValue("lineprofit", $value->lineprofit)
			->setValue("warehouse", $value->warehouse)
			->setValue("costtype", $value->costtype)
			->setValue("costdate", $value->costdate)
			->setValue("gldebitacct", $value->whgldebitacct  )
			->setValue("glcreditacct", $value->cashcreditacct )
			->setValue("transnum", $value->transnum)
			->setValue("linetype", $value->linetype)
			->setValue("paymenttype", $value->paymenttype)
			->setValue("invoicelinenum", $value->invoicelinenum)
			->setValue("transtype", $value->transtype)
			->setValue("towarehouse", $value->towarehouse)
			->setValue("torack", $value->torack)
			->setValue("actualdate", $value->actualdate)
			->setValue("transdate", $value->transdate)
			->setValue("primarydiscount", $value->primarydiscount)
			->setValue("secondarydiscount", $value->secondarydiscount)
			->setValue("discountprice", $value->discountprice)
			->setValue("unitprice", $value->unitprice)
			->setValue("lineprice", $value->lineprice)
			->setValue("unitpricediscount", $value->unitpricediscount)
			->setValue("linepricediscount", $value->linepricediscount)
			->setValue("taxcode", $value->taxcode)
			->setValue("taxprice", $value->taxprice)
			->setValue("issuetype", $value->costtype)
			->setValue("expdate", $value->expdate)
			->setValue("batchnum", $value->batchnum)
			->setValue("unitprofitdiscount", $value->unitprofitdiscount)
			->setValue("lineprofitdiscount", $value->lineprofitdiscount)
			->setValue("fromrack", $value->fromrack)
			->setValue("customercode", $value->customercode)
			->setValue("isrefund", "Y")
			->setValue("invoiced", "Y")
			->setValue("orgcode", $value->orgcode)
			->setValue("sitecode", $value->sitecode)
			->setValue("enterby", $value->enterby)
			->setValue("enterdate", $value->transdate)
			->setValue("salesman", $value->salesman)
			->setValue("pricecode", $value->pricecode)
			->setValue("refundnum", $value->refundnum)
			->setValue("refundlinenum", $value->refundlinenum)
			->setValue("refundlinenum2", $value->refundlinenum2)
			->setValue("unitcommission", $value->unitcommission)
			->setValue("linecommission", $value->linecommission)
			->add();
			
			$set = new Set();	
			$value->conditionrate = $set->MboSetRemote("conditiontype")->setWhere("conditiontype='{$value->conditiontype}'")->MboRemote("conditionrate")->getMboSet()->getMbo()->getString("conditionrate");
			ActionSet::UpdateAllInventoryObject($value);
			
			ActionSet::RefundInvoiceTrans($value, "return");
			
		}else{
			if($value->transtable == "matrectrans"){
				
			}else{
				if($value->transtable == "servrectrans"){
					
				}else{
					if($value->transtable == "servsaletrans"){
						//RETURN DISINI
					}else{
						if($value->transtable == "matusetrans"){
							$set =  new Set();
							$billSet = $set->MboSetRemote("billingline")
							->setWhere("billingline.billingnum='{$value->billingnum}' and billingline.billinglinenum='{$value->keylinenum}'")
							->setRelationship("billing", "billing.billingnum = billingline.billingnum")
							->MboRemote("billingline.unitcost")->MboRemote("billingline.unitprice")->MboRemote("billingline.unitpricediscount")
							->MboRemote("billingline.unitprofit")->MboRemote("billingline.unitprofitdiscount")
							->MboRemote("billingline.wonum")->MboRemote("billing.customercode")->MboRemote("billing.billingnum")
							->MboRemote("billingline.itemnum")->MboRemote("billingline.costdate")
							->MboRemote("billingline.conditiontype")->MboRemote("billingline.fromwarehouse")->MboRemote("billingline.fromracknum")
							->getMboSet();
							
							$bill = $billSet->getMbo();
							$wonum = $bill->getString("wonum");
							$billingnum = $bill->getString("billingnum");
							$customercode = $bill->getString("customercode");
							$unitprofitdiscount = $bill->getString("unitprofitdiscount"); 
							$unitprofit = $bill->getString("unitprofit"); 
							$unitpricediscount = $bill->getString("unitpricediscount"); 
							$unitprice = $bill->getString("unitprice"); 
							$unitcost = $bill->getString("unitcost"); 
							$itemnum = $bill->getString("itemnum"); 
							$costdate = $bill->getString("costdate"); 
							$wccostdate = Super::whereDate("costdate", $costdate);
							$conditiontype = $bill->getString("conditiontype");
							$fromwarehouse = $bill->getString("fromwarehouse");
							$fromracknum = $bill->getString("fromracknum");
						
							if (empty($conditiontype)) {
								$condval = " and (conditiontype='' or conditiontype is null)";
							}
							else {
								$condval = " and conditiontype='{$value->conditiontype}'";
							}
							
							if(empty($fromracknum)) $rackval = " and (racknum is null or racknum='')"; 
							else $rackval = " and racknum='{$fromracknum}'";
							
							
							$st = new Set();
							$matuseSet = $st->MboSetRemote("matusetrans")->setWhere("wonum='$wonum' and itemnum='$itemnum' and warehouse='$fromwarehouse' $condval $rackval $wccostdate ")->getMboSet();
							$matuse = $matuseSet->getMbo();
							$value->expdate = $matuse->getString("expireddate");
							$value->batchnum = $matuse->getString("batchnum");
							$gldebitacct = $matuse->getString("gldebitacct");
							$glcreditacct = $matuse->getString("glcreditacct");
							$matuseqty = $matuse->getString("quantity");
							$discountprice = $matuse->getString("discountprice");
							$unitprofit = $matuse->getString("unitprofit");
							$mrnum = $matuse->getString("mrnum");
							$lineprofit = $value->quantity*$unitprofit;
							$matusediscountprice = $discountprice/$matuseqty;
							
							if($matusediscountprice<0) $matusediscountprice = $matusediscountprice*-1;
							
							$primarydiscount = $matuse->getString("primarydiscount");
							$secondarydiscount = $matuse->getString("secondarydiscount");
							$issuetype = $matuse->getString("issuetype");
							$orgcode = $matuse->getString("orgcode");
							$sitecode = $matuse->getString("sitecode");
							$requestedby = $matuse->getString("requestedby");
							$assetnum = $matuse->getString("assetnum");
							$location = $matuse->getString("location");
							$unitcost = $matuse->getString("unitcost");
							if($value->quantity<0) $value->quantity = $value->quantity*-1;
							
							$si = new Set();
							$invbalSet = $si->MboSetRemote("invbalance")->setWhere("itemnum='{$value->itemnum}' and warehouse='{$fromwarehouse}' $condval $rackval")->MboRemote("curbal")->getMboSet();
							$invbal = $invbalSet->getMbo();
							$oldcurbal = $invbal->getString("curbal");
							$newcurbal = $oldcurbal+$value->quantity;
							$linecost = $unitcost*$value->quantity;
							if($linecost<0) $linecost = $linecost*-1;
							$unitprice = $value->unitcost;
							$lineprice = $unitprice*$value->quantity;
							if($lineprice >0) $lineprice = $lineprice*-1;
							
							
							$sl = new Set();
							$invoicelineSet=  $sl->MboSetRemote("invoiceline")
							->setWhere("invoicenum='{$value->invoicenum}' and invoicelinenum='{$value->invoicelinenum}'")->getMboSet();
							$invoice = $invoicelineSet->getMbo();
							$invoiceqty = $invoice->getString("quantity");
							$salesman = $invoice->getString("salesman");
							$taxcode = $invoice->getString("tax");
							$unitcommission  = $invoice->getString("unitcommission");
							$linecommission = ($unitcommission*$value->quantity	)*-1;						
							$unitdiscountprice = $value->discountprice/$invoiceqty;
							$newdiscountprice = $matusediscountprice+$unitdiscountprice;
							$linepricediscount = $value->quantity*$unitpricediscount;
							if($linepricediscount<0) $linepricediscount = $linepricediscount*-1;
							
							$unitpricediscount = $value->unitcost;
							$linepricediscount = $unitpricediscount*$value->quantity;
							if($linepricediscount>0) $linepricediscount = $linepricediscount*-1;
							
							$value->lineprofitdiscount = ($value->unitprofitdiscount*$value->quantity)*-1;
							$lineprofit = $lineprofit*-1;
							
							$transtype = 'RETURN';
							$isconsignment = 'Y';
							$value->changedate = $value->transdate;
							$value->changeby = $value->enterby;
							
							$ss = new Set();
							$ss->MboSetRemote("matusetrans")
							->setValue("wonum", $wonum)
							->setValue("requestedby", $requestedby)
							->setValue("description", $value->description)
							->setValue("assetnum", $assetnum)
							->setValue("location", $location)
							->setValue("itemnum", $itemnum)
							->setValue("expireddate", $value->expdate)
							->setValue("batchnum", $value->batchnum)
							->setValue("conditiontype", $conditiontype)
							->setValue("warehouse", $fromwarehouse)
							->setValue("racknum", $fromracknum)
							->setValue("quantity", $value->quantity)
							->setValue("oldcurbal", $oldcurbal)
							->setValue("newcurbal", $newcurbal)
							->setValue("unitcost", $unitcost)
							->setValue("linecost", $linecost)
							->setValue("unitprice", $unitprice)
							->setValue("lineprice", $lineprice)
							->setValue("primarydiscount", $primarydiscount)
							->setValue("secondarydiscount", $secondarydiscount)
							->setValue("discountprice", $newdiscountprice)
							->setValue("unitpricediscount", $unitpricediscount)
							->setValue("linepricediscount", $linepricediscount)
							->setValue("unitprofit", $unitprofit)
							->setValue("lineprofit", $lineprofit)
							->setValue("unitprofitdiscount", $value->unitprofitdiscount)
							->setValue("lineprofitdiscount", $value->lineprofitdiscount)
							->setValue("issuetype", $issuetype)
							->setValue("costdate", $costdate)
							->setValue("transtype", $transtype)
							->setValue("gldebitacct", $glcreditacct)
							->setValue("glcreditacct", $gldebitacct)
							->setValue("mrnum", $mrnum)
							->setValue("invoicenum", $value->invoicenum)
							->setValue("isconsignment", $isconsignment)
							->setValue("enterby", $value->enterby)
							->setValue("enterdate", $value->transdate)
							->setValue("changeby", $value->changeby)
							->setValue("changedate", $value->changedate)
							->setValue("orgcode", $value->orgcode)
							->setValue("sitecode", $value->sitecode)
							->setValue("actualdate", $value->actualdate)
							->setValue("refundnum", $value->refundnum)
							->setValue("refundlinenum", $value->refundlinenum)
							->setValue("refundlinenum2", $value->refundlinenum2)
							->setValue("unitcommission", $unitcommission)
							->setValue("linecommission", $linecommission)
							->add();
							
							$value->wonum = $wonum;
							$value->unitcost = $unitcost;
							$value->linecost = $linecost;
							$value->unitprice = $unitprice;
							$value->lineprice = $lineprice;
							$value->unitpricediscount = $unitpricediscount;
							$value->linepricediscount = $linepricediscount;
							$value->unitprofit = $unitprofit;
							$value->lineprofit = $lineprofit;
							$value->consignment = 'Y';
							$value->transtype = "sales-return";
							$value->customercode = $customercode;
							$value->enterdate = $value->transdate;
							$value->salesman = $salesman;
							$value->taxcode = $taxcode;
							$taxvalue = XboSet::getTaxValue($value->taxcode);
							$value->taxprice = $value->linepricediscount*$taxvalue/100;
							$value->toracknum = $value->torack;
							$value->retqty = $value->quantity;
							$value->enterdate = $value->transdate;
							$value->changedate = $value->transdate;
							$value->changeby = $value->enterby;
							$value->unitcommission = $unitcommission;
							$value->linecommission = $linecommission;
							$value->conditionrate = $set->MboSetRemote("conditiontype")->setWhere("conditiontype='{$value->conditiontype}'")->MboRemote("conditionrate")->getMboSet()->getMbo()->getString("conditionrate");
							
							$value->glcreditacct =  Xbo::getCustomerRBNIAcct($value->customercode, $value->orgcode, $value->sitecode);
							ActionSet::RefundInvoiceTrans($value, "return");
							ActionSet::UpdateAllInventoryObject($value);
								
						}
					}
				}
			}
		}
	}
	
	
	public static function UpdateRefund(){
		$refundnum = $_POST["updaterefund"];
		$enterby = Xbo::getLoginUser();
		$transdate = Xbo::setEnterDate();
		$financialperiod = Xbo::getFinancialPeriod();
		$set = new Set();
		$refunlineSet = $set->MboSetRemote("refundline")->setWhere("refundline.refundnum='$refundnum'")
		->setRelationship("refund", "refundline.refundnum = refund.refundnum")
		->MboRemote("refundline.itemnum")->MboRemote("refundline.conditiontype")->MboRemote("refundline.description")
		->MboRemote("refundline.invoicenum")->MboRemote("refundline.ponum")->MboRemote("refundline.sonum")->MboRemote("refundline.wonum")
		->MboRemote("refundline.billingnum")->MboRemote("refundline.transnum")->MboRemote("refundline.quantity")
		->MboRemote("refundline.issueunit")->MboRemote("refundline.unitcost")->MboRemote("refundline.linecost")
		->MboRemote("refundline.unitprofit")->MboRemote("refundline.lineprofit")->MboRemote("refundline.warehouse")
		->MboRemote("refundline.costtype")->MboRemote("refundline.costdate")->MboRemote("refundline.gldebitacct")
		->MboRemote("refundline.glcreditacct")->MboRemote("refundline.transid")->MboRemote("refundline.linetype")
		->MboRemote("refundline.towarehouse")->MboRemote("refundline.torack")
		->MboRemote("refundline.paymenttype")->MboRemote("refundline.invoicelinenum")
		->MboRemote("refundline.keylinenum")->MboRemote("refund.actualdate")
		->MboRemote("refundline.discount")->MboRemote("refundline.discountprice")
		->MboRemote("refund.orgcode")->MboRemote("refund.sitecode")
		->MboRemote("refundline.exchangerate")->MboRemote("refundline.refundlinenum")
		->getMboSet();
		
		$cnt  = $refunlineSet->counts(); 
		for($i=0; $i<$cnt; $i++){
			$rob = new stdClass();
			$rf = $refunlineSet->getMbo();
			$rob->financialperiod = $financialperiod;
			$rob->itemnum = $rf->getString("itemnum");
			$rob->conditiontype = $rf->getString("conditiontype");
			$rob->description = $rf->getString("description");
			$rob->invoicenum = $rf->getString("invoicenum");
			$rob->ponum = $rf->getString("ponum");
			$rob->sonum = $rf->getString("sonum");
			$rob->quantity = $rf->getString("quantity");
			$rob->issueunit = $rf->getString("issueunit");
			$rob->unitcost = $rf->getString("unitcost"); 
			$rob->linecost = $rf->getString("linecost");
			$rob->unitpricediscount = $rob->unitcost;
			$rob->linepricediscount = $rob->linecost;
			$rob->unitprofitdiscount = $rf->getString("unitprofit");
			$rob->lineprofitdiscount = $rf->getString("lineprofit");
			$rob->warehouse = $rf->getString("warehouse");
			$rob->costtype = $rf->getString("costtype");
			$rob->costdate = $rf->getString("costdate");
			$rob->gldebitacct = $rf->getString("gldebitacct");
			$rob->glcreditacct = $rf->getString("glcreditacct");
			$rob->transid = $rf->getString("transid");
			$rob->billingnum = $rf->getString("billingnum");
			$rob->transnum = $rf->getString("transnum");
			$rob->wonum = $rf->getString("wonum");
			$rob->linetype = $rf->getString("linetype");
			$rob->paymenttype = $rf->getString("paymenttype");
			$rob->invoicelinenum = $rf->getString("invoicelinenum");
			$rob->transtype = "RETURN";
			$rob->keylinenum = $rf->getString("keylinenum");
			$rob->towarehouse = $rf->getString("towarehouse");
			$rob->torack = $rf->getString("torack");
			$rob->actualdate = $rf->getString("actualdate");
			$rob->discountprice = $rf->getString("discountprice");
			$rob->discount = $rf->getString("discount");
			$rob->orgcode = $rf->getString("orgcode");
			$rob->sitecode = $rf->getString("sitecode");
			$rob->exchangerate = $rf->getString("exchangerate");
			$rob->transdate = $transdate;
			$rob->enterby = $enterby;
			$rob->refundnum = $refundnum;
			$rob->refundlinenum =  $rf->getString("refundlinenum");
			$rob->refundlinenum2 = null;
			if(!empty($rob->ponum)){
				if($rob->linetype == "services"){
					$rob->transtable = "servrectrans";
				}else{
					if($rob->linetype == "item"){
						$rob->transtable = "matrectrans";
					}
				}
			}else{
				if(!empty($rob->billingnum)){
					$rob->transtable = "matusetrans";
				}else{
					if($rob->linetype == "services"){
						$rob->transtable = "servsaletrans";
					}else{
						if($rob->linetype == "item"){
							$rob->transtable = "matsaletrans";
						}
					}
				}
			}
			ActionSet::ReturnTransaction($rob);
		
		}
		
		$set = new Set();
		$refunline2Set = $set->MboSetRemote("refundline2")->setWhere("refundline2.refundnum='$refundnum'")
		->setRelationship("refund", "refundline2.refundnum = refund.refundnum")
		->MboRemote("refundline2.refundlinenum")->MboRemote("refundline2.linetype")->MboRemote("refundline2.torefundlinenum")
		->MboRemote("itemnum")->MboRemote("partnum")->MboRemote("refundline2.description")
		->MboRemote("refundline2.wonum")->MboRemote("refundline2.conditiontype")->MboRemote("refundline2.warehouse")
		->MboRemote("refundline2.racknum")->MboRemote("refundline2.issueunit")
		->MboRemote("refundline2.costtype")->MboRemote("refundline2.unitcost")->MboRemote("refundline2.pricecode")
		->MboRemote("refundline2.unitprice")->MboRemote("refundline2.lineprice")
		->MboRemote("refundline2.unitpricediscount")->MboRemote("refundline2.linepricediscount")
		->MboRemote("refundline2.taxcode")->MboRemote("refundline2.taxprice")->MboRemote("refundline2.varunitprice")
		->MboRemote("refundline2.varlineprice")->MboRemote("refundline2.taxpricediscount")
		->MboRemote("refundline2.requireddate")->MboRemote("refundline2.sellqty")->MboRemote("refundline2.orderunit")->MboRemote("refundline2.glcreditacct")
		->MboRemote("refundline2.customercode")->MboRemote("refundline2.gldebitacct")->MboRemote("refundline2.remarks")
		->MboRemote("refund.orgcode")->MboRemote("refund.sitecode")->MboRemote("refund.refundnum")
		->MboRemote("refund.invoicenum")->MboRemote("refund.ponum")->MboRemote("refund.sonum")
		->MboRemote("refund.billingnum")->MboRemote("refund.transnum")
		->getMboSet();
		
		$cnt = $refunline2Set->counts();
		if($cnt>0){
			for($i=0; $i<$cnt; $i++){
				$ref = $refunline2Set->getMbo();
				$rob = new stdClass();
				$rob->refundnum = $ref->getString("refundnum");
				$rob->refundlinenum = null;
				$rob->refundlinenum2 = $ref->getString("refundlinenum");
				$rob->linetype = $ref->getString("linetype");
				$rob->itemnum = $ref->getString("itemnum");
				$rob->partnum = $ref->getString("partnum");
				$rob->description = $ref->getString("description");
				$rob->wonum = $ref->getString("wonum");
				$rob->conditiontype = $ref->getString("conditiontype");
				$rob->warehouse = $ref->getString("warehouse");
				$rob->issueunit = $ref->getString("issueunit");
				$rob->costtype = $ref->getString("costtype");
				$rob->unitcost = $ref->getString("unitcost");
				$rob->pricecode = $ref->getString("pricecode");
				$rob->unitprice = $ref->getString("unitprice");
				$rob->lineprice = $ref->getString("lineprice");
				$rob->unitpricediscount = $ref->getString("unitpricediscount");
				$rob->linepricediscount = $ref->getString("linepricediscount");
				
				$rob->taxcode = $ref->getString("taxcode");
				$rob->taxprice = $ref->getString("taxprice");
				$rob->varunitprice = $ref->getString("varunitprice");
				$rob->varlineprice = $ref->getString("varlineprice");
				$rob->taxpricediscount = $ref->getString("taxpricediscount");
				$rob->requireddate = $ref->getString("requireddate");
				$rob->issuedqty = $ref->getString("sellqty");
				$rob->sellqtymin = $rob->issuedqty*-1;
				$rob->orderunit = $ref->getString("orderunit");
				$rob->glcreditacct = $ref->getString("glcreditacct");
				$rob->customercode = $ref->getString("customercode");
				$rob->gldebitacct = $ref->getString("gldebitacct");
				$rob->racknum = $ref->getString("racknum");
				$rob->remarks = $ref->getString("remarks");
				$rob->taxvalue = ActionSet::getTaxValue($rob->taxcode);
				$taxprice = $rob->taxvalue*$rob->linepricediscount;
				$rob->unittaxprice = $taxprice/$rob->issuedqty;
				$rob->transdate = $transdate;
				$rob->enterby = $enterby;			
				$rob->financialperiod = $financialperiod;
				$rob->orgcode = $ref->getString("orgcode");
				$rob->sitecode = $ref->getString("sitecode");
				$rob->torefundlinenum = $ref->getString("torefundlinenum");
				$rob->ponum = $ref->getString("ponum");
				$rob->sonum = $ref->getString("sonum");
				$rob->billingnum = $ref->getString("billingnum");
				$rob->invoicenum = $ref->getString("invoicenum");
				$rob->transnum = $ref->getString("transnum");
				if(!empty($rob->ponum)){
					if($rob->linetype == "services"){
						$rob->transtable = "servrectrans";
					}else{
						if($rob->linetype == "item"){
							$rob->transtable = "matrectrans";
						}
					}
				}else{
					if(!empty($rob->billingnum)){
						$rob->transtable = "matusetrans";
					}else{
						if($rob->linetype == "services"){
							$rob->transtable = "servsaletrans";
						}else{
							if($rob->linetype == "item"){
								$rob->transtable = "matsaletrans";
							}
						}
					}
				}
				
				
				ActionSet::IssueTransactions($rob);
			}
		}
		Set::throws(["msg"=>$refundnum]);
	}
	
	public static function IssueTransactions($value){
		//sini ambil data po, expdate, wo, vendor dari refundline2
		$ss = new Set();
		$refundlineSet = $ss->MboSetRemote("refundline")
		->setWhere("refundline.refundnum='{$value->refundnum}' and refundline.refundlinenum='{$value->torefundlinenum}'")
		->setRelationship("refund", "refundline.refundnum = refund.refundnum")
		->MboRemote("refundline.invoicenum")->MboRemote("refundline.keylinenum")->MboRemote("refundline.discountprice")
		->MboRemote("refundline.unitprofit")->MboRemote("refundline.wonum")->MboRemote("refundline.transid")->MboRemote("refundline.invoicelinenum")
		->MboRemote("refundline.costdate")->MboRemote("refundline.exchangerate")->MboRemote("refund.actualdate")
		->MboRemote("refundline.sonum")->MboRemote("refundline.billingnum")->MboRemote("refundline.itemnum ")
		->getMboSet();
		
		$refund = $refundlineSet->getMbo();
		$value->itemnum = $refund->getString("itemnum");
		$value->invoicenum = $refund->getString("invoicenum");
		$value->keylinenum = $refund->getString("keylinenum");
		$value->discountprice = $refund->getString("discountprice");
		$value->unitprofitdiscount = $refund->getString("unitprofit");
		$value->wonum = $refund->getString("wonum");
		$value->transid = $refund->getString("transid");
		$value->invoicelinenum = $refund->getString("invoicelinenum");
		$value->costdate = $refund->getString("costdate");
		$value->exchangerate = $refund->getString("exchangerate");
		$value->quantity = $value->issuedqty;
		$value->enterdate = $value->transdate;
		$value->changedate = $value->transdate;
		$value->changeby = $value->enterby;
		$value->actualdate = $refund->getString("actualdate");
		
		$ss = new Set();
		$invoiceSet = $ss->MboSetRemote("invoice")->setWhere("invoicenum='{$value->invoicenum}'")->MboRemote("paymenttype")->getMboSet();
		$invoice = $invoiceSet->getMbo();
		$value->paymenttype = $invoice->getString("paymenttype");
		
		if($value->transtable == "matsaletrans"){
			//GET Payment Type
			if(empty($value->ponum)){
				$set = new Set();
				
				if(empty($value->sonum)) {
					$wc = "transnum = '{$value->transnum}' ";
				}else{
					$wc = "sonum='{$value->sonum}' and solinenum='{$value->keylinenum}'";
				}
				$matsaleSet = $set->MboSetRemote("matsaletrans")->setWhere($wc)
				->MboRemote("taxcode")->MboRemote("expdate")->MboRemote("batchnum")->MboRemote("unitprofit")
				->MboRemote("primarydiscount")->MboRemote("secondarydiscount")->MboRemote("discountprice")->MboRemote("fromrack")
				->MboRemote("customercode")->MboRemote("unitcost")->MboRemote("salesman")->MboRemote("transnum")
				->MboRemote("transnum")->MboRemote("quantity")->MboRemote("unitcommission")->MboRemote("linecommission")
				->getMboSet();
				
				$matsale = $matsaleSet->getMbo();
				$value->taxcode = $matsale->getString("taxcode");
				$value->expdate = $matsale->getString("expdate");
				$value->batchnum = $matsale->getString("batchnum");
				$value->primarydiscount = $matsale->getString("primarydiscount");
				$value->secondarydiscount = $matsale->getString("secondarydiscount");
				
				$value->matsalediscountprice = $matsale->getString("discountprice");
				$value->matsaleqty = $matsale->getString("quantity");
				if($value->matsaleqty<0) $value->matsaleqty = $value->matsaleqty*-1;
				$value->matsalediscountprice  = $value->matsalediscountprice/$value->matsaleqty;
				
				$value->discountprice = $value->discountprice + $value->matsalediscountprice;
				$value->lineprice = $value->unitprice*$value->issuedqty;
				$taxvalue = XboSet::getTaxValue($value->taxcode);
				$value->linepricediscount = $value->unitpricediscount*$value->issuedqty;
				$value->lineprofitdiscount = $value->unitprofitdiscount*$value->issuedqty;
				$value->fromrack  =  $matsale->getString("fromrack");
				$value->customercode  =  $matsale->getString("customercode");
				$value->unitcost  =  $matsale->getString("unitcost");
				$value->linecost = $value->unitcost*$value->issuedqty;
				$value->salesman = $matsale->getString("salesman");
				$value->unitcommission = $matsale->getString("unitcommission");
				$value->linecommission = $value->unitcommission*$value->issuedqty;
				
				
			}
			if (empty($value->conditiontype)) {
				$condval = " and (conditiontype='' or conditiontype is null)";
			}
			else {
				$condval = " and conditiontype='{$value->conditiontype}'";
			}
			if(empty($value->racknum)) $rackval = " and (racknum is null or racknum='')"; 
			else $rackval = " and racknum='{$value->racknum}'";
			$value->tablename = "matsaletrans";
			$value->transtype = "SALE";
			$value->linecostmin = $value->linecost*-1;
			$ss = new Set();
			$invcost = $ss->MboSetRemote("invcost")->setWhere("itemnum='{$value->itemnum}' and warehouse='{$value->warehouse}'")->MboRemote("avgcost")->getMboSet()->getMbo();
			$avgcost = $invcost->getString("avgcost");
			
			$value->unitprofit_avg = $value->unitprice-$avgcost;
			$value->lineprofit_avg = $value->unitprofit_avg*$value->issuedqty;
			
			$value->unitprofitdiscount_avg = $value->unitpricediscount-$avgcost;
			$value->lineprofitdiscount_avg = $value->unitprofitdiscount_avg*$value->issuedqty;
			
			
			$taxvalue = XboSet::getTaxValue($value->taxcode);
			$value->taxprice = $taxvalue*$value->linepricediscount/100;
			$value->whgldebitacct  =  Xbo::getwhgl($value->warehouse, "noprint");
			$matsale = $set->MboSetRemote("matsaletrans")
			->setValue("sonum", $value->sonum)
			->setValue("transnum", $value->transnum)
			->setValue("linetype", $value->linetype)
			->setValue("itemnum", $value->itemnum)
			->setValue("conditiontype", $value->conditiontype)
			->setValue("description", $value->description)
			->setValue("quantity", $value->sellqtymin)
			->setValue("unitprice", $value->unitprice)
			->setValue("unitpricediscount", $value->unitpricediscount)
			->setValue("lineprice", $value->lineprice)
			->setValue("linepricediscount", $value->linepricediscount)
			->setValue("warehouse", $value->warehouse)
			->setValue("gldebitacct", $value->glcreditacct )
			->setValue("glcreditacct", $value->whgldebitacct)
			->setValue("transdate", $value->transdate)
			->setValue("enterdate", $value->transdate)
			->setValue("actualdate", $value->actualdate)
			->setValue("enterby", $value->enterby)
			->setValue("transtype", $value->transtype)
			->setValue("remarks", $value->remarks)
			->setValue("customercode", $value->customercode)
			->setValue("pricecode", $value->pricecode)
			->setValue("costtype", $value->costtype)
			->setValue("unitprofit", $value->unitprofit_avg)
			->setValue("lineprofit", $value->lineprofit_avg)
			->setValue("unitprofitdiscount", $value->unitprofitdiscount_avg)
			->setValue("lineprofitdiscount", $value->lineprofitdiscount_avg)
			->setValue("orgcode", $value->orgcode)
			->setValue("sitecode", $value->sitecode)
			->setValue("salesman", $value->salesman)
			->setValue("financialperiod", $value->financialperiod)
			->setValue("unitcost", $value->unitcost)
			->setValue("linecost", $value->linecostmin)
			->setValue("primarydiscount", $value->primarydiscount)
			->setValue("secondarydiscount", $value->secondarydiscount)
			->setValue("discountprice", $value->discountprice)
			->setValue("paymenttype", $value->paymenttype)
			->setValue("taxcode", $value->taxcode)
			->setValue("taxprice", $value->taxprice)
			->setValue("invoicenum", $value->invoicenum)
			->setValue("invoicelinenum", $value->invoicelinenum)
			->setValue("invoiced", "Y")
			->setValue("refundnum", $value->refundnum)
			->setValue("refundlinenum", $value->refundlinenum)
			->setValue("refundlinenum2", $value->refundlinenum2)
			->setValue("unitcommission", $value->unitcommission)
			->setValue("linecommission", $value->linecommission);
			
			$setf = new Set();
			$fifotrans = $setf->MboSetRemote("matsaletrans")
			->setValue("sonum", $value->sonum)
			->setValue("solinenum", $value->keylinenum)
			->setValue("transnum", $value->transnum)
			->setValue("linetype", $value->linetype)
			->setValue("itemnum", $value->itemnum)
			->setValue("conditiontype", $value->conditiontype)
			->setValue("description", $value->description)
			->setValue("unitprice", $value->unitprice)
			->setValue("unitpricediscount", $value->unitpricediscount)
			->setValue("warehouse", $value->warehouse)
			->setValue("gldebitacct", $value->glcreditacct)
			->setValue("glcreditacct", $value->whgldebitacct)
			->setValue("transdate", $value->transdate)
			->setValue("actualdate", $value->actualdate)
			->setValue("enterdate", $value->transdate)
			->setValue("enterby", $value->enterby)
			->setValue("transtype", $value->transtype)
			->setValue("remarks", $value->remarks)
			->setValue("customercode", $value->customercode)
			->setValue("pricecode", $value->pricecode)
			->setValue("orgcode", $value->orgcode)
			->setValue("sitecode", $value->sitecode)
			->setValue("salesman", $value->salesman)
			->setValue("financialperiod", $value->financialperiod)
			->setValue("paymenttype", $value->paymenttype)
			->setValue("taxcode", $value->taxcode)
			->setValue("primarydiscount", $value->primarydiscount)
			->setValue("secondarydiscount", $value->secondarydiscount)
			->setValue("discountprice", $value->discountprice)
			->setValue("invoicenum", $value->invoicenum)
			->setValue("invoicelinenum", $value->invoicelinenum)
			->setValue("invoiced", "Y")
			->setValue("refundnum", $value->refundnum)
			->setValue("refundlinenum", $value->refundlinenum)
			->setValue("refundlinenum2", $value->refundlinenum2)
			->setValue("unitcommission", $value->unitcommission);
			
			$value->issuedqty = $value->sellqtymin;
			$value->condval = $condval;
			$value->rackval = $rackval;
			$value->avgtrans = $matsale; 
			$value->fifotrans = $fifotrans; 
			
			$taxvalue = XboSet::getTaxValue($value->taxcode);
			$taxprice = $taxvalue*$value->linepricediscount;
			$unittaxprice = $taxprice/$value->issuedqty/100;
			if($unittaxprice<0) $unittaxprice=$unittaxprice*-1;		
							
			$value->unittaxprice = $unittaxprice; 
			$execfifo = XboSet::IssuedFIFOLIFO($value);
			
			$set = new Set();
			$invbalanceSet = $set->MboSetRemote("invbalance")->setWhere("itemnum='{$value->itemnum}' and warehouse='{$value->warehouse}' $condval $rackval")
			->MboRemote("curbal")->MboRemote("oldcurbal")
			->getMboSet();
			$invbalance = $invbalanceSet->getMbo();
			$oldcurbal = $invbalance->getString("oldcurbal");
			$curbal = $invbalance->getString("curbal");
			
			
			if($value->issuedqty<0) $value->issuedqty = $value->issuedqty*-1;
			$newcurbal = $curbal-$value->issuedqty;
			$set = new Set();
			$set->MboSetRemote("invbalance")
			->setWhere("itemnum='{$value->itemnum}' and warehouse='{$value->warehouse}' $condval $rackval")->setValue("curbal", $newcurbal)->setValue("oldcurbal", $curbal)->change();
			
			$value->transtype = "sales-refund";
			
			$cn_execfifo = count($execfifo);
			//echo "count execfifo : $cn_execfifo";
			//var_dump($execfifo);
			for($i=0; $i<$cn_execfifo; $i++){
				$data = $execfifo[$i];
				$value->unitcost = $data->unitcost;
				$value->linecost = $data->linecost;
				$value->unitprice = $data->unitprice;
				$value->lineprice = $data->lineprice;
				$value->linepricediscount = $data->linepricediscount;
				$value->unitprofit =$data->unitprofit;
				$value->lineprofit = $data->lineprofit;
				$value->unitprofitdiscount = $data->unitprofitdiscount;
				$value->lineprofitdiscount = $data->lineprofitdiscount;
				$value->gldebitacct = $value->whgldebitacct;
				
				ActionSet::RefundInvoiceTrans($value, "issue");
			}
		}else{
			if($value->transtable == "matrectrans"){
				
			}else{
				if($value->transtable == "servrectrans"){
					
				}else{
					if($value->transtable == "servsaletrans"){
						//" ISSUE DISINI ";
					}else{
						if($value->transtable == "matusetrans"){
							
							$set =  new Set();
							$billSet = $set->MboSetRemote("billingline")
							->setWhere("billingline.billingnum='{$value->billingnum}' and billingline.billinglinenum='{$value->keylinenum}'")
							->setRelationship("billing", "billing.billingnum = billingline.billingnum")
							->MboRemote("billingline.unitcost")->MboRemote("billingline.unitprice")->MboRemote("billingline.unitpricediscount")
							->MboRemote("billingline.unitprofit")->MboRemote("billingline.unitprofitdiscount")
							->MboRemote("billingline.wonum")->MboRemote("billing.customercode")->MboRemote("billing.billingnum")
							->MboRemote("billingline.itemnum")->MboRemote("billingline.costdate")
							->MboRemote("billingline.conditiontype")->MboRemote("billingline.fromwarehouse")->MboRemote("billingline.fromracknum")
							->MboRemote("billingline.tax")
							->getMboSet();
							
							$bill = $billSet->getMbo();
							$wonum = $bill->getString("wonum");
							$billingnum = $bill->getString("billingnum");
							$customercode = $bill->getString("customercode");
							$itemnum = $bill->getString("itemnum"); 
							$costdate = $bill->getString("costdate"); 
							$wccostdate = Super::whereDate("costdate", $costdate);
							$conditiontype = $bill->getString("conditiontype");
							$fromwarehouse = $bill->getString("fromwarehouse");
							$fromracknum = $bill->getString("fromracknum");
							$value->taxcode =  $bill->getString("tax");
							$value->customercode = $customercode;
							if (empty($conditiontype)) {
								$condval = " and (conditiontype='' or conditiontype is null)";
							}
							else {
								$condval = " and conditiontype='{$value->conditiontype}'";
							}
							
							if(empty($fromracknum)) $rackval = " and (racknum is null or racknum='')"; 
							else $rackval = " and racknum='{$fromracknum}'";
							
							
							$st = new Set();
							$matuseSet = $st->MboSetRemote("matusetrans")
							->setWhere("wonum='$wonum' and itemnum='$itemnum' and warehouse='$fromwarehouse' $condval $rackval $wccostdate ")->getMboSet();
							$matuse = $matuseSet->getMbo();
							$value->expdate = $matuse->getString("expireddate");
							$value->batchnum = $matuse->getString("batchnum");
							$gldebitacct = $matuse->getString("gldebitacct");
							$glcreditacct = $matuse->getString("glcreditacct");
							$matuseqty = $matuse->getString("quantity");
							$discountprice = $matuse->getString("discountprice");
							$unitprofit = $matuse->getString("unitprofit");
							$mrnum = $matuse->getString("mrnum");
							$lineprofit = $value->issuedqty*$unitprofit;
							$matusediscountprice = $discountprice/$matuseqty;
							$unitcommission = $matuse->getString("unitcommission");
							$linecommission = $unitcommission*$value->issuedqty;
							if($matusediscountprice<0) $matusediscountprice = $matusediscountprice*-1;
							
							$primarydiscount = $matuse->getString("primarydiscount");
							$secondarydiscount = $matuse->getString("secondarydiscount");
							$issuetype = $matuse->getString("issuetype");
							$orgcode = $matuse->getString("orgcode");
							$sitecode = $matuse->getString("sitecode");
							$requestedby = $matuse->getString("requestedby");
							$assetnum = $matuse->getString("assetnum");
							$location = $matuse->getString("location");
							$unitcost = $matuse->getString("unitcost");
						
							if($matuseqty<0) $matuseqty = $matuseqty*-1;
							
							$si = new Set();
							$invbalSet = $si->MboSetRemote("invbalance")->setWhere("itemnum='{$value->itemnum}' and warehouse='{$value->warehouse}' $condval $rackval")->MboRemote("curbal")->getMboSet();
							$invbal = $invbalSet->getMbo();
							$oldcurbal = $invbal->getString("curbal");
							$newcurbal = $oldcurbal-$value->quantity;
							
							
							$linecost = $unitcost*$value->quantity;
							$unitprice = $value->unitcost;
							$lineprice = $unitprice*$value->quantity;
							
							$invcostSet = $set->MboSetRemote("invcost")->setWhere("itemnum='$itemnum' and warehouse='{$value->warehouse}' $condval")->MboRemote("avgcost")->getMboSet();
							$invcost = $invcostSet->getMbo();
							$avgcost = $invcost->getString("avgcost");
							
							
							
							
							if(empty($value->racknum)) $rackval = " and (racknum is null or racknum='')"; 
							else $rackval = " and racknum='{$value->racknum}'";
							
							$value->discountprice = $value->discountprice/$matuseqty;
							$value->wonum = $wonum;
							$value->condval = $condval;
							$value->rackval = $rackval;
							$value->avgcost = $avgcost;
							$value->unitcost = $avgcost;
							$value->unitprice = $value->unitprice;
							$value->unitpricediscount = $value->unitpricediscount;
							$value->tablename = $value->transtable;
							$value->transtype = "ISSUE";
							$value->isconsignment = "Y";
							$linecostMinus = $value->avgcost*$value->sellqtymin;
							$value->lineprice = $value->unitprice*$value->issuedqty;
							$value->linepricediscount = $value->unitpricediscount*$value->issuedqty;
							$value->unitprofit = $value->unitprice-$value->unitcost;
							$value->lineprofit = $value->unitprofit*$value->issuedqty;
							$value->lineprofitdiscount = $value->unitprofitdiscount*$value->issuedqty;
							$value->unitcommission = $unitcommission;
							$value->linecommission = $linecommission;
							
							$value->glcreditacct2  =  Xbo::getwhgl($value->warehouse, "noprint");
							$taxvalue = XboSet::getTaxValue($value->taxcode);
							$value->taxprice = $value->linepricediscount*$taxvalue/100;
							
							
							$setin = new Set();
							$matuse = $setin->MboSetRemote("matusetrans")
							->setValue("wonum", $wonum)
							->setValue("assetnum", $assetnum)
							->setValue("location", $location)
							->setValue("itemnum", $value->itemnum)
							->setValue("conditiontype", $value->conditiontype)
							->setValue("warehouse", $value->warehouse)
							->setValue("racknum", $value->racknum)
							->setValue("quantity", $value->sellqtymin)
							->setValue("oldcurbal", $oldcurbal)
							->setValue("newcurbal", $newcurbal)
							->setValue("unitcost", $avgcost)
							->setValue("linecost", $linecostMinus)
							->setValue("enterby", $value->enterby)
							->setValue("gldebitacct", $value->gldebitacct)
							->setValue("glcreditacct", $value->glcreditacct2)
							->setValue("transtype", $value->transtype)
							->setValue("enterdate", $value->enterdate)
							->setValue("changeby", $value->enterby)
							->setValue("changedate", $value->enterdate)
							->setValue("orgcode", $value->orgcode)
							->setValue("sitecode", $value->sitecode)
							->setValue("requestedby", $requestedby)
							->setValue("description", $value->description)
							->setValue("expireddate", $value->expdate)
							->setValue("isconsignment", $value->isconsignment)
							->setValue("unitprice", $value->unitprice)
							->setValue("unitpricediscount", $value->unitpricediscount)
							->setValue("lineprice", $value->lineprice)
							->setValue("linepricediscount", $value->linepricediscount)
							->setValue("unitprofit", $value->unitprofit)
							->setValue("lineprofit", $value->lineprofit)
							->setValue("remarks", $value->remarks)
							->setValue("actualdate", $value->actualdate)
							->setValue("unitprofitdiscount", $value->unitprofitdiscount)
							->setValue("lineprofitdiscount", $value->lineprofitdiscount)
							->setValue("primarydiscount", $primarydiscount)
							->setValue("secondarydiscount", $secondarydiscount)
							->setValue("discountprice", $value->discountprice)
							->setValue("invoicenum", $value->invoicenum)
							->setValue("refundnum", $value->refundnum)
							->setValue("refundlinenum", $value->refundlinenum)
							->setValue("refundlinenum2", $value->refundlinenum2)
							->setValue("unitcommission", $value->unitcommission)
							->setValue("linecommission", $value->linecommission);
							
							
							$setf = new Set();
							$fifotrans = $setf->MboSetRemote("matusetrans")
							->setValue("wonum", $wonum)
							->setValue("assetnum", $assetnum)
							->setValue("location", $location)
							->setValue("itemnum", $value->itemnum)
							->setValue("conditiontype", $value->conditiontype)
							->setValue("warehouse", $value->warehouse)				
							->setValue("enterby", $value->enterby)
							->setValue("gldebitacct", $value->gldebitacct)
							->setValue("glcreditacct", $value->glcreditacct2)
							->setValue("transtype", $value->transtype)
							->setValue("enterdate", $value->enterdate)
							->setValue("changeby", $value->enterby)
							->setValue("changedate", $value->enterdate)
							->setValue("orgcode", $value->orgcode)
							->setValue("sitecode", $value->sitecode)
							->setValue("requestedby", $requestedby)
							->setValue("description", $value->description)
							->setValue("isconsignment", $value->isconsignment)
							->setValue("unitprice", $value->unitprice)
							->setValue("unitpricediscount", $value->unitpricediscount)
							->setValue("remarks", $value->remarks)
							->setValue("actualdate", $value->actualdate)
							->setValue("primarydiscount", $primarydiscount)
							->setValue("secondarydiscount", $secondarydiscount)
							->setValue("discountprice", $value->discountprice)
							->setValue("invoicenum", $value->invoicenum)
							->setValue("refundnum", $value->refundnum)
							->setValue("refundlinenum", $value->refundlinenum)
							->setValue("refundlinenum2", $value->refundlinenum2)
							->setValue("unitcommission", $value->unitcommission)
							->setValue("linecommission", $value->linecommission);
							
							$fl = new stdClass();
							$fl->tablename = "matusetrans";
							$fl->transtype = $value->transtype;
							$fl->itemnum = $value->itemnum;
							$fl->warehouse = $value->warehouse;
							$fl->conditiontype = $value->conditiontype;
							$fl->racknum = $value->racknum;
							$fl->issuedqty = $value->sellqtymin;
							$fl->condval = $value->condval;
							$fl->rackval = $value->rackval;
							$fl->avgtrans = $matuse;
							$fl->fifotrans = $fifotrans;
							$fl->unitcost = $avgcost;
							$fl->unitprice = $value->unitprice;
							$fl->unitpricediscount = $value->unitpricediscount;
							
							$execfifo = XboSet::IssuedFIFOLIFO($fl);
							//var_dump($execfifo);
							$value->transtype = 'sales-refund';
													
							$cn_execfifo = count($execfifo);
							
							$si = new Set();
							$invoicetransSet = $si->MboSetRemote("invoicetrans")->setWhere("invoicenum='{$value->invoicenum}' and invoicelinenum='{$value->invoicelinenum}' and transtype='sales'")
							->MboRemote("salesman")->MboRemote("transnum")->getMboSet();							
							$ivctrans = $invoicetransSet->getMbo();
							
							$value->salesman = $ivctrans->getString("salesman");
							$value->transnum = $ivctrans->getString("transnum");
							//echo "SINI DEN cn_execfifo $cn_execfifo";
							for($i=0; $i<$cn_execfifo; $i++){
								$data = $execfifo[$i];
								$value->unitcost = $data->unitcost;
								$value->linecost = $data->linecost;
								$value->unitprice = $data->unitprice;
								$value->lineprice = $data->lineprice;
								$value->linepricediscount = $data->linepricediscount;
								$value->unitprofit =$data->unitprofit;
								$value->lineprofit = $data->lineprofit;
								$value->unitprofitdiscount = $data->unitprofitdiscount;
								$value->lineprofitdiscount = $data->lineprofitdiscount;
								
								ActionSet::RefundInvoiceTrans($value, "issue");
							}
			
							if($execfifo == "notenoughfifoqty"){
								$cmsg = "notenoughfifoqty";
							}else{
								$isbalexistSet = $set->MboSetRemote("invbalance")->setWhere("itemnum='{$value->itemnum}' and warehouse='{$value->warehouse}' {$value->condval} {$value->rackval}")
								->MboRemote("itemnum")->getMboSet();
								$invbalexist = $isbalexistSet->counts();
								if ($invbalexist > 0) {
									$setup = new Set();
									$setup->MboSetRemote("invbalance")->setWhere("itemnum='{$value->itemnum}' and warehouse='{$value->warehouse}' {$value->condval} {$value->rackval}")
									->setValue("curbal", $newcurbal)
									->setValue("oldcurbal", $oldcurbal)
									->setValue("changeby", $value->enterby)
									->setValue("changedate", $value->enterdate)
									->change();
								}
								if ($invbalexist == 0) {
									$setin = new Set();
									$setup->MboSetRemote("invbalance")
									->setValue("itemnum", $value->itemnum)
									->setValue("itemnum", $value->itemnum)
									->setValue("conditiontype", $value->conditiontype)
									->setValue("warehouse", $value->warehouse)
									->setValue("racknum", $value->racknum)
									->setValue("curbal", $newcurbal)
									->setValue("oldcurbal", $newcurbal)
									->setValue("enterby", $value->enterby)
									->setValue("enterdate", $value->enterdate)
									->setValue("changeby", $value->enterby)
									->setValue("changedate", $value->enterdate)
									->setValue("orgcode", $value->orgcode)
									->setValue("sitecode", $value->sitecode)->add();
								}
								
							}
						}
					}
				}
			}
		}
		
	}
	
	public static function ValidateQty(){
		$refundnum = $_POST['validateqty'];
		$transid = $_POST['transid'];
		$set = new Set();
		$invoiceqty=$set->MboSetRemote("invoicetrans")->setWhere("invoicetransid='$transid'")->MboRemote("quantity")->getMboSet()->getMbo()->getString("quantity");
		Set::throws(["invoiceqty" => $invoiceqty]);
	}
	public static function SumRefundCost(){
		$refundnum = $_GET["sumrefundcost"];
		$set = new Set();
		$rfSet = $set->MboSetRemote("refundline")->setWhere("refundnum='$refundnum'")->MboRemote("sum(linecost) as total")->getMboSet();
		$rf = $rfSet->getMbo();
		
		$total = $rf->getString("total");
		
		$set = new Set();
		$rfSet2 = $set->MboSetRemote("refundline2")->setWhere("refundnum='$refundnum'")->MboRemote("sum(linepricediscount) as totalrefund")->getMboSet();
		$rf2 = $rfSet2->getMbo();
		$totalrefund = $rf2->getString("totalrefund");
		Set::throws([
			"total"=>$total,
			"totalrefund"=>$totalrefund,
		]);
	}
	
	public static function WriteRefund($value){
		$pts = explode("`", $value);
		$refundnum = $_POST["refundnum"];
		$id = $pts[1];
		
		$enterdate = Xbo::setEnterDate();
		$enterby = Xbo::getLoginUser();
		$set = new Set();
		$invoicetransSet = $set->MboSetRemote("invoicetrans")->setWhere("invoicetransid=$id")
		->MboRemote("linetype")->MboRemote("itemnum")->MboRemote("description")->MboRemote("invoicenum")
		->MboRemote("ponum")->MboRemote("sonum")->MboRemote("wonum")->MboRemote("billingnum")
		->MboRemote("transnum")->MboRemote("quantity")->MboRemote("unitcost")
		->MboRemote("linecost")->MboRemote("unitprofitdiscount")->MboRemote("lineprofitdiscount")->MboRemote("warehouse")->MboRemote("linepricediscount")
		->MboRemote("costtype")->MboRemote("costdate")->MboRemote("gldebitacct")->MboRemote("glcreditacct")->MboRemote("invoicetransid")
		->MboRemote("conditiontype")->MboRemote("unitpricediscount")->MboRemote("paymenttype")->MboRemote("invoicelinenum")->MboRemote("keylinenum")
		->MboRemote("exchangerate")
		->getMboSet();
		
		$cnt = $invoicetransSet->counts();
		for($i=0; $i<$cnt; $i++){
			$ivc = $invoicetransSet->getMbo();
			$linetype = $ivc->getString("linetype");
			$itemnum = $ivc->getString("itemnum");
			$description = $ivc->getString("description");
			$invoicenum = $ivc->getString("invoicenum");
			$billingnum = $ivc->getString("billingnum");
			$transnum = $ivc->getString("transnum");
			$quantity = $ivc->getString("quantity");
			$ponum = $ivc->getString("ponum");
			$paymenttype =  $ivc->getString("paymenttype");
			$invoicelinenum =  $ivc->getString("invoicelinenum");
			$exchangerate =  $ivc->getString("exchangerate");
			if(!empty($ponum)) { 
				$unitcost = $ivc->getString("unitcost");
			}
			else {
				$unitcost = $ivc->getString("unitpricediscount");
			}
			$unitprofitdiscount = $ivc->getString("unitprofitdiscount");
			$lineprofitdiscount = $ivc->getString("lineprofitdiscount");
			$warehouse = $ivc->getString("warehouse");
			$costtype = $ivc->getString("costtype");
			$costdate = $ivc->getString("costdate");
			$gldebitacct = $ivc->getString("gldebitacct");
			$glcreditacct = $ivc->getString("glcreditacct");
			$wonum = $ivc->getString("wonum");
			$sonum = $ivc->getString("sonum");
			$conditiontype = $ivc->getString("conditiontype");
			$keylinenum = $ivc->getString("keylinenum");
			
			if(!empty($ponum)) $linecost = $ivc->getString("linecost");
			else $linecost = $ivc->getString("linepricediscount");
			$invoicetransid = $ivc->getString("invoicetransid");
			$refundlinenum = Xbo::getLineNumber("refundline", "refundlinenum", "refundnum='$refundnum'");
			$ss = new Set();
			$issueunit = $ss->MboSetRemote("item")->setWhere("itemnum='$itemnum'")->MboRemote("issueunit")->getMboSet()->getMbo()->getString("issueunit");
			
			$si = new Set();
			$counttransid = $si->MboSetRemote("refundline")->setWhere("transid=$invoicetransid")->MboRemote("transid")->getMboSet()->counts();
			
			
			if($counttransid == 0){
				$sq = new Set();
				$invoicelineSet = $sq->MboSetRemote("invoiceline")->setWhere("invoicenum='$invoicenum' and invoicelinenum='$invoicelinenum'")
				->MboRemote("discount")->MboRemote("discountprice")->getMboSet();
				$invc = $invoicelineSet->getMbo();
				$discount = $invc->getString("discount");
				$discountprice = $invc->getString("discountprice");
				
				if(!empty($ponum )) {
					$sp = new Set();
					$matrecSet = $sp->MboSetRemote("matrectrans")->setWhere("ponum='$ponum' and itemnum='$itemnum'")
					->MboRemote("racknum")->MboRemote("conditiontype")->getMboSet();
					$matrec = $matrecSet->getMbo();
					$fromrack = $matrec->getString("racknum");
					$conditiontype = $matrec->getString("conditiontype");
					$towarehouse = null;
					$gldebit = $glcreditacct;
					$glcredit = $gldebitacct;
					
				}else{
					
					if(!empty($sonum) || !empty($transnum)){
						if(!empty($sonum )) { 
							$st = " and sonum='$sonum'";
							if(!empty($keylinenum)) {
								$st = $st. " and solinenum = '$keylinenum' ";
							}
						}
						if(!empty($transnum )) {
							$st = " and transnum='$transnum'";
						}
						
						$sa = new Set();
						$matsaleSet = $sa->MboSetRemote("matsaletrans")->setWhere("itemnum='$itemnum' $st and transtype='SALE'")
						->MboRemote("warehouse")->MboRemote("fromrack")->MboRemote("conditiontype")->getMboSet();
						$matsale = $matsaleSet->getMbo();
						$fromrack = $matsale->getString("fromrack");
						$conditiontype = $matsale->getString("conditiontype");
						
					}else{
						if(!empty($billingnum)) {
							
							$sb = new Set();
							$billinglineSet = $sb->MboSetRemote("billingline")->setWhere("billingnum='$billingnum' and itemnum='$itemnum'")
							->MboRemote("fromwarehouse")->MboRemote("fromracknum")->MboRemote("conditiontype")
							->MboRemote("wonum")->getMboSet();
							
							$billing = $billinglineSet->getMbo();
							if(empty ($wonum)) $wonum = $billing->getString("wonum");
							$warehouse = $billing->getString("fromwarehouse");
							$fromrack = $billing->getString("fromracknum");
							$conditiontype = $billing->getString("conditiontype");
						}
					}
					
					$towarehouse = $warehouse;
					$gldebit = $gldebitacct;
					$glcredit = $glcreditacct;
				}
				
				
				
				if(empty($costtype) || empty($conditiontype)) {
					if(empty($fromrack)){
						$si = new Set();
						$inventorySet = $si->MboSetRemote("invbalance")
						->setRelationship("inventory", "invbalance.itemnum = inventory.itemnum and invbalance.warehouse = inventory.warehouse")
						->setWhere("invbalance.itemnum='$itemnum' and invbalance.warehouse='$warehouse'")
						->MboRemote("inventory.issuetype")->MboRemote("invbalance.conditiontype")->MboRemote("invbalance.racknum")->getMboSet();
						$inventory = $inventorySet->getMbo();
						$costtype = $inventory->getString("issuetype");
						$conditiontype = $inventory->getString("conditiontype");
						$fromrack = $inventory->getString("racknum");
						
					}else{
						$si = new Set();
						$inventorySet = $si->MboSetRemote("invbalance")
						->setRelationship("inventory", "invbalance.itemnum = inventory.itemnum and invbalance.warehouse = inventory.warehouse")
						->setWhere("invbalance.itemnum='$itemnum' and invbalance.warehouse='$warehouse' and invbalance.racknum='$fromrack'")
						->MboRemote("inventory.issuetype")->MboRemote("invbalance.conditiontype")->getMboSet();
						$inventory = $inventorySet->getMbo();
						$costtype = $inventory->getString("issuetype");
						$conditiontype = $inventory->getString("conditiontype");
					}
					
				}
				
				
				if(empty($costdate)){
					$si = new Set();
					if (empty($conditiontype)) {
						$condval = " and (conditiontype='' or conditiontype is null)";
					}
					else {
						$condval = " and conditiontype='$conditiontype'";
					}
					
					if(empty($fromrack)) $rackval = " and (racknum is null or racknum='')"; 
					else $rackval = " and racknum='{$fromrack}'";
					
					
					if(!empty($ponum)) $sss = " and ponum='$ponum' ";
					else{
						if(!empty($wonum)) $sss = " and wonum='$wonum' ";
						else{
							$sss = "";
						}
					} 
					
					$invfifolifocostSet = $si->MboSetRemote("invfifolifocost")->setWhere("itemnum='$itemnum' and warehouse = '$warehouse' $condval $rackval $sss")
					->MboRemote("costdate")->getMboSet();
					$invfifolifo = $invfifolifocostSet->getMbo();
					$costdate = $invfifolifo->getString("costdate");
					
				}
				$si = new Set();
				$si->MboSetRemote("refundline")->setValue("refundnum", $refundnum)->setValue("gldebitacct", $gldebit)
				->setValue("linetype", $linetype)->setValue("unitcost", $unitcost)->setValue("glcreditacct", $glcredit)
				->setValue("itemnum", $itemnum)->setValue("linecost", $linecost)->setValue("wonum", $wonum)
				->setValue("description", $description)->setValue("unitprofit", $unitprofitdiscount)->setValue("sonum", $sonum)
				->setValue("invoicenum", $invoicenum)->setValue("lineprofit", $lineprofitdiscount)->setValue("conditiontype", $conditiontype)
				->setValue("billingnum", $billingnum)->setValue("warehouse", $warehouse)->setValue("ponum", $ponum)->setValue("issueunit", $issueunit)
				->setValue("transnum", $transnum)->setValue("costtype", $costtype)->setValue("transid", $invoicetransid)
				->setValue("quantity", $quantity)->setValue("costdate", $costdate)->setValue("refundlinenum", $refundlinenum)
				->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)->setValue("paymenttype", $paymenttype)
				->setValue("towarehouse", $towarehouse)->setValue("invoicelinenum", $invoicelinenum)->setValue("keylinenum", $keylinenum)
				->setValue("discount", $discount)->setValue("discountprice", $discountprice)->setValue("exchangerate", $exchangerate)
				->setValue("fromrack", $fromrack)
				->add();
			}else{
			}
		}
		$msg = "written";
		return $msg;
	}
	public static function WriteRefundSet2(){
		$value = $_POST["refundtrans2"];
		$countval = count($value);
		for($i=0; $i<$countval; $i++){
			$msg = ActionSet::WriteRefund2($value[$i]);
		}
		Set::throws(["msg" => $msg]);
	}
	
	public static function WriteRefundSet(){
		$value = $_POST["refundtrans"];
		$countval = count($value);
		for($i=0; $i<$countval; $i++){
			$msg = ActionSet::WriteRefund($value[$i]);
		}
		Set::throws(["msg" => $msg]);
	}
	public static function WriteRefund2($value){
		$pts = explode("`", $value);
		$refundnum = $_POST["refundnum"];
		$id = $pts[1];
		
		$set = new Set();
		$invbalanceSet = $set->MboSetRemote("invbalance")->setRelationship("item", "item.itemnum=invbalance.itemnum ")->setWhere("invbalance.invbalanceid='$id'")->MboRemote("invbalance.itemnum")->MboRemote("invbalance.warehouse")->MboRemote("invbalance.racknum")->MboRemote("invbalance.conditiontype")->MboRemote("invbalance.curbal")->MboRemote("invbalance.orgcode")->MboRemote("invbalance.sitecode")->MboRemote("item.stocktype")->getMboSet("remove_duplicate")->getMbo();
		$itemnum = $invbalanceSet->getString("itemnum");
		$warehouse = $invbalanceSet->getString("warehouse");
		$racknum = $invbalanceSet->getString("racknum");
		$conditiontype = $invbalanceSet->getString("conditiontype");
		$customercode = "";
		$condval = "";
		if (empty($conditiontype)) {
			$condval = "(conditiontype='' or conditiontype is null)";
		}
		else {
			$condval = "conditiontype='$conditiontype'";
		}
		$curbal = $invbalanceSet->getString("curbal");
		$stocktype = $invbalanceSet->getString("stocktype");
		$orgcode = $invbalanceSet->getString("orgcode");
		$sitecode = $invbalanceSet->getString("sitecode");
		
		
		$costtype = Xbo::InventoryCostType($itemnum, $warehouse, $orgcode);
		
		
		if($costtype == "AVG"){
			$set = new Set();
			$invcostSet =  $set->MboSetRemote("invcost")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' and $condval and orgcode='$orgcode' and sitecode='$sitecode'")->MboRemote("avgcost")->getMboSet();
			$unitcost = $invcostSet->getMbo()->getString("avgcost");
		}else{
			$unitcost = 0;
			
		}
		$set = new Set();
		$invprice = $set->MboSetRemote("invprice")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' and $condval and defaultprice='Y'")->MboRemote("pricecode")
		->MboRemote("unitprice")->getMboSet()->getMbo();
		$pricecode = $invprice->getString("pricecode");
		
		$u = new Xbo();
		$up = $u->getUnitPriceDiscount($orgcode, $itemnum, $warehouse, $conditiontype);
		$unitprice = $u->unitpricediscount;
		
		
		if ($unitcost == null) $unitcost = 0;
		
		
		$ss = new Set();
		$rflineSet = $ss->MboSetRemote("refundline")->setWhere("refundnum='$refundnum'")->MboRemote("invoicenum")->getMboSet();
		$rf = $rflineSet->getMbo();
		
		$invoicenum = $rf->getString("invoicenum");
		
		$ss = new Set();
		$invoiceSet = $ss->MboSetRemote("invoice")->setWhere("invoicenum='$invoicenum'")->MboRemote("paymenttype")->getMboSet();
		$invoice = $invoiceSet->getMbo();
		$paymenttype = $invoice->getString("paymenttype");
		
		
		if($paymenttype == "kredit"){
			$glcreditacct = Xbo::getCustomerAPAcct($customercode, $orgcode, $sitecode); // stok/penjualan berkurang
			$gldebitacct = Xbo::getCustomerRBNIAcct($customercode, $orgcode, $sitecode); //nambah piutang
		}else{
			if($paymenttype == "cashonbank"){
				$gldebitacct = Xbo::getCashOnBankAcct($orgcode, $sitecode); // nambah kas BANK perusahaan
			}else{
				if($paymenttype == "cashonhand"){
					$gldebitacct = Xbo::getCashOnHandAcct($orgcode, $sitecode); // nambah kas on HAND perusahaan
				}
			}
			//$glcreditacct = Xbo::getControlAcct($warehouse, $orgcode, $sitecode); // mengurangi stok persediaan
			$glcreditacct = Xbo::getCustomerRBNIAcct($customercode, $orgcode, $sitecode);  //PIUTANG
		}
		
		
		if ($curbal < 1) {
			Set::throws(["msg" => "ZeroBalance"]);
			return null;
		}
		if ($stocktype == "UNSELLABLE") {
			Set::throws(["msg" => "unsellable"]);
			return null;
		}
		if ($curbal >= 1) {
			$refundlinenum = Xbo::getLineNumber("refundline2", "refundlinenum", "refundnum='$refundnum'");
			$enterdate = Xbo::setEnterDate();
			$enterby = Xbo::getLoginUser();
			
			$si = new Set();
			$itemSet = $si->MboSetRemote("item")->setWhere("itemnum='$itemnum'")->MboRemote("description")
			->getMboSet();
			$item = $itemSet->getMbo();
			$description = $item->getString("description");
			
			$sr = new Set();
			//default 
			$sellqty =1;
			//
			$lineprice = $sellqty*$unitprice;
			$linepricediscount = $sellqty*$unitprice;
			
			$sr->MboSetRemote("refundline2")->setValue("refundnum", $refundnum)->setValue("refundlinenum", $refundlinenum)
			->setValue("linetype", "item")->setValue("itemnum", $itemnum)->setValue("description", $description)
			->setValue("warehouse", $warehouse)->setValue("racknum", $racknum)->setValue("conditiontype", $conditiontype)
			->setValue("costtype", $costtype)->setValue("pricecode", $pricecode)->setValue("unitprice", $unitprice)
			->setValue("unitpricediscount", $unitprice)->setValue("linepricediscount", $linepricediscount)
			->setValue("customercode", $customercode)->setValue("lineprice", $lineprice)
			->setValue("sellqty", $sellqty)->setValue("gldebitacct", $glcreditacct )->setValue("glcreditacct", $gldebitacct)
			->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)->setValue("sentstatus", "NONE")
			->add();
		}
	}
} 

Class ValidateTables {
	public static function tbcx(){
		$dl = explode("|", $_GET["tbcx"]);
		$set = new Set();
		if ($dl[0] == "locations") {
			if ($dl[2] == "matusetrans") echo json_encode(["msg" => "relateddata", "rltbl" => "matusetrans"]);
			if ($dl[2] == "assets") echo json_encode(["msg" => "relateddata", "rltbl" => "assets"]);
		}
	}
}
	

?>