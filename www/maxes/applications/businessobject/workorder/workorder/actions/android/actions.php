<?php
require_once ("../../../../eat.php");
//URL : http://202.157.187.252/maxes/applications/businessobject/workorder/workorder/actions/android/run.php
class WorkOrderAndroidActions {
	public static function ChangeStatusWorkorder($data){
		$wonum = $data->changestatuswo;
		$orgcode = $data->orgcode;  
		$objectname = $data->objectname; 
		$status = $data->status; 
		if(isset($data->status)){
			$status = $data->status;
		}else{
			$status = "approved";
		}
		
		$wc = "wonum='$wonum' and orgcode = '$orgcode'";
		$set = new Set();
		$workorderSet = $set->MboSetRemote("workorder")->setWhere($wc)->getMboSet();
		$countWO = $workorderSet->counts();
		if($countWO == 0){
			$msg="WO_DOES_NOT_EXIST";
		}else{
			$statusdate = Xbo::setEnterDate();
			$set = new Set();
			$workorderSet = $set->MboSetRemote("workorder")->setWhere($wc)->setValue("status", $status)->setValue("statusdate", $statusdate)->change();
			$set = new Set();
			$set2 = new Set();
			$enterdate = Xbo::setEnterDate();
			$enterby = Xbo::getLoginUser();
			$changeby = $enterby;
			$changedate = $enterdate;
			$xboset = $objectname;
			if ($xboset == "workorder") {
				$woplanmaterialSet = $set->MboSetRemote("woplanmaterial")->setWhere($wc)->MboRemote("wonum")
				->MboRemote("itemnum")->MboRemote("quantity")->MboRemote("conditiontype")
				->MboRemote("warehouse")->MboRemote("racknum")->getMboSet();
				
				$countWOPlan = $woplanmaterialSet->counts();
				if($countWOPlan >0 ){
					for ($i = 0; $i < $woplanmaterialSet->counts(); $i++) {
						$woplanmaterial = $woplanmaterialSet->getMbo();
						$wonum = $woplanmaterial->getString("wonum");
						$itemnum = $woplanmaterial->getString("itemnum");
						$quantity = $woplanmaterial->getString("quantity");
						$warehouse = $woplanmaterial->getString("warehouse");
						$conditiontype = $woplanmaterial->getString("conditiontype");
						$racknum = $woplanmaterial->getString("racknum");
						$set2->MboSetRemote("invreserve")->setValue("wonum", $wonum)->setValue("itemnum", $itemnum)
						->setValue("quantity", $quantity)->setValue("warehouse", $warehouse)->setValue("conditiontype", $conditiontype)
						->setValue("racknum", $racknum)->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)
						->setValue("changeby", $changeby)->setValue("changedate", $changedate)->add();
					}
				}
				
			}
			if ($xboset == "mr") {
				$mrlineSet = $set->MboSetRemote("mrline")->setRelationship("mr", "mr.mrnum = mrline.mrnum")->setWhere("mrline.mrnum='$wonum'")
				->MboRemote("mrline.mrnum")->MboRemote("mr.wonum")->MboRemote("mrline.itemnum")->MboRemote("mrline.quantity")
				->MboRemote("mrline.conditiontype")->MboRemote("mrline.warehouse")
				->MboRemote("mrline.racknum")->getMboSet();
				
				$countMR = $mrlineSet->counts();
				if($countMR >0){
					for ($i = 0; $i < $mrlineSet->counts(); $i++) {
						$mrline = $mrlineSet->getMbo();
						$mrnum = $mrline->getString("mrnum");
						$wonum = $mrline->getString("wonum");
						$itemnum = $mrline->getString("itemnum");
						$quantity = $mrline->getString("quantity");
						$warehouse = $mrline->getString("warehouse");
						$conditiontype = $mrline->getString("conditiontype");
						$racknum = $mrline->getString("racknum");
						$set2->MboSetRemote("invreserve")->setValue("mrnum", $mrnum)
						->setValue("wonum", $wonum)->setValue("itemnum", $itemnum)->setValue("quantity", $quantity)
						->setValue("warehouse", $warehouse)->setValue("conditiontype", $conditiontype)
						->setValue("racknum", $racknum)->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)
						->setValue("changeby", $changeby)->setValue("changedate", $changedate)->add();
					}
				}
				
			}
			
			$msg = "WO_STATUS_CHANGED";
		}
		
		$ss = new stdClass();
		$ss->msg = $msg;
		Set::throws($ss);
	}
	
	public static function AddWorkOrder($data){
		
		$ss = new stdClass;	
		$msg  = null;
		$addworkorder = $data->addworkorder;
		if(isset($data->addmaterials)){
			$addplanmaterials = $data->addmaterials;
		}else{
			$addplanmaterials = null;
		}
		
		$addwo = $addworkorder;
		$wonum = $addwo->wonum;
		$wodesc = $addwo->description;
		$status = $addwo->status;
		$assetnum = $addwo->assetnum;
		$location = $addwo->location;
		$priority = $addwo->priority;
		$owner = $addwo->owner;
		$leader = $addwo->leader;
		$orgcode = $addwo->orgcode;
		$sitecode = $addwo->sitecode;
		$targstartdate = $addwo->targstartdate;
		$targcompdate = $addwo->targcompdate;
		$statusdate = $addwo->statusdate;
		
		$set = new Set();
		$wc = "wonum='$wonum' and orgcode = '$orgcode'";
		$wcplan = "wonum='$wonum'";
		$WOSet = $set->MboSetRemote("workorder")->setWhere($wc)->getMboSet();
		$counts = $WOSet->counts();
		
		if($counts == 0){
			$set = new Set();
			$set->MboSetRemote("workorder")
			->setValue("wonum", $wonum)
			->setValue("description", $wodesc)
			->setValue("status", $status)
			->setValue("statusdate", $statusdate)
			->setValue("assetnum", $assetnum)
			->setValue("location", $location)
			->setValue("priority", $priority)
			->setValue("owner", $owner)
			->setValue("leader", $leader)
			->setValue("orgcode", $orgcode)
			->setValue("sitecode", $sitecode)
			->setValue("targstartdate", $targstartdate)
			->setValue("targcompdate", $targcompdate)
			->add();
		}else{
			$set = new Set();
			$set->MboSetRemote("workorder")->setWhere($wc)
			->setValue("description", $wodesc)
			->setValue("status", $status)
			->setValue("statusdate", $statusdate)
			->setValue("assetnum", $assetnum)
			->setValue("location", $location)
			->setValue("priority", $priority)
			->setValue("owner", $owner)
			->setValue("leader", $leader)
			->setValue("orgcode", $orgcode)
			->setValue("sitecode", $sitecode)
			->setValue("targstartdate", $targstartdate)
			->setValue("targcompdate", $targcompdate)
			->change();
		}
		
		if($addplanmaterials != null){
			$addmats = json_decode($addplanmaterials);
			$countmats = count($addmats);
			if($countmats >0 ){
				$set = new Set();
				$set->MboSetRemote("woplanmaterial")->setWhere($wcplan)->remove();
				for($i=0; $i<$countmats; $i++){
					$tt = $addmats[$i];
					$planlinenum =  $tt->planlinenum;
					$itemnum = $tt->itemnum;
					$warehouse = $tt->warehouse;
					$racknum = $tt->racknum;
					$conditiontype = $tt->conditiontype;
					$quantity =$tt->quantity;
					$unitcost = $tt->unitcost;
					$linecost = $tt->linecost;
					$enterby = $tt->enterby;
					$enterdate = $tt->enterdate;
					$gldebitacct = $tt->gldebitacct;
					$glcreditacct = $tt->glcreditacct;
					$remarks = $tt->remarks;
					
					$set2 = new Set();
					$set2->MboSetRemote("woplanmaterial")
					->setValue("planlinenum", $planlinenum)
					->setValue("itemnum", $itemnum)
					->setValue("warehouse", $warehouse)
					->setValue("racknum", $racknum)
					->setValue("conditiontype", $conditiontype)
					->setValue("quantity", $quantity)
					->setValue("unitcost", $unitcost)
					->setValue("linecost", $linecost)
					->setValue("enterby", $enterby)
					->setValue("enterdate", $enterdate)
					->setValue("gldebitacct", $gldebitacct)
					->setValue("glcreditacct", $glcreditacct)
					->setValue("remarks", $remarks)
					->setValue("wonum", $wonum)
					->add();
				}
			}
		}
		$ss->msg = "added";
		Set::throws($ss);
	}
	
}

		
		
?>