<?php
Class CommonActions {
	public static function ChangeExpDate(){
		$set = new Set();
		$ponum = $_POST["ponum"];
		$expdate = $_POST["changeexpdate"];
		$enterdate = $_POST["enterdate"];
		$polinenum = $_POST["polinenum"];
		$matrectransSet = $set->MboSetRemote("matrectrans")->setWhere("ponum='$ponum' and polinenum='$polinenum'")->MboRemote("itemnum")->getMboSet();
		$matrectrans = $matrectransSet->getMbo();
		$itemnum = $matrectrans->getString("itemnum");
		
		if(!empty($itemnum)){
			
			$enterdate = Super::whereDate("enterdate", $enterdate);
			$wc = "ponum='$ponum' and polinenum='$polinenum' and itemnum='$itemnum' $enterdate";
			
			$invfifolifocostSet = $set->MboSetRemote("invfifolifocost")->setWhere($wc)->getMboSet();
			$cnt = $invfifolifocostSet->counts();
			if($cnt == 0 ){
				Set::throws(["msg"=>null]);
			}else{
				$set->MboSetRemote("invfifolifocost")->setWhere($wc)->setValue("expdate", $expdate)->change();
			}
		}else{
			Set::throws(["msg"=>null]);
		}
	}
	public static function ChangeReceivedDate(){
		$set = new Set();
		$ponum = $_POST["ponum"];
		$actualdate = $_POST["changerecdate"];
		$enterdate = $_POST["enterdate"];
		$polinenum = $_POST["polinenum"];
		$matrectransSet = $set->MboSetRemote("matrectrans")->setWhere("ponum='$ponum' and polinenum='$polinenum'")->MboRemote("itemnum")->getMboSet();
		$matrectrans = $matrectransSet->getMbo();
		$itemnum = $matrectrans->getString("itemnum");
		
		if(!empty($itemnum)){
			
			$enterdate = Super::whereDate("enterdate", $enterdate);
			$wc = "ponum='$ponum' and polinenum='$polinenum' and itemnum='$itemnum' $enterdate";
			
			$invfifolifocostSet = $set->MboSetRemote("invfifolifocost")->setWhere($wc)->getMboSet();
			$cnt = $invfifolifocostSet->counts();
			if($cnt == 0 ){
				Set::throws(["msg"=>null]);
			}else{
				$set->MboSetRemote("invfifolifocost")->setWhere($wc)->setValue("costdate", $actualdate)->change();
			}
		}else{
			Set::throws(["msg"=>null]);
		}
	}
	
	
	public static function ReceiptFIFOLIFO($fl){
		$set = new Set();
		
		if($fl->actualdate == null) $fl->costdate=$fl->enterdate;
		$set->MboSetRemote("invfifolifocost")
		->setValue("itemnum", $fl->itemnum)->setValue("conditiontype", $fl->conditiontype)->setValue("costdate", $fl->costdate)->setValue("vendorcode", $fl->vendorcode)
		->setValue("financialperiod", $fl->financialperiod)->setValue("unitcost", $fl->unitcost)->setValue("quantity", $fl->quantity)->setValue("racknum", $fl->racknum)
		->setValue("conditionrate", $fl->conditionrate)->setValue("orgcode", $fl->orgcode)->setValue("sitecode", $fl->sitecode)->setValue("warehouse", $fl->warehouse)
		->setValue("ponum", $fl->ponum)->setValue("polinenum", $fl->polinenum)->setValue("enterdate", $fl->enterdate)->setValue("batchnum", $fl->batchnum)
		->add();
	}
	
	
	public static function ReturnTransaction($value=null)

	{
		$transtable = $value["sourcetable"];
		$ponum = $value["ponum"];
		$polinenum = $value["polinenum"];
		$retQty = $value["restqty"];
		$actualdate = $value["actualdate"];
		$remarks = $value["remarks"];
		$retQtyMinus = $retQty * -1;
			
		if(isset($value["racknum"])) $racknum = $value["racknum"];
		else $racknum = null;
		
		if(isset($value["warehouse"])) $warehouse = $value["warehouse"];
		else $warehouse = null;
		
		if(isset($value["conditiontype"])) $conditiontype = $value["conditiontype"];
		else $conditiontype = null;
		 
		
		if(isset($value["batchnum"])) $batchnum = $value["batchnum"];
		else $batchnum = null;
		
		if(isset($value["costdate"])) $costdate = $value["costdate"];
		else $costdate = null;
		
		$colnames = "";
		$set = new Set();
		$retQtyConversion = 0;
		$condval = "";
		if (empty($conditiontype)) {
			$condval = "and (conditiontype='' or conditiontype is null)";
			$conditiontype = null;
		}
		else {
			$condval = "and conditiontype='$conditiontype'";
		}
		if (empty($racknum)) {
			$rackval = "and (racknum='' or racknum is null)";
		}
		else {
			$rackval = "and racknum='$racknum'";
		}
		$vset = new Set();
		$vendorSet = $vset->MboSetRemote("vendor")->setRelationship("po", "po.vendor = vendor.vendorcode")
		->setWhere("ponum='$ponum'")->MboRemote("vendor.name as vendorname")->MboRemote("po.vendor")->getMboSet();
		$vend = $vendorSet->getMbo();
		
		$vendorcode = $vend->getString("vendor");
		$vendorname = $vend->getString("vendorname");
		
		$ss = new Set();
		$poSet = $ss->MboSetRemote("po")->setWhere("ponum='$ponum'")->MboRemote("exchangerate")->MboRemote("discountpercentage")->getMboSet();
		$po = $poSet->getMbo();
		$discountpercentage = $po->getString("discountpercentage");
		$exchangerate = $po->getString("exchangerate");
		
		$set = new Set();
		$polineSet = $set->MboSetRemote("poline")->setWhere("ponum='$ponum' and polinenum='$polinenum'")
		->MboRemote("conversionfactor")->MboRemote("unitcost")->MboRemote("linecost")->MboRemote("orderqty")
		->MboRemote("orderunit")->MboRemote("wonum")->getMboSet();
		
		
		$poline = $polineSet->getMbo();
		$conversionfactor = $poline->getString("conversionfactor");
		$unitcost =  $poline->getString("unitcost");
	
		$orderunit = $poline->getString("orderunit");
		$wonum = $poline->getString("wonum");
		$orderqty = $poline->getString("orderqty");
		if(empty($conversionfactor)) $conversionfactor = 1;
		if($conversionfactor<=0) $conversionfactor = 1;
		
		if($discountpercentage==null) $discountprice = 0;
		else {
			$discountprice = ($discountpercentage/100)*$unitcost;
		}
		
		$unitcost = $unitcost-$discountprice;
		$unitcost = $unitcost* $exchangerate;
		$linecost = $unitcost * $retQty;
		
		$set = new Set();
		if ($transtable == "matrectrans") {
			$transtableSet = $set->MboSetRemote("matrectrans")->setWhere("ponum='$ponum' and polinenum='$polinenum' and transtype='RECEIPT'")->MboRemote("ponum")->MboRemote("polinenum")
			->MboRemote("linetype")->MboRemote("itemnum")->MboRemote("description")->MboRemote("warehouse")->MboRemote("gldebitacct")->MboRemote("glcreditacct")->MboRemote("orgcode")
			->MboRemote("sitecode")->getMboSet("remove_duplicate")->getMbo();
		}
		if ($transtable == "servrectrans") {
			$transtableSet = $set->MboSetRemote("servrectrans")->setWhere("ponum='$ponum' and polinenum='$polinenum' and transtype='RECEIPT'")->MboRemote("ponum")->MboRemote("polinenum")
			->MboRemote("linetype")->MboRemote("description")->MboRemote("gldebitacct")->MboRemote("glcreditacct")->MboRemote("orgcode")->MboRemote("sitecode")
			->getMboSet("remove_duplicate")->getMbo();
		}
		
		$ponum = $transtableSet->getString("ponum");
		$polinenum = $transtableSet->getString("polinenum");
		$linetype = $transtableSet->getString("linetype");
		$description = $transtableSet->getString("description");
		$gldebitacct = $transtableSet->getString("gldebitacct");
		$glcreditacct = $transtableSet->getString("glcreditacct");
		$orgcode = $transtableSet->getString("orgcode");
		$sitecode = $transtableSet->getString("sitecode");
		
		if ($linetype == "item") {
			if ($retQty <= 0) {
				return "ReturnQtyIsLesserThanZero";
			}else{
				$itemnum = $transtableSet->getString("itemnum");
				
				$set = new Set();
				$oldCurbal = $set->MboSetRemote("invbalance")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' $condval $rackval")
				->MboRemote("curbal")->getMboSet()->getMbo()->getString("curbal");
				
				if ($oldCurbal < $retQty && $oldCurbal != 0) {
					return "ReturnQtyIsGreaterThanReceiptQty";
				}else{
					
					if ($oldCurbal >= $retQty) {
						$linecostConversion = $linecost / $conversionfactor;
						$transtype = "RETURN";
						$enterby = Xbo::getLoginUser();
						$enterdate = Xbo::setEnterDate();
						$tableid = $transtable . "id";
						$retQtyMinusConversion = $retQtyMinus / $conversionfactor;
						$retQtyConversion = $retQty / $conversionfactor;
						$unitcostConversion = $unitcost / $conversionfactor;
						$financialperiod = Xbo::getFinancialPeriod();
						$transtableid = Xbo::getMaxTableID($transtable);
						
						$issuetype = Xbo::InventoryCostType($itemnum, $warehouse, $orgcode);
						
						if($issuetype == "AVG"){
							$matrecs = $set->MboSetRemote("matrectrans")->setValue("ponum", $ponum)->setValue("polinenum", $polinenum)->setValue("linetype", $linetype)
							->setValue("itemnum", $itemnum)->setValue("description", $description)->setValue("warehouse", $warehouse)->setValue("quantity", $retQtyMinusConversion)
							->setValue("unitcost", $unitcost)->setValue("linecost", $linecostConversion * -1)->setValue("gldebitacct", $glcreditacct)->setValue("glcreditacct", $gldebitacct)
							->setValue("transtype", $transtype)->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)
							->setValue("financialperiod", $financialperiod)->setValue("conditiontype", $conditiontype)->setValue("actualdate", $actualdate)->setValue("remarks", $remarks);
							
							$sf = new Set();
							$fifotrans = $sf->MboSetRemote("matrectrans")->setValue("ponum", $ponum)->setValue("polinenum", $polinenum)->setValue("linetype", $linetype)
							->setValue("itemnum", $itemnum)->setValue("description", $description)->setValue("warehouse", $warehouse)->setValue("gldebitacct", $glcreditacct)
							->setValue("glcreditacct", $gldebitacct)->setValue("transtype", $transtype)->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)
							->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)->setValue("financialperiod", $financialperiod)->setValue("conditiontype", $conditiontype)
							->setValue("actualdate", $actualdate)->setValue("remarks", $remarks);
							
							$issuedqty = $retQtyMinusConversion*$conversionfactor;
							$fl = new stdClass();
							$fl->tablename = "matrectrans";
							$fl->transtype = $transtype;
							$fl->itemnum = $itemnum;
							$fl->warehouse = $warehouse;
							$fl->conditiontype = $conditiontype;
							$fl->racknum = $racknum;
							$fl->issuedqty = $issuedqty;
							$fl->opposite = $vendorcode;
							$fl->condval = $condval;
							$fl->rackval = $rackval;
							$fl->avgtrans = $matrecs;
							$fl->fifotrans = $fifotrans;
							$fl->unitcost = $unitcost;
							$fl->fifowhereclause = "itemnum='$itemnum' and warehouse='$warehouse' and vendorcode = '$vendorcode' and ponum='$ponum' $condval and quantity>0";
							$execfifo = XboSet::IssuedFIFOLIFO($fl);

							if($execfifo == "notenoughfifoqty"){
								return "notenoughfifoqty";
							}else{
								$set = new Set();
								$oldCurbal = $set->MboSetRemote("invbalance")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' $condval $rackval")->MboRemote("curbal")
								->getMboSet()->getMbo()->getString("curbal");
								$newCurbal = $oldCurbal - $retQty;
								
								$set->MboSetRemote("invbalance")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' $condval $rackval")->setValue("curbal", $newCurbal)
								->setValue("oldcurbal", $oldCurbal)->change();
								
								$oldCurbal = $set->MboSetRemote("invbalance")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' $condval $rackval")
								->MboRemote("curbal")->getMboSet()->getMbo()->getString("curbal");
							}
						}else{
							// when returning the item, maxes will invert the gl debit and gl credit
							// update matrectrans
							
							$set = new Set();
							$set->MboSetRemote("matrectrans")->setValue("ponum", $ponum)->setValue("polinenum", $polinenum)->setValue("linetype", $linetype)
							->setValue("itemnum", $itemnum)->setValue("description", $description)->setValue("warehouse", $warehouse)->setValue("quantity", $retQtyMinusConversion)
							->setValue("unitcost", $unitcost)->setValue("linecost", $linecostConversion * -1)->setValue("gldebitacct", $glcreditacct)->setValue("glcreditacct", $gldebitacct)
							->setValue("transtype", $transtype)->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)
							->setValue("financialperiod", $financialperiod)->setValue("conditiontype", $conditiontype)->setValue("actualdate", $actualdate)->setValue("remarks", $remarks)
							->setValue("batchnum", $batchnum)->setValue("costdate", $costdate)->setValue("issuetype", $issuetype)
							->add();
							
							//update inventory balance
							$set = new Set();
							$oldCurbal = $set->MboSetRemote("invbalance")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' $condval $rackval")->MboRemote("curbal")
							->getMboSet()->getMbo()->getString("curbal");
							$newCurbal = $oldCurbal - $retQty;
							
							$set = new Set();
							$set->MboSetRemote("invbalance")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' $condval $rackval")->setValue("curbal", $newCurbal)
							->setValue("oldcurbal", $oldCurbal)->change();
							
							//update inventory fifo lifo
							
							$set = new Set();
							$oldQuantity = $set->MboSetRemote("invfifolifocost")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' and batchnum='$batchnum' $condval $rackval ")
							->MboRemote("quantity")->getMboSet()->getMbo()->getString("quantity");
							
							$newQuantity = $oldQuantity-$retQty;
							
							$set = new Set();
							$set->MboSetRemote("invfifolifocost")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' and batchnum='$batchnum' $condval $rackval ")
							->setValue("quantity", $newQuantity)->change();
						}
				
						XboSet::AdjustConversionStock($itemnum, $warehouse);
					}else{
						if (($oldCurbal === null || $oldCurbal == 0)) {
							return "RackDoesNotHaveBalance";
						}
					}
				}
			}
		}
		if ($linetype == "services") {
			
			$spl = new Set();
			$servSet = $spl->MboSetRemote("servrectrans")->setWhere("ponum='$ponum' and polinenum='$polinenum'")
			->MboRemote("sum(quantity) as sumqty")->getMboSet();
			$serv = $servSet->getMbo();
			$sumqty = $serv->getString("sumqty");
			$sumqtyretqty = $sumqty+$retQty;
			
			if($sumqtyretqty>$orderqty || $retQty >$orderqty){
				return "ReturnQtyIsGreaterThanReceiptQty";
			}else{
				$linecostConversion = $linecost / $conversionfactor;
				$transtype = "RETURN";
				$enterby = Xbo::getLoginUser();
				$enterdate = Xbo::setEnterDate();
				$tableid = $transtable . "id";
				$retQtyMinusConversion = $retQtyMinus / $conversionfactor;
				$retQtyConversion = $retQty / $conversionfactor;
				$financialperiod = Xbo::getFinancialPeriod();
				if ($transtable == "servrectrans") {
					$gldebit = $glcreditacct;
					$glcredit = $gldebitacct;
					$set->MboSetRemote("servrectrans")->setValue("ponum", $ponum)->setValue("polinenum", $polinenum)->setValue("linetype", $linetype)
						->setValue("description", $description)->setValue("quantity", $retQtyMinusConversion)->setValue("unitcost", $unitcost * -1)
						->setValue("linecost", $linecostConversion * -1)->setValue("gldebitacct", $gldebit)->setValue("glcreditacct", $glcredit)->setValue("transtype", $transtype)
						->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)->setValue("financialperiod", $financialperiod)
						->setValue("wonum", $wonum)->setValue("actualdate", $actualdate)->setValue("remarks", $remarks)
						->add();
					Xbo::calculateWOCost($wonum);
				}
			}
		}
		if ($retQty <= 0) {
			return "ReturnQtyIsLesserThanZero";
		}
		CommonActions::setPOReceiptStatus($ponum, $polinenum, $linetype);
		if ($transtable == "matrectrans") {
			$retQtyFormat = number_format($retQty, 2);
			$issueunit = Xbo::getIssueUnit($itemnum);
			$activity = "mengembalikan <font color=blue><b>$retQtyConversion $issueunit</b></font> dari Item:<font color=blue><b>$itemnum - $description</b></font> to <font color=blue><b>$vendorname</b></font>";
			Xbo::setActivity($activity);
		}
		if ($transtable == "servrectrans") {
			$qtyFormat = number_format($retQty, 2);
			$activity = "mengembalikan <font color=blue><b>$retQty $orderunit</b></font> dari Jasa: <font color=blue><b>$description</b></font> to <font color=blue><b>$vendorname</b></font>";
			Xbo::setActivity($activity);
		}
	}
	public static function setPOServrecReceiptStatus($ponum, $polinenum)

	{
		$set = new Set();
		$sumservrec = $set->MboSetRemote("servrectrans")->setWhere("ponum='$ponum' and polinenum='$polinenum'")->MboRemote("sum(quantity) as sumqty")->getMboSet()->getMbo()->getString("sumqty");
		$orderqty = $set->MboSetRemote("poline")->setWhere("ponum='$ponum' and polinenum='$polinenum'")->MboRemote("orderqty")->getMboSet()->getMbo()->getString("orderqty");
		if ($sumservrec == 0 || $sumservrec === null) {
			$receiptstatus = 'NONE';
		}
		if ($sumservrec > 0 || ($sumservrec < $orderqty && $sumservrec > 0)) {
			$receiptstatus = 'PARTIAL';
		}
		if ($sumservrec == $orderqty) {
			$receiptstatus = 'COMPLETE';
		}
		$set->MboSetRemote("poline")->setWhere("ponum='$ponum' and polinenum='$polinenum'")->setValue("receiptstatus", $receiptstatus)->change();
	}
	public static function setPOMatrecReceiptStatus($ponum, $polinenum)

	{
		$set = new Set();
		$sumMatrec = $set->MboSetRemote("matrectrans")->setWhere("ponum='$ponum' and polinenum='$polinenum'")->MboRemote("sum(quantity) as sumqty")->getMboSet()->getMbo()->getString("sumqty");
		$orderqty = $set->MboSetRemote("poline")->setWhere("ponum='$ponum' and polinenum='$polinenum'")->MboRemote("orderqty")->getMboSet()->getMbo()->getString("orderqty");
		if ($sumMatrec == 0 || $sumMatrec === null) $receiptstatus = 'NONE';
		if ($sumMatrec > 0 || ($sumMatrec < $orderqty && $sumMatrec > 0)) $receiptstatus = 'PARTIAL';
		if ($sumMatrec == $orderqty) $receiptstatus = 'COMPLETE';
		$set->MboSetRemote("poline")->setWhere("ponum='$ponum' and polinenum='$polinenum'")->setValue("receiptstatus", $receiptstatus)->change();
	}
	public static function setPOReceiptStatus($ponum, $polinenum, $linetype)

	{
		$set = new Set();
		if ($linetype == "item") {
			CommonActions::setPOMatrecReceiptStatus($ponum, $polinenum);
		}
		if ($linetype == "services") {
			CommonActions::setPOServrecReceiptStatus($ponum, $polinenum);
		}
		$polineSet = $set->MboSetRemote("poline")->setWhere("ponum='$ponum'")->MboRemote("receiptstatus")->getMboSet("remove_duplicate");
		$numrows = $polineSet->counts();
		$poreceipt = 'NONE';
		if ($numrows > 1) $poreceipt = 'PARTIAL';
		if ($numrows < 1) $poreceipt = 'NONE';
		if ($numrows == 1) $poreceipt = $polineSet->getMbo()->getString("receiptstatus");
		$set->MboSetRemote("po")->setWhere("ponum='$ponum'")->setValue("receipt", $poreceipt)->change();
	}
	public static function ReceiptTransaction($value=null)

	{
		
		$table = $value["sourcetable"];
		$tableid = $table . "id";
		$recQty = $value["restqty"];
		$racknum = $value["racknum"];
		$remarks = $value["remarks"];
		$actualdate = $value["actualdate"];
		$warehouse = $value["warehouse"];
		$selectedlines = $value["selectedlines"];
		$batchnum = $value["batchnum"];
		
		$set = new Set();
		$tableSet = $set->MboSetRemote($table)->setRelationship("po", "poline.ponum = po.ponum")->setWhere("poline.$tableid='" . $selectedlines . "'")
		->MboRemote("poline.ponum")->MboRemote("poline.polinenum")->MboRemote("poline.linetype")->MboRemote("poline.itemnum")
		->MboRemote("poline.description")->MboRemote("poline.unitcost")->MboRemote("poline.linecost")->MboRemote("poline.orderqty")
		->MboRemote("poline.orderunit")->MboRemote("poline.gldebitacct")->MboRemote("poline.glcreditacct")->MboRemote("poline.warehouse")
		->MboRemote("poline.conversionfactor")->MboRemote("poline.wonum")->MboRemote("po.orderdate")->MboRemote("po.vendor")
		->MboRemote("po.exchangerate")->MboRemote("po.orgcode")->MboRemote("po.sitecode")->MboRemote("po.discountpercentage")->getMboSet()->getMbo();
		$ponum = $tableSet->getString("ponum");
		$itemnum = $tableSet->getString("itemnum");
		$polinenum = $tableSet->getString("polinenum");
		$linetype = $tableSet->getString("linetype");
		$description = $tableSet->getString("description");
		$conversionfactor = $tableSet->getString("conversionfactor");
		
		if($conversionfactor == 0) $conversionfactor = 1;
		if(empty($conversionfactor)){
			$conversionfactor = 1;
		}
		
		$orderdate = $tableSet->getString("orderdate");
		$vendor = $tableSet->getString("vendor");
		$orderunit = $tableSet->getString("orderunit");
		$exchangerate = $tableSet->getString("exchangerate");
		if($exchangerate == 0){
			 $exchangerate =1;
			 $se = new Set();
			 $se->MboSetRemote("po")->setWhere("ponum='$ponum'")->setValue("exchangerate", $exchangerate)->change();
		}
		
		$unitcost = $tableSet->getString("unitcost");
		$discountpercentage = $tableSet->getString("discountpercentage");
		if(empty($discountpercentage)) {
			$discountpercentage =0;
			$discountprice = 0;
		}else{
			$discountprice = $unitcost*$discountpercentage/100;
		}
		
		//$ucost = $tableSet->getString("unitcost");
		$ucost = $unitcost-$discountprice;
		$unitcost = $ucost * $exchangerate;
		
		$linecost = $unitcost * $recQty;
		$orderqty = $tableSet->getString("orderqty");
		$gldebitacct = $tableSet->getString("gldebitacct");
		$glcreditacct = $tableSet->getString("glcreditacct");
		$defaultwarehouse = $tableSet->getString("warehouse");
		if($warehouse == null ||$warehouse == ""){
			$warehouse = $defaultwarehouse;
		}
		$wonum = $tableSet->getString("wonum");
		$orgcode = $tableSet->getString("orgcode");
		$sitecode = $tableSet->getString("sitecode");
		
		$vendorname = $set->MboSetRemote("vendor")->setRelationship("po", "po.vendor = vendor.vendorcode")->setWhere("po.ponum='$ponum'")
		->MboRemote("vendor.name as vendorname")->getMboSet()->getMbo()->getString("vendorname");
		$recQtyConversion = $recQty * $conversionfactor;
		$unitcostConversion = $unitcost / $conversionfactor;
		$linecostConversion = $linecost;
		$transtype = 'RECEIPT';
		$enterby = Xbo::getLoginUser();
		$enterdate = Xbo::setEnterDate();
		$colnames = "";
		$transtable = "";
		$values = "";
		$receiptStatus = "";
		if ($linetype == "item") {
			$transtable = "matrectrans";
		}
		if ($linetype == "services") {
			$transtable = "servrectrans";
		}
		
		if(empty($actualdate)) $actualdate = $enterdate;
		
		$it = new Set();
		$itSet = $it->MboSetRemote("inventory")->setWhere("itemnum='$itemnum' and warehouse = '$warehouse'")->MboRemote("issuetype")->getMboSet();
		$its = $itSet->getMbo();
		$issuetype = $its->getString("issuetype");
		
		if(empty($issuetype)) {
			$set = new Set();
			$orgSet = $set->MboSetRemote("organization")->setWhere("orgcode='$orgcode'")->MboRemote("costtype")->getMboSet();
			$org = $orgSet->getMbo();
			$costtype = $org->getString("costtype");
			
			if(empty($costtype)) $costtype ="FIFO";
			$issuetype = $costtype;
		}
		
		
		
		$matrecqty = $set->MboSetRemote("matrectrans")->setWhere("ponum='$ponum' and polinenum='$polinenum'")->MboRemote("sum(quantity) as matrecqty")->getMboSet()->getMbo()->getString("matrecqty");
		$servrecqty = $set->MboSetRemote("servrectrans")->setWhere("ponum='$ponum' and polinenum='$polinenum'")->MboRemote("sum(quantity) as servrecqty")->getMboSet()->getMbo()->getString("servrecqty");
		if($matrecqty == null ) $matrecqty=0;
		$MatrecToReceiptQty = $matrecqty + $recQty;
		if($servrecqty == null )$servrecqty=0;
		$ServrecToReceiptQty = $servrecqty + $recQty;
		
		if ($transtable == "matrectrans") {
			if ($MatrecToReceiptQty <= $orderqty) {
				if ($recQty <= 0) {
					return "ReceiptQtyCannotLessThanZero";
				}
				else {
					$financialperiod = Xbo::getFinancialPeriod();
					$itemconditionSet = $set->MboSetRemote("itemcondition")->setRelationship("conditiontype", "itemcondition.conditiontype = conditiontype.conditiontype")
					->setWhere("itemcondition.itemnum='$itemnum' and itemcondition.conditionrate='100'")->MboRemote("itemcondition.conditiontype")->MboRemote("itemcondition.conditionrate")
					->MboRemote("conditiontype.conditionrate as origconditionrate")->getMboSet();
					
					$itemcondition = $itemconditionSet->getMbo();
				
					$conditiontype = $itemcondition->getString("conditiontype");
					$conditionrate = $itemcondition->getString("conditionrate");
					$origconditionrate = $itemcondition->getString("origconditionrate");
					$isItemExist = $set->MboSetRemote("inventory")->setWhere("itemnum='$itemnum' and warehouse='$warehouse'")
									->MboRemote("itemnum")->getMboSet()->getMbo()->getString("itemnum");
									
					$mataddSet = $set->MboSetRemote("matrectrans")->setValue("ponum", $ponum)->setValue("polinenum", $polinenum)->setValue("linetype", $linetype)->setValue("itemnum", $itemnum)
								->setValue("description", $description)->setValue("warehouse", $warehouse)->setValue("racknum", $racknum)->setValue("quantity", $recQty)
								->setValue("unitcost", $unitcost)->setValue("linecost", $linecostConversion)->setValue("gldebitacct", $gldebitacct)
								->setValue("glcreditacct", $glcreditacct)
								->setValue("transtype", $transtype)->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)
								->setValue("financialperiod", $financialperiod)->setValue("costdate", $actualdate)->setValue("issuetype", $issuetype)
								->setValue("actualdate", $actualdate)->setValue("remarks", $remarks)->setValue("batchnum", $batchnum)
								->setValue("discountprice", $discountprice)->setValue("discountpercentage", $discountpercentage);
						
					if($conditionrate == null){
						$mataddSet->add();
					}else{
						$mataddSet->setValue("conditiontype", $conditiontype)->add();
					}
					if (empty($isItemExist)) {
						$set->MboSetRemote("inventory")->setValue("itemnum", $itemnum)->setValue("warehouse", $warehouse)
							->setValue("racknum", $racknum)->setValue("reorderqty", 0)->setValue("leadtime", 0)->setValue("eoqqty", 1)
							->setValue("safetystock", 0)->setValue("reorder", "Y")->setValue("changeby", $enterby)->setValue("issuetype", $issuetype)
							->setValue("changedate", $enterdate)->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)->add();
					}
					
					
					$condval = Xbo::CondTypeWhereClause($conditiontype);
					$rackval = Xbo::RackWhereClause($racknum);
					
					$curbal = $set->MboSetRemote("invbalance")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' $rackval $condval")->MboRemote("curbal")->getMboSet()->getMbo()->getString("curbal");
					$oldcurbal = $curbal;
					if (empty($curbal)) {
						$curbal =0;
						$curbal = $curbal * $conversionfactor;
						if($conditionrate != null){
							$set->MboSetRemote("invbalance")->setValue("itemnum", $itemnum)->setValue("warehouse", $warehouse)
							->setValue("racknum", $racknum)->setValue("curbal", $recQtyConversion)->setValue("oldcurbal", $recQtyConversion)->setValue("enterby", $enterby)
							->setValue("enterdate", $enterdate)->setValue("changeby", $enterby)->setValue("changedate", $enterdate)->setValue("orgcode", $orgcode)
							->setValue("sitecode", $sitecode)->setValue("conditiontype", $conditiontype)->add();
						}else{
							$set->MboSetRemote("invbalance")->setValue("itemnum", $itemnum)->setValue("warehouse", $warehouse)
							->setValue("racknum", $racknum)->setValue("curbal", $recQtyConversion)->setValue("oldcurbal", $recQtyConversion)->setValue("enterby", $enterby)
							->setValue("enterdate", $enterdate)->setValue("changeby", $enterby)->setValue("changedate", $enterdate)->setValue("orgcode", $orgcode)
							->setValue("sitecode", $sitecode)->add();
						}
					}
					else {
						$oldcurbal = $curbal;
						$curbal = $curbal + $recQtyConversion;
						if($conditionrate != null){
							$set->MboSetRemote("invbalance")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' $rackval $condval")->setValue("curbal", $curbal)
							->setValue("oldcurbal", $oldcurbal)->setValue("changeby", $enterby)->setValue("changedate", $enterdate)->setValue("conditiontype", $conditiontype)
							->change();
						}else{
							$set->MboSetRemote("invbalance")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' $rackval $condval")->setValue("curbal", $curbal)
							->setValue("oldcurbal", $oldcurbal)->setValue("changeby", $enterby)->setValue("changedate", $enterdate)->change();	
						}
					}
					$setv = new Set();
					$vendorSet = $setv->MboSetRemote("invvendor")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' and vendorcode='$vendor'")->MboRemote("vendorcode")->getMboSet();
					$vendormbo = $vendorSet->getMbo();
					$vendorcode = $vendormbo->getString("vendorcode");
					
					/*get Internal Lead Time from PR REQUESTED DATE to PO SENT DATE
						if PR does not exist, then counting from PO NEW DATE
					*/
					//get INIT Date First, (int_initleadtime)
					$setpr = new Set();
					$prpolineSet = $setpr->MboSetRemote("poline")->setRelationship("pr", "pr.prnum = poline.prnum")->setWhere("poline.ponum='$ponum'")
					->MboRemote("poline.prnum")->MboRemote("pr.requesteddate")->getMboSet();
					$prpoline = $prpolineSet->getMbo();
					$init_intleadtime = $prpoline->getString("requesteddate");
					
					if(empty($init_intleadtime)){
						$setpr = new Set();
						$postatusSet = $setpr->MboSetRemote("postatus")->setWhere("ponum='$ponum' and status='new'")->MboRemote("changedate")->getMboSet();						
						$postatus = $postatusSet->getMbo();
						$init_intleadtime = $postatus->getString("changedate");
					}
					
					$int_initleadtime = strtotime($init_intleadtime);
					//get Next Date, (int_nextleadtime)
					$setpost = new Set();
					$postatusSet2 = $setpost->MboSetRemote("postatus")->setWhere("ponum='$ponum' and status='sent'")->MboRemote("changedate")->getMboSet();	
					$postatus2 = $postatusSet2->getMbo();
					$next_intleadtime = $postatus2->getString("changedate");
					$next_intleadtime = strtotime($next_intleadtime);
					
					$internalleadtime_range = $next_intleadtime-$int_initleadtime;
					$internalleadtime = round($internalleadtime_range / (60 * 60 * 24));
					
					if($internalleadtime <0) $internalleadtime= $internalleadtime*-1;
					
					/* get External Lead Time from PO SENT DATE to MATRECT.TRANSDATE
					*/
					
					$setmtr = new Set();
					$matrecSet = $setmtr->MboSetRemote("matrectrans")->setWhere("ponum='$ponum' and itemnum='$itemnum'")->MboRemote("enterdate")->getMboSet();
					
					$matrec = $matrecSet->getMbo();
					$next_extleadtime = $matrec->getString("enterdate");
					$next_extleadtime = strtotime($next_extleadtime);
					
					$ext_leadtime_range = $next_extleadtime-$next_intleadtime;
					$externalleadtime = round($ext_leadtime_range / (60 * 60 * 24));
					if($externalleadtime <0) $externalleadtime= $externalleadtime*-1;
					
					$totalleadtime = $externalleadtime+$internalleadtime;
					
					if ($vendorcode == null) {
						$set->MboSetRemote("invvendor")->setValue("itemnum", $itemnum)->setValue("warehouse", $warehouse)->setValue("lastorderdate", $orderdate)
						->setValue("lastprice", $unitcostConversion)->setValue("vendorcode", $vendor)->setValue("enterby", $enterby)->setValue("lastponum", $ponum)
						->setValue("enterdate", $enterdate)->setValue("changeby", $enterby)->setValue("changedate", $enterdate)->setValue("orgcode", $orgcode)
						->setValue("sitecode", $sitecode)->setValue("intleadtime", $internalleadtime)->setValue("extleadtime", $externalleadtime)->setValue("leadtime", $totalleadtime)->add();						
					}
					else {
						$set->MboSetRemote("invvendor")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' and orgcode='$orgcode' and sitecode='$sitecode'")->setValue("lastprice", $unitcostConversion)
						->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)->setValue("changeby", $enterby)->setValue("changedate", $enterdate)->setValue("extleadtime", $externalleadtime)
						->setValue("leadtime", $totalleadtime)->setValue("lastponum", $ponum)->setValue("intleadtime", $internalleadtime)->change();
					}
					
					$upd1 = new Set();
					$maxleadtimeSet = $upd1->MboSetRemote("invvendor")->setWhere("itemnum='$itemnum' and warehouse='$warehouse'")->MboRemote("max(leadtime) as maxleadtime")->getMboSet();
					
					$maxleadtime = $maxleadtimeSet->getMbo();
					$leadtime = $maxleadtime->getString("maxleadtime");
					
					$upd2 = new Set();
					$upd2->MboSetRemote("inventory")->setWhere("itemnum='$itemnum' and warehouse='$warehouse'")->setValue("leadtime",$leadtime )->change();
					
					
					//(Old Balance * Old Average Cost) + (Receipt Qty * Receipt Cost) / (Old Balance + Receipt Qty)
					$setic = new Set();
					$invcostSet = $setic->MboSetRemote("invcost")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' $condval")->MboRemote("avgcost")->getMboSet();
					$countInvCost = $invcostSet->counts();
					$invcost = $invcostSet->getMbo();
					$oldavgcost = $invcost->getString("avgcost");
					
					if ($oldavgcost == 0 || $oldavgcost==null) {
						$oldavgcost = 0;
						$newAvgCost = $unitcostConversion;
					}
					else {
						if($oldcurbal == null) $oldcurbal = 0;
						if($oldavgcost == null) $oldavgcost = 0;
						
						$newAvgCost = (($oldcurbal * $oldavgcost) + ($recQty * $unitcostConversion)) / ($oldcurbal + $recQty);
						Xbo::AdjustWTMaterialCost($itemnum, $warehouse, $newAvgCost);
					}
					
					$conditionrate = Xbo::getConditionRate($conditiontype);
					$glaccount = Xbo::getGLAccount($itemnum);					
					$shrinkageacc = Xbo::getShrinkageAcct($warehouse, $orgcode, $sitecode);					
					$invcostadjacc = Xbo::getInvCostAdjAcct($warehouse, $orgcode, $sitecode);		

					if($countInvCost == 0){
						if($conditionrate != null){
							$set->MboSetRemote("invcost")->setValue("itemnum", $itemnum)->setValue("warehouse", $warehouse)
							->setValue("conditionrate", $conditionrate)->setValue("stdcost", 0)->setValue("avgcost", $newAvgCost)->setValue("oldcost", $unitcostConversion)
							->setValue("lastcost", $unitcostConversion)->setValue("glaccount", $glaccount)->setValue("shrinkageacc", $shrinkageacc)->setValue("invcostadjacc", $invcostadjacc)
							->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)->setValue("conditiontype", $conditiontype)->add();
						}else{
							$set->MboSetRemote("invcost")->setValue("itemnum", $itemnum)->setValue("warehouse", $warehouse)
							->setValue("conditionrate", $conditionrate)->setValue("stdcost", 0)->setValue("avgcost", $newAvgCost)->setValue("oldcost", $unitcostConversion)
							->setValue("lastcost", $unitcostConversion)->setValue("glaccount", $glaccount)->setValue("shrinkageacc", $shrinkageacc)->setValue("invcostadjacc", $invcostadjacc)
							->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)->add();
						}
					}else{
						if($conditionrate != null){
							$set->MboSetRemote("invcost")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' $condval")
							->setValue("avgcost", $newAvgCost)->setValue("oldcost", $oldavgcost)->setValue("lastcost", $unitcostConversion)
							->setValue("glaccount", $glaccount)->setValue("shrinkageacc", $shrinkageacc)->setValue("invcostadjacc", $invcostadjacc)->setValue("conditiontype", $conditiontype)->change();
						}else{
							$set->MboSetRemote("invcost")->setWhere("itemnum='$itemnum' and warehouse='$warehouse' $condval")
							->setValue("avgcost", $newAvgCost)->setValue("oldcost", $oldavgcost)->setValue("lastcost", $unitcostConversion)
							->setValue("glaccount", $glaccount)->setValue("shrinkageacc", $shrinkageacc)->setValue("invcostadjacc", $invcostadjacc)->change();
						}
					}
					if (!empty($oldavgcost)) {			
						
					}
					else {	
						
					}
					$fl = new stdClass();
					$fl->itemnum = $itemnum;
					$fl->racknum = $racknum;
					$fl->warehouse = $warehouse;
					$fl->conditiontype = $conditiontype;
					$fl->vendorcode = $vendor;
					$fl->enterby = $enterby;
					$fl->costdate = $actualdate;
					$fl->financialperiod = $financialperiod;
					$fl->conditionrate = $conditionrate;
					$fl->orgcode = $orgcode;
					$fl->sitecode = $sitecode;
					$fl->unitcost = $unitcostConversion;
					$fl->quantity = $recQtyConversion;
					$fl->ponum = $ponum;
					$fl->polinenum = $polinenum;
					$fl->enterdate = $enterdate;
					$fl->actualdate = $actualdate;
					$fl->batchnum = $batchnum;
					CommonActions::ReceiptFIFOLIFO($fl);
					
					$recQtyFormat = number_format($recQty, 2);
					$activity = "menerima <font color=blue><b>$recQtyFormat $orderunit</b></font> dari Item:<font color=blue><b>$itemnum - $description</b></font> dari <font color=blue><b>$vendorname</b></font>";
					
					XboSet::AdjustConversionStock($itemnum, $warehouse);
					Xbo::setActivity($activity);
					CommonActions::setPOReceiptStatus($ponum, $polinenum, $linetype);
					
				}
			}
			if ($MatrecToReceiptQty > $orderqty) {
				
				return "ReceiptQtyIsGreaterThanOrderQty";				
			}
		}
		
		if ($transtable == "servrectrans") {
			
			if ($ServrecToReceiptQty <= $orderqty) {
				if ($recQty < 0) {
					return "ReceiptQtyCannotLessThanZero";
				}
				else {
					
					$linecost = $recQty * $unitcost;
					$financialperiod = Xbo::getFinancialPeriod();
					$set->MboSetRemote("servrectrans")->setValue("ponum", $ponum)->setValue("polinenum", $polinenum)
					->setValue("linetype", $linetype)->setValue("description", $description)->setValue("quantity", $recQty)
					->setValue("unitcost", $unitcost)->setValue("linecost", $linecost)->setValue("gldebitacct", $gldebitacct)
					->setValue("glcreditacct", $glcreditacct)->setValue("transtype", $transtype)->setValue("orgcode", $orgcode)
					->setValue("sitecode", $sitecode)->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)
					->setValue("financialperiod", $financialperiod)->setValue("wonum", $wonum)
					->setValue("remarks", $remarks)->setValue("actualdate", $actualdate)
					->setValue("discountpercentage", $discountpercentage)->setValue("discountprice", $discountprice)
					->add();
					$recQtyFormat = number_format($recQty, 2);
					$activity = "menerima <font color=blue><b>$recQty $orderunit</b></font> JASA <font color=blue><b>$description</b></font> dari <font color=blue><b>$vendorname</b></font>";
					Xbo::setActivity($activity);
					CommonActions::setPOReceiptStatus($ponum, $polinenum, $linetype);
					// calcuate service wo
					Xbo::calculateWOCost($wonum);
					// end
					
				}
			}
			else {
				
				return "ReceiptQtyIsGreaterThanOrderQty";
			}
			
		}
		/**/
	}
	
	public static function PORecTrans(){
		
		$transtype = $_POST["transtype"];
		$msg = null;
		$countlines = count($_POST['porectrans']);
		if($transtype == "poline"){
			for($i=0;$i<$countlines; $i++){
				$value = $_POST['porectrans'][$i];
				$process = CommonActions::ReceiptTransaction($value);
				if($process=="ReceiptQtyCannotLessThanZero"){
					$msg = "ReceiptQtyCannotLessThanZero";
					break;
				}
				if($process=="ReceiptQtyIsGreaterThanOrderQty"){
					$msg = "ReceiptQtyIsGreaterThanOrderQty";
					break;
				}
			}
			
			if($msg!=null){
				echo $msg;
			}
		}
		if($transtype == "rectrans" || $transtype == "matrectrans" || $transtype == "servrectrans"){
			
			for($i=0;$i<count($_POST['porectrans']); $i++){
				$value = $_POST['porectrans'][$i];
				$process = CommonActions::ReturnTransaction($value);
				if($process=="ReturnQtyIsLesserThanZero"){
					$msg = "ReturnQtyIsLesserThanZero";
					break;
				}else{
					if($process=="RackDoesNotHaveBalance"){
						$msg = "RackDoesNotHaveBalance";
						break;
					}else{
						if($process=="ReturnQtyIsGreaterThanReceiptQty"){
							$msg = "ReturnQtyIsGreaterThanReceiptQty";
							break;
						}else{
							if($process == "notenoughfifoqty"){
								$msg = "notenoughfifoqty";
								break;
							}
						}
					}
				}
			}
			if($msg!=null){
				echo $msg;
			}
		}
	}
}


?>