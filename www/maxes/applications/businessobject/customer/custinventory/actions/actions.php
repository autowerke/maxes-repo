<?php
require_once ("../../../eat.php");

Class ActionSet {
	public static function CustomerShipment(){
		$value = $_POST["custshipment"];
		$cnt = count($value);
		$newmsg = null;
		for($i=0; $i<$cnt; $i++){
			$get = ActionSet::ProcessCustomerShipment($value[$i]);
			$msg = $get->msg;
			$newmsg = $msg;
			if($newmsg == "minusqtytransfer") break;
			if($newmsg == "notenoughqty") break;
		}
		Set::throws(["msg"=>$newmsg]);
	}
	
	public static function CustomerTransferTransaction($custob){
		$tocustomercode = $custob->tocustomercode;
		$fromcustomercode = $custob->fromcustomercode;
		$itemnum = $custob->itemnum;
		$wonum = $custob->wonum;
		$costdate = $custob->costdate;
		$expdate = $custob->expdate;
		$conditiontype = $custob->conditiontype;
		$fromwarehouse = $custob->fromwarehouse;
		$fromracknum = $custob->fromracknum;
		$shipstatus = $custob->shipstatus;
		$custbalanceid = $custob->custbalanceid;
		$batchnum = $custob->batchnum;
		$quantity = $custob->transferqty;
		$actualdate = $custob->actualdate;
		
		$enterby = Xbo::getLoginUser();
		$changeby = $enterby;		
		$enterdate = Xbo::setEnterDate();
		$changedate = $enterdate;
		$financialperiod = Xbo::getFinancialPeriod();
		$custbalancepriceid = $custob->custbalanceid;
		$transferdate = $actualdate;
		$transtype = "TRANSFER";
		
		
		$set = new Set();
		$custbalancepriceSet = $set->MboSetRemote("custbalanceprice")->setWhere("custbalancepriceid='$custbalancepriceid'")->getMboSet();
		$cp = $custbalancepriceSet->getMbo();
		
		$unitcost = $cp->getString("unitcost");
		$linecost = $quantity * $unitcost;
		
		$unitprice = $cp->getString("unitprice");
		$lineprice = $quantity * $unitprice;
		
		$unitprofit = $cp->getString("unitprofit");
		$lineprofit = $quantity * $unitprofit;
		
		$unitpricediscount = $cp->getString("unitpricediscount");
		$linepricediscount = $quantity * $unitpricediscount;
		
		$unitprofitdiscount = $cp->getString("unitprofitdiscount");
		$lineprofitdiscount = $quantity * $unitprofitdiscount;
		
		$unitcommission = $cp->getString("unitcommission");
		$linecommission = $quantity * $unitcommission;
		
		
		$issuetype =  $cp->getString("issuetype");
		$glcreditacct = Xbo::getCustomerAcct($tocustomercode);
		$gldebitacct = Xbo::getCustomerAcct($fromcustomercode);
		
		$set2 = new Set();
		$set2->MboSetRemote("custinvtrans")
		->setValue("itemnum", $itemnum)->setValue("customercode", $fromcustomercode)->setValue("tocustomercode", $tocustomercode)->setValue("quantity", $quantity)
		->setValue("wonum", $wonum)->setValue("batchnum", $batchnum)->setValue("expdate", $expdate)->setValue("unitcost", $unitcost)->setValue("linecost", $linecost)
		->setValue("unitprice", $unitprice)->setValue("lineprice", $lineprice)->setValue("unitprofit", $unitprofit)->setValue("lineprofit", $lineprofit)
		->setValue("unitpricediscount", $unitpricediscount)->setValue("linepricediscount", $linepricediscount)->setValue("unitprofitdiscount", $unitprofitdiscount)->setValue("lineprofitdiscount", $lineprofitdiscount)
		->setValue("costdate", $costdate)->setValue("transferdate", $transferdate)->setValue("issuetype", $issuetype)->setValue("gldebitacct", $gldebitacct)
		->setValue("glcreditacct", $glcreditacct)->setValue("financialperiod", $financialperiod)->setValue("conditiontype", $conditiontype)->setValue("transtype", $transtype)
		->setValue("unitcommission", $unitcommission)->setValue("linecommission", $linecommission)
		->setValue("shipstatus", $shipstatus)
		->add();
	}
	
	public static function ProcessCustomerShipment($value){
		$retval = new stdClass();
		$custob = new stdClass();
		if(isset($custob->shipstatus)) $custob->shipstatus = $value["shipstatus"];
		else $custob->shipstatus = "DELIVERED";
		
		$custob->transferqty = $value["receivedqty"];
		$custshipmentid = $value["custshipmentid"];
		$custob->actualdate = $value["actualdate"];
		$custob->custshipmentid = $custshipmentid;
		
		$set = new Set();
		$custshipmentSet = $set->MboSetRemote("custshipment")->setWhere("custshipmentid='$custshipmentid'")
		->MboRemote("keyid")->MboRemote("quantity")->MboRemote("tocustomercode")->MboRemote("customercode")->getMboSet();
		$custshipment = $custshipmentSet->getMbo();
		
		$customercode = $custshipment->getString("customercode");
		$custob->tocustomercode = $custshipment->getString("tocustomercode");
		$keyid = $custshipment->getString("keyid");
		$oldshipqty = $custshipment->getString("quantity");
		
		if($custob->transferqty <= $oldshipqty){
			if($custob->transferqty > 0){
				$custob->keyid = $keyid;
				$custob->custbalanceid = $keyid;
				$set2 = new Set();
				$custbalanceSet = $set2->MboSetRemote("custbalance")->setWhere("custbalanceid='".$custob->custbalanceid."'")->getMboSet();
				$custbalance = $custbalanceSet->getMbo();
				
				$custob->fromcustomercode = $custbalance->getString("customercode");
				$custob->shippedcustomercode = $custbalance->getString("tocustomercode");
				$custob->itemnum = $custbalance->getString("itemnum");
				$custob->expdate = $custbalance->getString("expdate");
				$custob->costdate = $custbalance->getString("costdate");
				$custob->conditiontype = $custbalance->getString("conditiontype");
				$custob->fromwarehouse = $custbalance->getString("fromwarehouse");
				$custob->fromracknum = $custbalance->getString("fromracknum");
				$custob->batchnum = $custbalance->getString("batchnum");
				$custob->curbal = $custbalance->getString("curbal");
				$custob->wonum = $custbalance->getString("wonum");
				$custob->costtype = $custbalance->getString("costtype");
				
				
				if($custob->itemnum == null){
					$setmat = new Set();
					$matusetransSet = $setmat->MboSetRemote("matusetrans")->setWhere("matusetransid='".$keyid."'")->getMboSet();
					$matusetrans = $matusetransSet->getMbo();
					$custob->fromcustomercode = $customercode;
					$custob->shippedcustomercode = $custob->tocustomercode;
					$custob->itemnum = $matusetrans->getString("itemnum");
					$custob->expdate = $matusetrans->getString("expdate");
					$custob->costdate = $matusetrans->getString("costdate");
					$custob->conditiontype = $matusetrans->getString("conditiontype");
					$custob->fromwarehouse = $matusetrans->getString("warehouse");
					$custob->fromracknum = $matusetrans->getString("racknum");
					$custob->batchnum = $matusetrans->getString("batchnum");
					$custob->wonum = $matusetrans->getString("wonum");
					
					$setcus = new Set();
					$checkCustBalanceSet = $setcus->MboSetRemote("custbalance")
					->setWhere("wonum='".$custob->wonum ."' and itemnum='".$custob->itemnum."' and customercode='$customercode'")
					->MboRemote("curbal")->getMboSet();
					$checkCustBalance = $checkCustBalanceSet->getMbo();
					
					$custob->curbal = $checkCustBalance->getString("curbal");
					$custob->costtype = $matusetrans->getString("issuetype");
				}
				
				$costdate = Super::whereDate("costdate", $custob->costdate);
				$expdate = Super::whereDate("expdate", $custob->expdate);
				
				if(empty($custob->conditiontype)) $condval = " and (conditiontype is null or conditiontype='')";
				else $condval = " and conditiontype='".$custob->conditiontype."' ";
				
				if(empty($custob->fromracknum)) $rackval = " and (fromracknum is null or fromracknum='')";
				else $rackval = " and fromracknum='".$custob->fromracknum."' ";
				
				$custob->fromwhereclause = "customercode = '".$custob->fromcustomercode."' and itemnum = '".$custob->itemnum."' and fromwarehouse='".$custob->fromwarehouse."' and wonum='".$custob->wonum."' $condval $rackval $costdate $expdate";
				$custob->towhereclause = "customercode = '".$custob->tocustomercode."' and itemnum = '".$custob->itemnum."' and fromwarehouse='".$custob->fromwarehouse."' and wonum='".$custob->wonum."' $condval $rackval $costdate $expdate";
				
				$set = new Set();
				$custbalanceSet = $set->MboSetRemote("custbalance")->setWhere($custob->fromwhereclause)->MboRemote("curbal")->getMboSet();
				
				$custbalance = $custbalanceSet->getMbo();
				$custob->oldqty = $custbalance->getString("curbal");	
				$custob->newqty = $custob->oldqty - $custob->transferqty;
				
				ActionSet::InsertOrUpdateCustBalance($custob);
				$retval->msg = "transfered";
				
				
			}else{
				$retval->msg = "minusqtytransfer";
			}
		}else{
			$retval->msg = "notenoughqty";
		}
		
		return $retval;
	}
	public static function InsertCustBalance($custob){
		$tocustomercode = $custob->tocustomercode;
		$fromcustomercode = $custob->fromcustomercode;
		$itemnum = $custob->itemnum;
		$wonum = $custob->wonum;
		$costdate = $custob->costdate;
		$expdate = $custob->expdate;
		$conditiontype = $custob->conditiontype;
		$fromwarehouse = $custob->fromwarehouse;
		$fromracknum = $custob->fromracknum;
		$shipstatus = $custob->shipstatus;
		$custbalanceid = $custob->custbalanceid;
		$batchnum = $custob->batchnum;
		$issuedqty = 0;
		$newqty = $custob->newqty;
		$transferqty = $custob->transferqty;
		$custshipmentid = $custob->custshipmentid ;
		$costtype = $custob->costtype;
		$droppedqty = $transferqty;
		
		$enterby = Xbo::getLoginUser();
		$changeby = $enterby;		
		$enterdate = Xbo::setEnterDate();
		$changedate = $enterdate;
		$financialperiod = Xbo::getFinancialPeriod();
		//$arriveddate = $enterdate;
		$arriveddate = $custob->actualdate;
		$isinvoiced = "N";
		
		$set = new Set();
		$set->MboSetRemote("custbalance")->setValue("customercode", $tocustomercode)->setValue("wonum", $wonum)->setValue("itemnum", $itemnum)->setValue("expdate", $expdate)->setValue("costdate", $costdate)
		->setValue("enterdate", $enterdate)->setValue("enterby", $enterby)->setValue("changedate", $changedate)->setValue("changeby", $changeby)->setValue("droppedqty", $droppedqty)->setValue("issuedqty", $issuedqty)
		->setValue("curbal", $transferqty)->setValue("isinvoiced", $isinvoiced)->setValue("arriveddate", $arriveddate)->setValue("conditiontype", $conditiontype)->setValue("fromwarehouse", $fromwarehouse)
		->setValue("fromracknum", $fromracknum)->setValue("shipstatus", null)->setValue("batchnum", $batchnum)->setValue("costtype", $costtype)
		->add();
		
		ActionSet::UpdateCustShipment($custob);
		
		if(isset($custob->keyid)){
		}else{
			$set2 = new Set();
			$set2->MboSetRemote("custbalance")->setWhere($custob->fromwhereclause)->setValue("curbal", $newqty)->setValue("droppedqty", $newqty)->setValue("issuedqty", $issuedqty)->change();
		}
		
	}
	public static function UpdateCustBalance($custob){
		$set = new Set();
		$issuedqty = 0;
		
		if(isset($custob->keyid)){
		}else{
			if(empty($custob->newqty)){
				$transferqty = $custob->transferqty;
				$oldqty = $custob->curbal;
				$custob->newqty = $oldqty-$transferqty;
				$set->MboSetRemote("custbalance")->setWhere($custob->fromwhereclause)->setValue("curbal", $custob->newqty)->setValue("droppedqty", $custob->newqty)->setValue("issuedqty", $issuedqty)
				->change();
			}else{
				$set->MboSetRemote("custbalance")->setWhere($custob->fromwhereclause)->setValue("curbal", $custob->newqty)->setValue("droppedqty", $custob->newqty)->setValue("issuedqty", $issuedqty)
				->change();
			}
		}
		$shipstatus = $custob->shipstatus;
		if($shipstatus == "DELIVERED"){
			$set2 =  new Set();
			$set->MboSetRemote("custbalance")->setWhere($custob->towhereclause)->setValue("curbal", $custob->tonewqty)->setValue("droppedqty", $custob->tonewqty)->setValue("issuedqty", $issuedqty)
			->setValue("shipstatus", null)->setValue("tocustomercode", null)
			->change();
			
			ActionSet::UpdateCustShipment($custob);
		}
	}
	public static function InsertOrUpdateCustBalance($custob){
		
		$set = new Set();
		$custbalanceSet = $set->MboSetRemote("custbalance")->setWhere($custob->fromwhereclause)->MboRemote("curbal")->getMboSet();
		$custbalance = $custbalanceSet->getMbo();
		$custob->oldqty = $custbalance->getString("curbal");	
		$custob->newqty = $custob->oldqty - $custob->transferqty;
		
		$set = new Set();
		$custbalanceSet = $set->MboSetRemote("custbalance")->setWhere($custob->towhereclause)->MboRemote("curbal")->getMboSet();
		$cnt = $custbalanceSet->counts();
		
		if($cnt == 0){
			ActionSet::InsertCustBalance($custob);
		}else{
			$custbalance = $custbalanceSet->getMbo();
			$custob->tooldqty = $custbalance->getString("curbal");
			$custob->tonewqty = $custob->tooldqty + $custob->transferqty;
			ActionSet::UpdateCustBalance($custob);
		}
		ActionSet::CustomerTransferTransaction($custob);
	}
	public static function UpdateCustShipment($custob){
		$transferqty = $custob->transferqty;
		
		if(isset($custob->custshipmentid)) $custshipmentid = $custob->custshipmentid;
		else $custob->custshipmentid = null;
		
		$set = new Set();
		$wc = "custshipmentid='$custshipmentid'";
		$custshipmentSet = $set->MboSetRemote("custshipment")->setWhere($wc)->getMboSet();
		$cship = $custshipmentSet->getMbo();
		$oldqty = $cship->getString("quantity");
		$newqty = $oldqty-$transferqty;
		$set2 = new Set();
		
		$status = "SHIPPED";
		if($newqty == 0) $status = "DELIVERED";
		
		if(isset($custob->shipstatus)){
			if($custob->shipstatus == "DELIVERED"){
				$set2->MboSetRemote("custshipment")->setWhere($wc)->setValue("quantity", $newqty)->setValue("status", $status)->setValue("receiptdate", $custob->actualdate)->change();
			}
		}else{
			$set2->MboSetRemote("custshipment")->setWhere($wc)->setValue("quantity", $newqty)->setValue("status", $status)->change();
		}
		
	}
	public static function InsertCustShipment($custob){
		
		$wonum = $custob->wonum;
		$itemnum = $custob->itemnum;
		$customercode = $custob->fromcustomercode;
		$tocustomercode = $custob->tocustomercode;
		$shipdate = $custob->actualdate;
		$keyid = $custob->custbalanceid;
		$quantity = $custob->transferqty;
		$status = $custob->shipstatus;
		$statusdate = Xbo::setEnterDate();
		
		$set = new Set();
		$sss = $set->MboSetRemote("custshipment");
		$sss->setValue("wonum", $wonum)->setValue("itemnum", $itemnum)->setValue("customercode", $customercode)->setValue("tocustomercode", $tocustomercode)
		->setValue("keyid", $keyid)->setValue("quantity", $quantity)->setValue("status", $status)->setValue("statusdate", $statusdate)->setValue("shipdate", $shipdate)
		->add();
		
		$set = new Set();
		$set->MboSetRemote("custbalance")->setWhere("custbalanceid='".$custob->custbalanceid."'")->setValue("shipstatus", $status)->change();
		
	}
	public static function ValidateCustomerShipment($custob){
		$msg = "processing";
		$set = new Set();
		$tocustomercode = $custob->tocustomercode;
		$fromcustomercode = $custob->fromcustomercode;
		$itemnum = $custob->itemnum;
		$wonum = $custob->wonum;
		$costdate = $custob->costdate;
		$expdate = $custob->expdate;
		$conditiontype = $custob->conditiontype;
		$fromwarehouse = $custob->fromwarehouse;
		$fromracknum = $custob->fromracknum;
		$shipstatus = $custob->shipstatus;
		$custbalanceid = $custob->custbalanceid;
		$shippedcustomercode = $custob->shippedcustomercode;
		$costdate = Super::whereDate("costdate", $costdate);
		$expdate = Super::whereDate("expdate", $expdate);
		$actualdate  = $custob->actualdate;
		if(empty($conditiontype)) $condval = " and (conditiontype is null or conditiontype='')";
		else $condval = " and conditiontype='$conditiontype' ";
		
		if(empty($fromracknum)) $rackval = " and (fromracknum is null or fromracknum='')";
		else $rackval = " and fromracknum='$fromracknum' ";
		
		$custob->towhereclause = "customercode = '$tocustomercode' and itemnum = '$itemnum' and fromwarehouse='$fromwarehouse' and wonum='$wonum' $condval $rackval $costdate $expdate";
		$custob->fromwhereclause = "customercode = '$fromcustomercode' and itemnum = '$itemnum' and fromwarehouse='$fromwarehouse' and wonum='$wonum' $condval $rackval $costdate $expdate";
		
		if($shipstatus == "DELIVERED"){
			if(!empty($shippedcustomercode)){
				if($shippedcustomercode != $tocustomercode){
					$msg = "differentshipcustomer";
					return $msg;
				}else{
					ActionSet::InsertOrUpdateCustBalance($custob);
				}
			}else{
				ActionSet::InsertOrUpdateCustBalance($custob);
			}
		}else{
			if(empty($shipstatus)){
				$msg = "emptyshipstatus";
				return $msg;
			}else{
				if($shipstatus == "SHIPPED"){
					if(!empty($shippedcustomercode)){
						if($shippedcustomercode != $tocustomercode){
							$msg = "differentshipcustomer";
							return $msg;
						}else{
							
						}
					}
					ActionSet::CustomerTransferTransaction($custob);
					
					$set = new Set();
					$custbalanceSet = $set->MboSetRemote("custbalance")->setWhere($custob->towhereclause)->MboRemote("curbal")->getMboSet();
					
					$cnt = $custbalanceSet->counts();
					$custbalance = $custbalanceSet->getMbo();
					$custob->tooldqty = $custbalance->getString("curbal");
					$custob->tonewqty = $custob->tooldqty + $custob->transferqty;
					ActionSet::UpdateCustBalance($custob);
					ActionSet::InsertCustShipment($custob);
				}else{
					//another status except DELIVERED;
				}
			}
		}
		
		$set = new Set();
		$custbalanceSet = $set->MboSetRemote("custbalance")->setWhere("custbalanceid='$custbalanceid'")->setValue("shipstatus", $shipstatus)
		->setValue("tocustomercode", $tocustomercode)->change();
		
	}
	public static function ProcessCustomerTransferItems($value){
		$retval = new stdClass();
		$custob = new stdClass();
		
		$custob->transferqty = $value["transferqty"];
		$custob->tocustomercode = $value["customercode"];
		$custob->shipstatus = $value["shipstatus"];
		$custob->custbalanceid = $value["custbalanceid"];
		$custob->actualdate = $value["actualdate"];
		
		$set = new Set();
		$custbalanceSet = $set->MboSetRemote("custbalance")->setWhere("custbalanceid='".$custob->custbalanceid."'")->getMboSet();
		$custbalance = $custbalanceSet->getMbo();
		
		$custob->fromcustomercode = $custbalance->getString("customercode");
		$custob->shippedcustomercode = $custbalance->getString("tocustomercode");
		$custob->itemnum = $custbalance->getString("itemnum");
		$custob->expdate = $custbalance->getString("expdate");
		$custob->costdate = $custbalance->getString("costdate");
		$custob->conditiontype = $custbalance->getString("conditiontype");
		$custob->fromwarehouse = $custbalance->getString("fromwarehouse");
		$custob->fromracknum = $custbalance->getString("fromracknum");
		$custob->batchnum = $custbalance->getString("batchnum");
		$custob->curbal = $custbalance->getString("curbal");
		$custob->wonum = $custbalance->getString("wonum");
		$custob->costtype = $custbalance->getString("costtype");		
		if($custob->shipstatus == "PACKAGED"){
			$set = new Set();
			$set->MboSetRemote("custbalance")->setWhere("custbalanceid = '".$custob->custbalanceid."'")->setValue("shipstatus", $custob->shipstatus)->change();
			$msg = "changestatus";
		}else{
			if($custob->shipstatus == "RETURNED"){
				$msg = "returned";
			}else{
				if($custob->shipstatus == "DELIVERED"){
					$msg = "shipfirst";
				}else{
					if($custob->shipstatus == "SHIPPED"){
						if($custob->transferqty <= 0){
							$msg = "qtynotallowed";
						}else{
							if($custob->fromcustomercode == $custob->tocustomercode){
								$msg = "samecustomercode";
							}else{
								
								$costdate = Super::whereDate("costdate", $custob->costdate);
								$expdate = Super::whereDate("expdate", $custob->expdate);
								
								if(empty($custob->conditiontype)) $condval = " and (conditiontype is null or conditiontype='')";
								else $condval = " and conditiontype='".$custob->conditiontype."' ";
								
								if(empty($custob->fromracknum)) $rackval = " and (fromracknum is null or fromracknum='')";
								else $rackval = " and fromracknum='".$custob->fromracknum."' ";
								$custob->fromwhereclause = "customercode = '".$custob->fromcustomercode."' and itemnum = '".$custob->itemnum."' and fromwarehouse='".$custob->fromwarehouse."' and wonum='".$custob->wonum."' $condval $rackval $costdate $expdate";
								$wonum = $custob->wonum;
								$itemnum = $custob->itemnum;
								$customercode = $custob->fromcustomercode;
								$tocustomercode = $custob->tocustomercode;
								$keyid = $custob->custbalanceid;
								$status = $custob->shipstatus;
								$statusdate = Xbo::setEnterDate();
								ActionSet::InsertCustShipment($custob);
								ActionSet::UpdateCustBalance($custob);
								$msg = "shipped";
							}
						}
					}else{
						
						if($custob->fromcustomercode == $custob->tocustomercode){
							$msg= "samecustomercode";
						}else{
							if($custob->transferqty <= $custob->curbal){
								if($custob->transferqty < 0){
									$msg = "minusqtytransfer";
								}else{
									$msg = ActionSet::ValidateCustomerShipment($custob);
								}
							}else{
								$msg = "notenoughqty";
							}
						}
					}
				}
						
				
			}
			
		}
		
		$retval->msg = $msg;
		return $retval;	
	}
	
	public static function CustomerTransferItems(){
		$value = $_POST["transferitems"];
		$cnt = count($value);
		$newmsg = null;
		for($i=0; $i<$cnt; $i++){
			$get = ActionSet::ProcessCustomerTransferItems($value[$i]);
			$msg = $get->msg;
			if($msg == "samecustomercode"){
				$newmsg = $msg;
				break;
			}else{
				if($msg == "notenoughqty"){
					$newmsg = $msg;
					break;
				}else{
					if($msg == "emptyshipstatus"){
						$newmsg = $msg;
						break;
					}else{
						if($msg == "minusqtytransfer"){
							$newmsg = $msg;
							break;
						}else{
							if($msg == "differentshipcustomer"){
								$newmsg = $msg;
								break;
							}else{
								if($msg == "changestatus"){
									$newmsg = $msg;
									break;
								}else{
									if($msg == "shipped"){
										$newmsg = "shipped";
										break;
									}else{
										if($msg == "qtynotallowed"){
											$newmsg = "qtynotallowed";
											break;
										}else{
											if($msg == "samecustomercode"){
												$newmsg = "msg";
												break;
											}else{
												if($msg == "shipfirst"){
													$newmsg = "shipfirst";
													break;
												}else{
													$newmsg = "transfered";
													break;
												}
											}
											
										}
									}
									
								}
								
							}
						}
					}
				}
			}
		}
		Set::throws(["msg"=>$newmsg]);
	}
	
	public static function ProcessCustomerQuantityAdjustment($value){
		$retval = new stdClass();
		
		$newqty = $value["newqty"];
		$custbalancepriceid = $value["custbalancepriceid"];
		
		$set = new Set();
		$custbalancepriceSet = $set->MboSetRemote("custbalanceprice")->setWhere("custbalancepriceid='$custbalancepriceid'")->getMboSet();
		$cp = $custbalancepriceSet->getMbo();
		
		$itemnum = $cp->getString("itemnum");
		$customercode = $cp->getString("customercode");
								
		$oldqty = $cp->getString("curbal");
		$quantity = $newqty;
		$wonum = $cp->getString("wonum");
		$batchnum = $cp->getString("batchnum");
		$expdate = $cp->getString("expdate");
		
		$unitcost = $cp->getString("unitcost");
		if($unitcost == null) $unitcost =0;
		$linecost = $quantity * $unitcost;
		$oldlinecost = $oldqty * $unitcost;
		
		$unitprice = $cp->getString("unitprice");
		if($unitprice == null) $unitprice =0;
		$lineprice = $quantity * $unitprice;
		$oldlineprice = $oldqty * $lineprice;
		$oldunitprice = $unitprice;
		
		$unitprofit = $cp->getString("unitprofit");
		if($unitprofit == null) $unitprofit =0;
		$lineprofit = $quantity * $unitprofit;
		$oldlineprofit = $oldqty * $unitprofit;
		$oldunitprofit = $unitprofit;
		
		$unitpricediscount = $cp->getString("unitpricediscount");
		if($unitpricediscount == null) $unitpricediscount =0;
		$linepricediscount = $quantity * $unitpricediscount;
		$oldlinepricediscount = $oldqty * $unitpricediscount;
		$oldunitpricediscount = $unitpricediscount;
		
		$unitprofitdiscount = $cp->getString("unitprofitdiscount");
		if($unitprofitdiscount == null) $unitprofitdiscount =0;
		$lineprofitdiscount = $quantity * $unitprofitdiscount;
		$oldlineprofitdiscount = $oldqty * $unitprofitdiscount;
		$oldunitprofitdiscount = $unitprofitdiscount;
		
		$unitcommission = $cp->getString("unitcommission");
		if($unitcommission == null) $unitcommission =0;
		$linecommission = $quantity * $unitcommission;
		$oldlinecommission = $oldqty * $unitcommission;
		$oldunitcommission = $unitcommission;
		
		$varianceqty = $quantity-$oldqty;
		$variancecost = $linecost-$oldlinecost;
		$varianceprice = $lineprice-$oldlineprice;
		$variancepricediscount = $linepricediscount - $oldlinepricediscount;
		$varianceprofit = $lineprofit - $oldlineprofit;
		$varianceprofitdiscount = $lineprofitdiscount - $oldlineprofitdiscount;
		
		$costdate = $cp->getString("costdate");
		
		$ss = new Set();
		$itemSet = $ss->MboSetRemote("item")->setWhere("itemnum='$itemnum'")->MboRemote("orgcode")->MboRemote("sitecode")->getMboSet();
		$item = $itemSet->getMbo();
		$orgcode = $item->getString("orgcode");
		$sitecode = $item->getString("sitecode");
		
		$st = new Set();
		$whSet = $st->MboSetRemote("custbalance")->setWhere("wonum='$wonum' and itemnum='$itemnum'")->MboRemote("fromwarehouse")->MboRemote("fromracknum")->getMboSet();
		$wh = $whSet->getMbo();
		
		$fromwarehouse = $wh->getString("fromwarehouse");
		$fromracknum = $wh->getString("fromracknum");
		
		if($oldqty >= $quantity){
			$gldebitacct = Xbo::getControlAcct($fromwarehouse, $orgcode, $sitecode);
			$glcreditacct =  Xbo::getwogl($wonum);
			
			//nambah piutang
			//ngurangi persediaan
		}else{
			$gldebitacct = Xbo::getwogl($wonum); 
			$glcreditacct =   Xbo::getControlAcct($fromwarehouse, $orgcode, $sitecode);
			
			//ngurangi piutang
			//nambah persediaan
		}
		
		$financialperiod = Xbo::getFinancialPeriod();
		$conditiontype = $cp->getString("conditiontype");
		$transtype = "CURBALADJ";
		
		$set3 = new Set();
		$set3->MboSetRemote("custinvtrans")
		->setValue("itemnum", $itemnum)->setValue("customercode", $customercode)->setValue("oldqty", $oldqty)->setValue("quantity", $quantity)->setValue("wonum", $wonum)
		->setValue("batchnum", $batchnum)->setValue("expdate", $expdate)->setValue("unitcost", $unitcost)->setValue("linecost", $linecost)->setValue("oldlinecost", $oldlinecost)
		->setValue("unitprice", $unitprice)->setValue("lineprice", $lineprice)->setValue("oldlineprice", $oldlineprice)->setValue("oldunitprice", $oldunitprice)->setValue("unitprofit", $unitprofit)
		->setValue("lineprofit", $lineprofit)->setValue("oldlineprofit", $oldlineprofit)->setValue("oldunitprofit", $oldunitprofit)->setValue("unitpricediscount", $unitpricediscount)->setValue("linepricediscount", $linepricediscount)
		->setValue("oldlinepricediscount", $oldlinepricediscount)->setValue("oldunitpricediscount", $oldunitpricediscount)->setValue("unitprofitdiscount", $unitprofitdiscount)->setValue("lineprofitdiscount", $lineprofitdiscount)->setValue("oldlineprofitdiscount", $oldlineprofitdiscount)
		->setValue("oldunitprofitdiscount", $oldunitprofitdiscount)->setValue("varianceqty", $varianceqty)->setValue("variancecost", $variancecost)->setValue("varianceprice", $varianceprice)->setValue("variancepricediscount", $variancepricediscount)
		->setValue("varianceprofit", $varianceprofit)->setValue("varianceprofitdiscount", $varianceprofitdiscount)
		->setValue("costdate", $costdate)->setValue("gldebitacct", $gldebitacct)->setValue("glcreditacct", $glcreditacct)->setValue("financialperiod", $financialperiod)->setValue("conditiontype", $conditiontype)
		->setValue("transtype", $transtype)->setValue("fromwarehouse", $fromwarehouse)->setValue("fromracknum", $fromracknum)
		->setValue("unitcommission", $unitcommission)->setValue("linecommission", $linecommission)
		->setValue("oldunitcommission", $oldunitcommission)->setValue("oldlinecommission", $oldlinecommission)
		->add();
		
		$set4 = new Set();
		$custbalanceSet = $set4->MboSetRemote("custbalance")->setWhere("custbalanceid='$custbalancepriceid'")->setValue("curbal", $newqty)->change();
		$retval->msg = "adjusted";
		return $retval;
	}
	
	public static function CustomerQuantityAdjustment(){
		$value = $_POST["curbaladjust"];
		$cnt = count($value);
		$newmsg = null;
		for($i=0; $i<$cnt; $i++){
			$get = ActionSet::ProcessCustomerQuantityAdjustment($value[$i]);
			$msg = $get->msg;
			if($msg == "notadjusted"){
				$newmsg = $msg;
				break;
			}else{
				$newmsg = "adjusted";
			}
		}
		Set::throws(["msg"=>$newmsg]);
	}
	
	public static function ProcessCustomerCostAdjustment($value){
		$retval = new stdClass();
		
		
		$primarydiscount = $value["primarydiscount"];
		if($primarydiscount == null) $primarydiscount =0;
		
		
		$secondarydiscount = $value["secondarydiscount"];
		if($secondarydiscount == null)  $secondarydiscount =0 ;
	
		$unitcommissionpercent = $value["unitcommissionpercent"];
		if($unitcommissionpercent == null)  $unitcommissionpercent =0 ;
		
		$custbalancepriceid = $value["custbalancepriceid"];
		
		$set = new Set();
		$custbalancepriceSet = $set->MboSetRemote("custbalanceprice")->setWhere("custbalancepriceid='$custbalancepriceid'")->getMboSet();
		$cp = $custbalancepriceSet->getMbo();
		
		
		$itemnum = $cp->getString("itemnum");
		$customercode = $cp->getString("customercode");
		$fromwarehouse = $cp->getString("fromwarehouse");	
		$fromracknum = $cp->getString("fromracknum");			
		$conditiontype = $cp->getString("conditiontype");	
		$costdate = $cp->getString("costdate");
		
		
		$wonum = $cp->getString("wonum");
		$batchnum = $cp->getString("batchnum");
		$expdate = $cp->getString("expdate");
		
		if(empty($conditiontype)) $condval = " and (conditiontype is null or conditiontype='') ";
		else $condval = " and conditiontype = '$conditiontype' ";
		
		$costdateval = Super::whereDate("costdate", $costdate);
		
		$set2 = new Set();
		$matuseSet = $set2->MboSetRemote("matusetrans")->setWhere("itemnum='$itemnum' and warehouse = '$fromwarehouse' and  wonum= '$wonum' $condval $costdateval and transtype='ISSUE'")
		->MboRemote("quantity")->getMboSet();
		
		$matuse = $matuseSet->getMbo();
		$oldqty = $matuse->getString("quantity");
		if($oldqty<0) $oldqty = $oldqty*-1;
		
		$quantity = $oldqty;
		
		$unitcost = $cp->getString("unitcost"); 
		if($unitcost == null) $unitcost = 0;
		$linecost = $quantity * $unitcost;
		$oldlinecost = $oldqty * $unitcost;
		
		$oldunitprice = $cp->getString("unitprice");
		if($oldunitprice == null) $oldunitprice = 0;
		
		$newunitprice = $value["newunitprice"];
		if($newunitprice == null) $newunitprice = $oldunitprice;
		
		
		$oldlineprice = $oldqty * $oldunitprice;
		
		$oldunitprofit = $cp->getString("unitprofit");
		if($oldunitprofit == null) $oldunitprofit = 0;		
		$oldlineprofit = $oldqty*$oldunitprofit;
		
		
		$unitprice = $newunitprice;
		$lineprice = $unitprice*$oldqty;
		
		$unitprofit = $unitprice-$unitcost;
		if($unitprofit == null) $unitprofit = 0;
		$lineprofit = $quantity * $unitprofit;
		
		/* PRICE AFTER DISCOUNT ADJUSTMENT*/
		$oldunitpricediscount = $cp->getString("unitpricediscount"); 
		if($oldunitpricediscount == null) $oldunitpricediscount = 0;		
		
		/*
		if(empty($newunitpricediscount)) $newunitpricediscount = $oldunitpricediscount;
		$unitpricediscount = $newunitpricediscount;
		
		*/
		$discountprice = $unitprice*($primarydiscount/100);
		$unitpricediscount = $unitprice-$discountprice;
		
		$discountprice2 = $unitpricediscount*($secondarydiscount/100);
		$unitpricediscount = $unitpricediscount-$discountprice2;
		
		$linepricediscount = $quantity * $unitpricediscount;
		$oldlinepricediscount = $quantity * $oldunitpricediscount;
		
		
		/* PROFIT ADJUSTMENT*/
		$oldunitprofitdiscount =  $cp->getString("unitprofitdiscount");
		if($oldunitprofitdiscount == null) $oldunitprofitdiscount = 0;
		$oldlineprofitdiscount = $oldqty * $oldunitprofitdiscount;
		
		$unitprofitdiscount = $unitpricediscount - $unitcost;
		$lineprofitdiscount = $quantity * $unitprofitdiscount;
		
		/* COMMISSION ADJUSTMENT */
		
		$oldunitcommission =  $cp->getString("unitcommission");
		if($oldunitcommission == null) $oldunitcommission = 0;
		$oldlinecommission = $oldqty * $oldunitcommission;
		
		$unitcommission = $unitpricediscount * ($unitcommissionpercent/100);
		$linecommission = $quantity * $unitcommission;
		
		
		
		$varianceqty = $quantity-$oldqty;
		$variancecost = $linecost-$oldlinecost;
		$varianceprice = $lineprice-$oldlineprice;
		$variancepricediscount = $linepricediscount - $oldlinepricediscount;
		$varianceprofit = $lineprofit - $oldlineprofit;
		$varianceprofitdiscount = $lineprofitdiscount - $oldlineprofitdiscount;
		
		if($variancepricediscount == 0 ){
			$msg = "noneedpriceadjustment";
		}else{
				
			$costdate = $cp->getString("costdate");
			
			$ss = new Set();
			$itemSet = $ss->MboSetRemote("item")->setWhere("itemnum='$itemnum'")->MboRemote("orgcode")->MboRemote("sitecode")->getMboSet();
			$item = $itemSet->getMbo();
			$orgcode = $item->getString("orgcode");
			$sitecode = $item->getString("sitecode");
			
			if($oldunitpricediscount >= $unitpricediscount){
				$gldebitacct = Xbo::getControlAcct($fromwarehouse, $orgcode, $sitecode);
				$glcreditacct =  Xbo::getwogl($wonum);
			}else{
				$gldebitacct = Xbo::getwogl($wonum); 
				$glcreditacct =  Xbo::getControlAcct($fromwarehouse, $orgcode, $sitecode);
			}
			
			$financialperiod = Xbo::getFinancialPeriod();
			$conditiontype = $cp->getString("conditiontype");
			$transtype = "PRICEADJ";
			
			$set3 = new Set();
			$set3->MboSetRemote("custinvtrans")
			->setValue("itemnum", $itemnum)->setValue("customercode", $customercode)->setValue("oldqty", $oldqty)->setValue("quantity", $quantity)->setValue("wonum", $wonum)
			->setValue("batchnum", $batchnum)->setValue("expdate", $expdate)->setValue("unitcost", $unitcost)->setValue("linecost", $linecost)->setValue("oldlinecost", $oldlinecost)
			->setValue("unitprice", $unitprice)->setValue("lineprice", $lineprice)->setValue("oldlineprice", $oldlineprice)->setValue("oldunitprice", $oldunitprice)->setValue("unitprofit", $unitprofit)
			->setValue("lineprofit", $lineprofit)->setValue("oldlineprofit", $oldlineprofit)->setValue("oldunitprofit", $oldunitprofit)->setValue("unitpricediscount", $unitpricediscount)->setValue("linepricediscount", $linepricediscount)
			->setValue("oldlinepricediscount", $oldlinepricediscount)->setValue("oldunitpricediscount", $oldunitpricediscount)->setValue("unitprofitdiscount", $unitprofitdiscount)->setValue("lineprofitdiscount", $lineprofitdiscount)->setValue("oldlineprofitdiscount", $oldlineprofitdiscount)
			->setValue("oldunitprofitdiscount", $oldunitprofitdiscount)->setValue("varianceqty", $varianceqty)->setValue("variancecost", $variancecost)->setValue("varianceprice", $varianceprice)->setValue("variancepricediscount", $variancepricediscount)
			->setValue("varianceprofit", $varianceprofit)->setValue("varianceprofitdiscount", $varianceprofitdiscount)
			->setValue("costdate", $costdate)->setValue("gldebitacct", $gldebitacct)->setValue("glcreditacct", $glcreditacct)->setValue("financialperiod", $financialperiod)->setValue("conditiontype", $conditiontype)
			->setValue("transtype", $transtype)->setValue("fromwarehouse", $fromwarehouse)->setValue("fromracknum", $fromracknum)
			->setValue("oldunitcommission", $oldunitcommission)->setValue("oldlinecommission", $oldlinecommission)
			->setValue("unitcommission", $unitcommission)->setValue("linecommission", $linecommission)
			->add();
			
			$set4 = new Set();
			
			$costdate = Super::whereDate("costdate", $costdate);
			$expdate = Super::whereDate("expireddate", $expdate);
			if(empty($conditiontype)) $condval = " and (conditiontype is null or conditiontype='')";
			else $condval = " and conditiontype='$conditiontype' ";
			
			if(empty($fromracknum)) $rackval = " and (racknum is null or racknum='')";
			else $rackval = " and racknum='$fromracknum' ";
					
			$whereclause = "itemnum = '$itemnum' and warehouse='$fromwarehouse' and wonum='$wonum' $condval $rackval $costdate $expdate";
			
			$matuseSet = $set4->MboSetRemote("matusetrans")->setWhere($whereclause)
			->setValue("unitprice", $unitprice)->setValue("lineprice", $lineprice)
			->setValue("unitprofit", $unitprofit)->setValue("lineprofit", $lineprofit)
			->setValue("unitpricediscount", $unitpricediscount)->setValue("linepricediscount", $linepricediscount)
			->setValue("unitprofitdiscount", $unitprofitdiscount)->setValue("lineprofitdiscount", $lineprofitdiscount)
			->setValue("primarydiscount", $primarydiscount)->setValue("secondarydiscount", $secondarydiscount)
			->change();
			$msg = "adjusted";
		}
		
		
		$retval->msg = $msg;
		return $retval;
	}
	
	public static function CustomerCostAdjustment(){
	
		$value = $_POST["costadjust"];
		$cnt = count($value);
		$newmsg = null;
		for($i=0; $i<$cnt; $i++){
			$get = ActionSet::ProcessCustomerCostAdjustment($value[$i]);
			$msg = $get->msg;
			if($msg == "notadjusted"){
				$newmsg = $msg;
				break;
			}else{
				if($msg == "noneedpriceadjustment"){
					$newmsg = $msg;
					break;
				}else{
					$newmsg = "adjusted";
				}
				
			}
		}
		Set::throws(["msg"=>$newmsg]);
	}
	
	public static function InsertOrUpdateInventory($custob){
		$si = new Set();
		$itemnum = $custob->itemnum;
		$towarehouse = $custob->towarehouse;
		$toracknum = $custob->toracknum;
		$orgcode = $custob->orgcode;
		$sitecode = $custob->sitecode;
		
		$inventorySet = $si->MboSetRemote("inventory")->setWhere("itemnum = '$itemnum' and warehouse='$towarehouse' ")->getMboSet();
		$inv = $inventorySet->counts();
		if($inv == 0){
			$si = new Set();
			$si->MboSetRemote("inventory")->setValue("itemnum", $itemnum)->setValue("warehouse", $towarehouse)->setValue("racknum", $toracknum)->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)
			->setValue("eoqqty", 1)->setValue("reorder", "Y")->add();
			
			ActionSet::InsertOrUpdateInvcost($custob);
		}
	}
	
	public static function InsertOrUpdateInvcost($custob){
		$itemnum = $custob->itemnum;
		$fromwarehouse = $custob->fromwarehouse;
		$towarehouse = $custob->towarehouse;
		$orgcode = $custob->orgcode;
		$sitecode = $custob->sitecode;
		$conditionrate = $custob->conditionrate;
		$conditiontype = $custob->conditiontype;
		$unitcost = $custob->unitcost;
		
		$st2 = new Set();
		$warehouseSet = $st2->MboSetRemote("warehouse")->setWhere("warehouse='$fromwarehouse' and orgcode='$orgcode' and sitecode='$sitecode'")->MboRemote("controlacc")->MboRemote("shrinkageacc")
		->MboRemote("invcostadjacc")->getMboSet()->getMbo();
		$controlacc = $warehouseSet->getString("controlacc");
		$shrinkageacc = $warehouseSet->getString("shrinkageacc");
		$invcostadjacc = $warehouseSet->getString("invcostadjacc");
		$glaccount = Xbo::getGLAccount($itemnum);
		
		if($conditionrate < 100){
			$se = new Set();
			$invcostSet =$se->MboSetRemote("invcost")->setWhere("itemnum='$itemnum' and conditionrate='100' and warehouse='$towarehouse'")->getMboSet();
			$ca = $invcostSet->counts();
			if($ca == 0){
				$st2 = new Set();
				$conditiontype100 = $st2->MboSetRemote("itemcondition")->setWhere("itemnum='$itemnum' and conditionrate=100")->MboRemote("conditiontype")->getMboSet()->getMbo()->getString("conditiontype");
		
				$unitcost100 = ($unitcost*100)/$conditionrate;
				$se = new Set();
				$se->MboSetRemote("invcost")->setValue("itemnum", $itemnum)->setValue("warehouse", $towarehouse)->setValue("conditiontype", $conditiontype100)
				->setValue("conditionrate", 100)->setValue("glaccount", $glaccount)->setValue("shrinkageacc", $shrinkageacc)->setValue("invcostadjacc", $invcostadjacc)
				->setValue("stdcost", $unitcost100)->setValue("avgcost", $unitcost100)->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)->add();
			}else{}
		}
						
		$sc = new Set();
		$sc->MboSetRemote("invcost")->setValue("itemnum", $itemnum)->setValue("warehouse", $towarehouse)->setValue("conditiontype", $conditiontype)
		->setValue("conditionrate", $conditionrate)->setValue("glaccount", $glaccount)->setValue("shrinkageacc", $shrinkageacc)->setValue("invcostadjacc", $invcostadjacc)
		->setValue("stdcost", $unitcost)->setValue("avgcost", $unitcost)->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)->add();
					
	}
	
	public static function InsertOrUpdateInvbalance($custob){
		$itemnum = $custob->itemnum;
		$towarehouse = $custob->towarehouse;
		$condval = $custob->condval;
		$rackval = $custob->rackval;
		$dateval = $custob->dateval;
		$expdate = $custob->expdate;
		$todaydate = $custob->enterdate;
		$conditiontype = $custob->conditiontype;
		$toracknum = $custob->toracknum;
		$retQty = $custob->retQty;
		$enterby = $custob->enterby;
		$enterdate = $custob->enterdate;
		$changedate = $custob->changedate;
		$orgcode = $custob->orgcode;
		$sitecode = $custob->sitecode;
		$changeby = $custob->changeby;
		$changedate = $custob->changedate;
		
		$set2= new Set();
		$invbalanceSet = $set2->MboSetRemote("invbalance")->setWhere("itemnum = '$itemnum' and warehouse='$towarehouse' $condval $rackval $dateval")->MboRemote("itemnum")
						->MboRemote("curbal")->getMboSet();
		$invbalance = $invbalanceSet->getMbo();
		$countinvbal = $invbalanceSet->counts();
		$oldcurbal = $invbalanceSet->getString("curbal");
		
		if($expdate != null ) $oexpdate = strtotime($expdate);
		$otodaydate = strtotime($todaydate);	
		
		if($expdate != null ){
			$odate = $oexpdate - $otodaydate;
			if ($odate < 0) $isexpired = "Y";
			else $isexpired = "N";
		}else{
			$isexpired = 'N';
		}
		
		$set3 = new Set();		
		if($countinvbal==0){
			$newcurbal = $retQty;
			$set3 = $set3->MboSetRemote("invbalance")->setValue("itemnum", $itemnum)->setValue("conditiontype", $conditiontype)->setValue("warehouse", $towarehouse)
					->setValue("racknum", $toracknum)->setValue("curbal", $newcurbal)->setValue("oldcurbal", $newcurbal)->setValue("enterby", $enterby)
					->setValue("enterdate", $enterdate)->setValue("expdate", $expdate)->setValue("isexpired", $isexpired)->setValue("changeby", $changeby)
					->setValue("changedate", $changedate)->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)->add();
		}else{
			$newcurbal = $retQty+$oldcurbal;
			$set3 = $set3->MboSetRemote("invbalance")->setWhere("itemnum = '$itemnum' and warehouse='$towarehouse' $condval $rackval $dateval")
					->setValue("curbal", $newcurbal)->setValue("oldcurbal", $oldcurbal)
					->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)->setValue("changeby", $changeby)->setValue("changedate", $changedate)->change();
		}
		
		$custob->newcurbal = $newcurbal;
		$custob->oldcurbal = $oldcurbal;
		ActionSet::ReturnMatusetrans($custob);
					
	}
	
	public static function ReturnCustbalance($custob){
		$custcurbal = $custob->custcurbal;
		$retQty = $custob->retQty;
		$droppedqty = $custob->droppedqty;
		$issuedqty = $custob->issuedqty;
		$custbalanceid = $custob->custbalanceid;
		
		$newcustcurbal = $custcurbal-$retQty;
		$newdroppedqty = $droppedqty-$retQty;
		
		$set = new Set();
		if($newcustcurbal==0 && $issuedqty <= 0){
			$set->MboSetRemote("custbalance")->setWhere("custbalanceid='$custbalanceid'")->remove();
		}else{
			$set->MboSetRemote("custbalance")->setWhere("custbalanceid='$custbalanceid'")->setValue("curbal", $newcustcurbal)->setValue("droppedqty", $newdroppedqty)->change();
		}
	}
	
	public static function InsertOrUpdateINVFIFOLIFOCOST($custob){
		$itemnum = $custob->itemnum;
		$towarehouse = $custob->towarehouse;
		$toracknum = $custob->toracknum;
		$conditiontype = $custob->conditiontype;
		$enterby = $custob->enterby;
		$enterdate = $custob->enterdate;
		$orgcode = $custob->orgcode;
		$sitecode = $custob->sitecode;
		$quantity = $custob->quantity;
		$costdate = $custob->costdate;
		$financialperiod = $custob->financialperiod;
		$conditionrate = $custob->conditionrate;
		$unitcost = $custob->unitcost;
		$batchnum = $custob->batchnum;
		$expdate = $custob->expdate;
		$retqty = $custob->retqty; 
		
		if (empty($conditiontype)) $condval = " and (conditiontype is null or conditiontype='')";
		else $condval = " and conditiontype='$conditiontype'";
			
		if(empty($toracknum)) $rackval = " and (racknum is null or racknum='')"; 
		else $rackval = " and racknum='$toracknum'";
		
		$set = new Set();
		$wccostdate = Super::whereDate("costdate", $costdate);
		$wcinv = "itemnum='$itemnum' and warehouse='$towarehouse'";
		$wcfifolifo = "$wcinv $condval $wccostdate $rackval";
		$wcfifolifopovendor = "$wcinv $condval $wccostdate ";
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
	public static function ReturnMatusetrans($custob){
	
		$set5 = new Set();
		$set6 = new Set();
		
		$retQty = $custob->retQty;
		$orgcode = $custob->orgcode;
		$sitecode = $custob->sitecode;
		$itemnum = $custob->itemnum;
		$towarehouse = $custob->towarehouse;
		$condval = $custob->condval;
		$wonum = $custob->wonum;
		$unitprice = $custob->unitprice;
		$unitpricediscount = $custob->unitpricediscount;
		$unitprofit = $custob->unitprofit;
		$unitprofitdiscount = $custob->unitprofitdiscount;
		$discountprice = $custob->discountprice;
		$primarydiscount = $custob->primarydiscount;
		$secondarydiscount = $custob->secondarydiscount;
		$issuetype = $custob->issuetype;
		$isconsignment = $custob->isconsignment;
		$changedate = $custob->changedate;
		$changeby = $custob->changeby;
		$enterdate = $custob->enterdate;
		$enterby = $custob->enterby;
		$newcurbal = $custob->newcurbal;
		$toracknum = $custob->toracknum;
		$conditiontype = $custob->conditiontype;
		$expdate = $custob->expdate;
		$itemdesc = $custob->itemdesc;
		$oldcurbal = $custob->oldcurbal;
		if($oldcurbal == null )$oldcurbal=0;
		$costdate = $custob->costdate;
		$quantity = $retQty;
		$actualdate = $custob->actualdate;
		
		$unitcost = $custob->unitcost;
		$linecost = $quantity*$unitcost;
		
		$unitcommission = $custob->unitcommission;
		$linecommission = $quantity*$unitcommission;
		$linecommissionmin = $linecommission*-1;
		
		
		$transtype = "RETURN";
		$glcreditacct = Xbo::getWOAccount($wonum, $orgcode, $sitecode); 
		$gldebitacct = Xbo::getGLAccount($itemnum, $towarehouse, $orgcode, $sitecode);
			
		$lineprice = $unitprice*$quantity;			
		$linepricemin = $lineprice*-1;
		
		$linepricediscount = $unitpricediscount*$quantity;
		$linepricediscountmin = $linepricediscount*-1;
		
		$lineprofit = $unitprofit*$quantity;	
		$lineprofitmin = $lineprofit*-1;
		
		$lineprofitdiscount = $unitprofitdiscount*$quantity;	
		$lineprofitdiscountmin = $lineprofitdiscount*-1;
		
		$discountpricemin = $discountprice*-1;  
		
		
		$set5->MboSetRemote("matusetrans")->setValue("wonum", $wonum)->setValue("description", $itemdesc)->setValue("itemnum", $itemnum)->setValue("expireddate", $expdate)
			->setValue("conditiontype", $conditiontype)->setValue("warehouse", $towarehouse)->setValue("racknum", $toracknum)->setValue("quantity", $quantity)
			->setValue("oldcurbal", $oldcurbal)->setValue("newcurbal", $newcurbal)->setValue("unitcost", $unitcost)->setValue("linecost", $linecost)
			->setValue("transtype", $transtype)->setValue("gldebitacct", $gldebitacct)->setValue("glcreditacct", $glcreditacct)->setValue("enterby", $enterby)
			->setValue("enterdate", $enterdate)->setValue("changeby", $changeby)->setValue("changedate", $changedate)->setValue("orgcode", $orgcode)
			->setValue("sitecode", $sitecode)->setValue("isconsignment", $isconsignment)->setValue("costdate", $costdate)
			->setValue("unitprice", $unitprice)->setValue("lineprice", $linepricemin)->setValue("unitpricediscount", $unitpricediscount)
			->setValue("linepricediscount", $linepricediscountmin)->setValue("unitprofit", $unitprofit)->setValue("lineprofit", $lineprofitmin)
			->setValue("unitprofitdiscount", $unitprofitdiscount)->setValue("lineprofitdiscount", $lineprofitdiscountmin)->setValue("discountprice", $discountpricemin)
			->setValue("unitcommission", $unitcommission)->setValue("linecommission", $linecommissionmin)
			->setValue("primarydiscount", $primarydiscount)->setValue("secondarydiscount", $secondarydiscount)
			->setValue("issuetype", $issuetype)->setValue("actualdate", $actualdate)->setValue("shipstatus", "RETURNED")
			->add();
		
		$custob->quantity = $quantity;
		$custob->unitcost = $unitcost;
		$custob->retqty = $quantity;
		ActionSet::InsertOrUpdateINVFIFOLIFOCOST($custob);
		
	}
	
	public static function SumWOCOst($custob){
		$wonum = $custob->wonum;
		$itemnum = $custob->itemnum;
		$towarehouse = $custob->towarehouse;
		
		$sumActualCost = Xbo::sumWOActualCost($wonum);
		$sumPlanCost = Xbo::sumWOPlanCost($wonum);
		$variancecost = $sumPlanCost-$sumActualCost;
		$set5 = new Set();
		$set5->MboSetRemote("workorder")->setWhere("wonum='$wonum'")->setValue("actualcost", $sumActualCost)
		->setValue("plannedcost",$sumPlanCost)->setValue("variancecost", $variancecost)->change();
		XboSet::AdjustConversionStock($itemnum, $towarehouse);
	}
	public static function CustomerProcessReturnItems($value){
		$custob = new stdClass();
				
		$enterby = Xbo::getLoginUser();
		$enterdate = Xbo::setEnterDate();
		$changeby = $enterby;
		$changedate = $enterdate;
		$financialperiod = Xbo::getFinancialPeriod();
		$isexpired = null;		
		
		$retval = new stdClass();
		$retQty = $value["retqty"];
		$towarehouse = $value["warehouse"];
		$toracknum = $value["racknum"];
		$actualdate = $value["actualdate"];		
		$custbalanceid = $value["custbalanceid"];		
		
		$whset = new Set();
		$warehouseSet = $whset->MboSetRemote("warehouse")->setWhere("warehouse='$towarehouse'")->MboRemote("orgcode")->MboRemote("sitecode")->getMboSet()->getMbo();
		$orgcode = $warehouseSet->getString("orgcode");
		$sitecode = $warehouseSet->getString("sitecode");
		
		$set = new Set();
		$custbalanceSet = $set->MboSetRemote("custbalance")->setWhere("custbalanceid='$custbalanceid'")->MboRemote("customercode")->MboRemote("wonum")->MboRemote("itemnum")
						->MboRemote("expdate")->MboRemote("curbal")->MboRemote("issuedqty")->MboRemote("droppedqty")->MboRemote("conditiontype")->MboRemote("fromwarehouse")
						->MboRemote("fromracknum")->MboRemote("costdate")->MboRemote("batchnum")->getMboSet();
						
		$custbalance = $custbalanceSet->getMbo();
		
		$customercode = $custbalance->getString("customercode");
		$wonum = $custbalance->getString("wonum");
		$itemnum = $custbalance->getString("itemnum");
		$expdate = $custbalance->getString("expdate");
		$custcurbal = $custbalance->getString("curbal");
		$issuedqty = $custbalance->getString("issuedqty");
		$droppedqty = $custbalance->getString("droppedqty");
		$conditiontype = $custbalance->getString("conditiontype");
		$fromwarehouse = $custbalance->getString("fromwarehouse");
		$fromracknum = $custbalance->getString("fromracknum");
		$costdate = $custbalance->getString("costdate");
		$batchnum = $custbalance->getString("batchnum");
		
		$setcn = new Set();
		$isconsignment = $setcn->MboSetRemote("inventory")->setWhere("itemnum='$itemnum' and warehouse = '$fromwarehouse'")->MboRemote("consignment")->getMboSet()->getMbo()->getString("consignment");
		//get item description
		$itemSet = new Set();
		$itemdesc = $itemSet->MboSetRemote("item")->setWhere("itemnum='$itemnum'")->MboRemote("description")->getMboSet()->getMbo()->getString("description");
		//end item description
			
		
		$rackval=null; $condval=null;
		if (empty($conditiontype)) $condval = "and (conditiontype='' or conditiontype is null)";
		else $condval = " and conditiontype='$conditiontype' ";
		
		if (empty($toracknum)) $rackval = "and (racknum='' or racknum is null)";
		else $rackval = " and racknum='$toracknum' ";	
		
		if($expdate == null) $dateval = null;
		else $dateval = Super::whereDate("expdate", $expdate);
		
		if($costdate == null) $costdateval = null;
		else $costdateval = Super::whereDate("costdate", $costdate);
		
		
			
		if($retQty>0){
			if($retQty<=$custcurbal){
				$si = new Set();
				$inventorySet = $si->MboSetRemote("inventory")->setWhere("itemnum = '$itemnum' and warehouse='$towarehouse' ")->getMboSet();
				$inv = $inventorySet->counts();
				
				//GET LAST COST TO BE ADDED AS AVERAGE COST IN THE NEW WAREHOUSE
				$st = new Set();
				$matusetransSet = $st->MboSetRemote("matusetrans")->setWhere("itemnum='$itemnum' and wonum='$wonum' and warehouse = '$fromwarehouse' and transtype='issue' $condval $costdateval")
				->MboRemote("unitcost")->MboRemote("unitprice")->MboRemote("unitpricediscount")->MboRemote("unitprofit")->MboRemote("unitprofitdiscount")
				->MboRemote("primarydiscount")->MboRemote("secondarydiscount")->MboRemote("discountprice")->MboRemote("issuetype")
				->MboRemote("unitcommission")->MboRemote("linecommission")
				->getMboSet();
				
				
				
				$matuse = $matusetransSet->getMbo();
				$unitcost = $matuse->getString("unitcost");
				//get price
				$unitprice = $matuse->getString("unitprice");
				$unitpricediscount = $matuse->getString("unitpricediscount");
				$unitprofit = $matuse->getString("unitprofit");
				$unitprofitdiscount = $matuse->getString("unitprofitdiscount");
				$discountprice = $matuse->getString("discountprice");
				$primarydiscount = $matuse->getString("primarydiscount");
				$secondarydiscount = $matuse->getString("secondarydiscount");
				$issuetype = $matuse->getString("issuetype");
				$unitcommission = $matuse->getString("unitcommission");
				$linecommission = $matuse->getString("linecommission");
				
				$st2 = new Set();
				$conditionrate = $st2->MboSetRemote("itemcondition")->setWhere("itemnum='$itemnum' $condval")->MboRemote("conditionrate")->getMboSet()->getMbo()->getString("conditionrate");
				
				$custob->enterby = $enterby;
				$custob->enterdate = $enterdate;
				$custob->changeby = $changeby;
				$custob->changedate = $changedate;
				$custob->financialperiod = $financialperiod;
				$custob->isexpired = $isexpired;
				$custob->retQty = $retQty;
				$custob->towarehouse = $towarehouse;
				$custob->toracknum = $toracknum;
				$custob->custbalanceid = $custbalanceid;
				$custob->orgcode = $orgcode;
				$custob->sitecode = $sitecode;
				$custob->customercode = $customercode;
				$custob->wonum = $wonum;
				$custob->itemnum = $itemnum;
				$custob->expdate = $expdate;
				$custob->custcurbal = $custcurbal;
				$custob->issuedqty = $issuedqty;
				$custob->droppedqty = $droppedqty;
				$custob->conditiontype = $conditiontype;
				$custob->conditionrate = $conditionrate;
				$custob->fromwarehouse = $fromwarehouse;
				$custob->fromracknum = $fromracknum;
				$custob->costdate = $costdate;
				$custob->batchnum = $batchnum;
				$custob->isconsignment = $isconsignment;
				$custob->itemdesc = $itemdesc;
				$custob->condval = $condval;
				$custob->rackval = $rackval;
				$custob->dateval = $dateval;
				$custob->costdateval = $costdateval;
				$custob->unitcost = $unitcost;
				$custob->unitprice = $unitprice;
				$custob->unitpricediscount = $unitpricediscount;
				$custob->unitprofit = $unitprofit;
				$custob->unitprofitdiscount = $unitprofitdiscount;
				$custob->discountprice = $discountprice;
				$custob->primarydiscount = $primarydiscount;
				$custob->secondarydiscount = $secondarydiscount;
				$custob->issuetype = $issuetype;
				$custob->actualdate = $actualdate;
				$custob->unitcommission = $unitcommission;
				$custob->linecommission = $linecommission;
				
				ActionSet::InsertOrUpdateInventory($custob);
				ActionSet::InsertOrUpdateInvbalance($custob);
				ActionSet::ReturnCustbalance($custob);
				ActionSet::SumWOCOst($custob);
				
				$retval->msg = "returned";
			}else{
				if($retQty>$custcurbal){
					$retval->msg = "quantityexceed";
				}
			}
		}else{
			if($retQty<=0){
				$retval->msg = "minusqtytransfer";
			}
		}
		
		return $retval;
	}
	public static function CustomerReturnItems(){
		$value = $_POST["returnitem"];
		$cnt = count($value);
		$newmsg = null;
		for($i=0; $i<$cnt; $i++){
			$get = ActionSet::CustomerProcessReturnItems($value[$i]);
			$msg = $get->msg;
			if($msg == "notadjusted"){
				$newmsg = $msg;
				break;
			}else{
				$newmsg = $msg;
			}
		}
		Set::throws(["msg"=>$newmsg]);
	}
} 

Class ValidateTables {
	public static function tbcx(){
		$dl = explode("|", $_GET["tbcx"]);
		$set = new Set();
		
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

	}
}
	

?>