<?php
require_once ("../../../../eat.php");
//URL : http://202.157.187.252/maxessd/applications/businessobject/inventory/inventory/actions/android/run.php

class InventoryAndroidActions {
	public static function deleteInvFl(){
		$id = $_POST["deleteInvFl"];
		$set = new Set();
		$set->MboSetRemote("invfifolifocost")->setWhere("invfifolifocostid='$id'")->remove();
		
		$ss = new stdClass;
		$ss->msg = "deleted";
		Set::throws($ss);
	}
	public static function getIssueMaterials($minid, $length){
		$defaultlength = 10;
		$defaultminid = 1;
		$whereclause = null;
		$searchstr = $_POST["getIssueMaterials"];
		if($searchstr == "all"){
			$whereclause = "";
		}else{
			$ss = json_decode($searchstr);
			$whereclause = $searchstr;
		}
		if($length == null || $length == "null") { 
			$length = $defaultlength;
		}else{
			if(isset($_POST["length"])){
				if($_POST["length"] == null || $_POST["length"]=="null"){
					$length = $defaultlength;
				}else{
					$length = $_POST["length"];					
				}
			}else{
				$length = $defaultlength;				
			}
		}
		
		if($minid == null || $minid == "null"){
			$minid = 0;
		}else{
			 if(isset($_POST["minid"])){
				if($_POST["minid"] == null || $_POST["minid"]=="null"){
					$minid = $defaultminid;
				}else{
					$minid = $_POST["minid"];
				}
			}else{
				$minid = $defaultminid;
			}
		}
		
		$set = new Set();
		$tdr= [];
		$trr = [];
		$lastdata = 0;
		$lastdatatemp = 0;
		$orderby = "matusetrans.itemnum asc limit $minid,$length";
		if($searchstr == "all"){
			$TableSet = $set->MboSetRemote("matusetrans")
			->setRelationship("item", "item.itemnum = matusetrans.itemnum")
			->order("$orderby")
			->MboRemote("matusetrans.wonum")->MboRemote("matusetrans.itemnum")->MboRemote("matusetrans.quantity")
			->MboRemote("matusetrans.unitcost")->MboRemote("matusetrans.linecost")->MboRemote("item.issueunit")
			->MboRemote("matusetrans.warehouse")->MboRemote("matusetrans.racknum")->MboRemote("matusetrans.description")
			->MboRemote("matusetrans.batchnum")->MboRemote("matusetrans.transtype")->MboRemote("matusetrans.issuetype")
			->MboRemote("matusetrans.costdate")
			->getMboSet();
		}else{
			$TableSet = $set->MboSetRemote("matusetrans")
			->setRelationship("item", "item.itemnum = matusetrans.itemnum")
			->setWhere($whereclause)
			->order("$orderby")
			->MboRemote("matusetrans.wonum")->MboRemote("matusetrans.itemnum")->MboRemote("matusetrans.quantity")
			->MboRemote("matusetrans.unitcost")->MboRemote("matusetrans.linecost")->MboRemote("item.issueunit")
			->MboRemote("matusetrans.warehouse")->MboRemote("matusetrans.racknum")->MboRemote("matusetrans.description")
			->MboRemote("matusetrans.batchnum")->MboRemote("matusetrans.transtype")->MboRemote("matusetrans.issuetype")
			->MboRemote("matusetrans.costdate")
			->getMboSet();
		}
		
		$count = $TableSet->counts();
		for($i=0; $i<$count; $i++){
			$obj_row = new stdClass();
			$tdr = [];
			$table = $TableSet->getMbo();
			$obj_row->itemnum = $table->getString("itemnum");
			$obj_row->description = $table->getString("description");
			$obj_row->warehouse = $table->getString("warehouse");
			$obj_row->racknum = $table->getString("racknum");
			$obj_row->quantity = $table->getString("quantity");
			$obj_row->issueunit = $table->getString("issueunit");
			$obj_row->unitcost = $table->getString("unitcost");
			$obj_row->linecost = $table->getString("linecost");
			$obj_row->batchnum = $table->getString("batchnum");
			$obj_row->transtype = $table->getString("transtype");
			$obj_row->issuetype = $table->getString("issuetype");
			$obj_row->costdate = $table->getString("costdate");
			
			array_push($trr, $obj_row);
		}
		
		if($searchstr == "all"){
			$set = new Set();
			$TableSet  =  $set->MboSetRemote("matusetrans")->MboRemote("count(*) totaldata")->getMboSet();
			$table = $TableSet->getMbo();
			$totaldata = $table->getString("totaldata");
			if($totaldata == "") $totaldata = 0;
		}else{
			$set = new Set();
			$TableSet  =  $set->MboSetRemote("matusetrans")->setWhere($whereclause)->MboRemote("count(*) totaldata")->getMboSet();
			$table= $TableSet->getMbo();
			$totaldata = $table->getString("totaldata");
			if($totaldata == "") $totaldata = 0;
		}
		
		$data = new stdClass();
		$data->result = $trr;
		$data->minid = $minid;
		$data->totaldata = $totaldata;
		$data->lastdata = $minid+$length;
		$data->length = $length;
		$data->countdata = $count;
		$data->searchstr = $whereclause;
		$data->orgcode = $orgcode;
		Set::throws($data);
	}

	
	
	public static function getPlanMaterials($minid, $length){
		$defaultlength = 10;
		$defaultminid = 1;
		$whereclause = null;
		$searchstr = $_POST["getPlanMaterials"];
		if($searchstr == "all"){
			$whereclause = "";
		}else{
			$ss = json_decode($searchstr);
			$whereclause = $searchstr;
		}
		if($length == null || $length == "null") { 
			$length = $defaultlength;
		}else{
			if(isset($_POST["length"])){
				if($_POST["length"] == null || $_POST["length"]=="null"){
					$length = $defaultlength;
				}else{
					$length = $_POST["length"];					
				}
			}else{
				$length = $defaultlength;				
			}
		}
		
		if($minid == null || $minid == "null"){
			$minid = 0;
		}else{
			 if(isset($_POST["minid"])){
				if($_POST["minid"] == null || $_POST["minid"]=="null"){
					$minid = $defaultminid;
				}else{
					$minid = $_POST["minid"];
				}
			}else{
				$minid = $defaultminid;
			}
		}
		
		$set = new Set();
		$tdr= [];
		$trr = [];
		$lastdata = 0;
		$lastdatatemp = 0;
		$orderby = "woplanmaterial.itemnum asc limit $minid,$length";
		if($searchstr == "all"){
			$TableSet = $set->MboSetRemote("woplanmaterial")
			->setRelationship("item", "item.itemnum = woplanmaterial.itemnum")
			->order("$orderby")
			->MboRemote("woplanmaterial.wonum")->MboRemote("woplanmaterial.itemnum")->MboRemote("woplanmaterial.quantity")
			->MboRemote("woplanmaterial.unitcost")->MboRemote("woplanmaterial.linecost")->MboRemote("item.issueunit")
			->MboRemote("woplanmaterial.warehouse")->MboRemote("woplanmaterial.racknum")->MboRemote("item.description")
			->getMboSet();
		}else{
			$TableSet = $set->MboSetRemote("woplanmaterial")
			->setRelationship("item", "item.itemnum = woplanmaterial.itemnum")
			->setWhere($whereclause)
			->order("$orderby")
			->MboRemote("woplanmaterial.wonum")->MboRemote("woplanmaterial.itemnum")->MboRemote("woplanmaterial.quantity")
			->MboRemote("woplanmaterial.unitcost")->MboRemote("woplanmaterial.linecost")->MboRemote("item.issueunit")
			->MboRemote("woplanmaterial.warehouse")->MboRemote("woplanmaterial.racknum")->MboRemote("item.description")
			->getMboSet();
		}
		
		$count = $TableSet->counts();
		for($i=0; $i<$count; $i++){
			$obj_row = new stdClass();
			$tdr = [];
			$table = $TableSet->getMbo();
			$obj_row->itemnum = $table->getString("itemnum");
			$obj_row->description = $table->getString("description");
			$obj_row->warehouse = $table->getString("warehouse");
			$obj_row->racknum = $table->getString("racknum");
			$obj_row->quantity = $table->getString("quantity");
			$obj_row->issueunit = $table->getString("issueunit");
			$obj_row->unitcost = $table->getString("unitcost");
			$obj_row->linecost = $table->getString("linecost");
			array_push($trr, $obj_row);
		}
		
		if($searchstr == "all"){
			$set = new Set();
			$TableSet  =  $set->MboSetRemote("woplanmaterial")->MboRemote("count(*) totaldata")->getMboSet();
			$table = $TableSet->getMbo();
			$totaldata = $table->getString("totaldata");
			if($totaldata == "") $totaldata = 0;
		}else{
			$set = new Set();
			$TableSet  =  $set->MboSetRemote("woplanmaterial")->setWhere($whereclause)->MboRemote("count(*) totaldata")->getMboSet();
			$table= $TableSet->getMbo();
			$totaldata = $table->getString("totaldata");
			if($totaldata == "") $totaldata = 0;
		}
		
		$data = new stdClass();
		$data->result = $trr;
		$data->minid = $minid;
		$data->totaldata = $totaldata;
		$data->lastdata = $minid+$length;
		$data->length = $length;
		$data->countdata = $count;
		$data->searchstr = $whereclause;
		$data->orgcode = $orgcode;
		
		
		Set::throws($data);
	}
	public static function checkSafetyStock(){
		
		$set = new Set();
		$orgcode = $_POST["checkSafetyStock"];
		$ownerclause = "safetystock.orgcode like '%$orgcode%'";
		$safetyclause = " and currentstock <= safetystock";
		$TableSet = $set->MboSetRemote("safetystock")
			->setWhere($ownerclause.$safetyclause)
			->group("safetystock.itemnum, safetystock.warehouse")
			->MboRemote("itemnum")->MboRemote("description")->MboRemote("warehouse")->MboRemote("safetystock")
			->MboRemote("currentstock")->MboRemote("vendorcode")->MboRemote("name")->MboRemote("contact")
			->MboRemote("address")->MboRemote("city")->MboRemote("stateprovince")->MboRemote("zippostalcode")
			->MboRemote("phone")->MboRemote("orgcode")->MboRemote("sitecode")
			->getMboSet();
			
		$tableCount = $TableSet->counts();
		
		if($tableCount>0){
			$msg = "exist";
		
		}else{
			$msg = "notexist";
		}
		$ss = new stdClass;
		$ss->msg = $msg;
		Set::throws($ss);
	}
	
		public static function getConditionType($minid = null, $length = null){
		$defaultlength = 10;
		$defaultminid = 1;
		$whereclause = null;
		$searchstr = $_POST["getConditionType"];
		
		if($searchstr == "all"){
			$whereclause = "";
		}else{
			$whereclause = $searchstr;
		}
		if($length == null || $length == "null") { 
			$length = $defaultlength;
		}else{
			if(isset($_POST["length"])){
				if($_POST["length"] == null || $_POST["length"]=="null"){
					$length = $defaultlength;
				}else{
					$length = $_POST["length"];					
				}
			}else{
				$length = $defaultlength;				
			}
		}
		
		if($minid == null || $minid == "null"){
			$minid = 0;
		}else{
			 if(isset($_POST["minid"])){
				if($_POST["minid"] == null || $_POST["minid"]=="null"){
					$minid = $defaultminid;
				}else{
					$minid = $_POST["minid"];
				}
			}else{
				$minid = $defaultminid;
			}
		}
		
		$set = new Set();
		$tdr= [];
		$trr = [];
		$lastdata = 0;
		$lastdatatemp = 0;
		$ownerclause = "";
		//$ownerclause = "";
		$orderby = "conditiontype.conditiontype asc limit $minid,$length";
		
		$wc = "";
		if($searchstr == "all"){
			$wc = $ownerclause;
			$TableSet = $set->MboSetRemote("conditiontype")
			->order("$orderby")
			->MboRemote("conditiontype.conditiontype")->MboRemote("conditiontype.description")
			->getMboSet();
		}else{
			$wc = $whereclause;
			$TableSet = $set->MboSetRemote("conditiontype")
			->setWhere($wc)
			->order("$orderby")
			->MboRemote("conditiontype.conditiontype")->MboRemote("conditiontype.description")
			->getMboSet();
		}
		
		$count = $TableSet->counts();
		for($i=0; $i<$count; $i++){
			$obj_row = new stdClass();
			$tdr = [];
			$table = $TableSet->getMbo();
			$obj_row->conditiontype = $table->getString("conditiontype");
			$obj_row->description = $table->getString("description");
			array_push($trr, $obj_row);
		}
		
		
		if($searchstr == "all"){
			$set = new Set();
			$TableSet  =  $set->MboSetRemote("conditiontype")
			->MboRemote("count(conditiontypeid) totaldata")->getMboSet();
			$table = $TableSet->getMbo();
			$totaldata = $table->getString("totaldata");
			if($totaldata == "") $totaldata = 0;
			
		}else{ 
			$set = new Set();
			$TableSet  =  $set->MboSetRemote("conditiontype")
			->setWhere($wc)->MboRemote("count(conditiontypeid) totaldata")->getMboSet();
			$table= $TableSet->getMbo();
			$totaldata = $table->getString("totaldata");
			if($totaldata == "") $totaldata = 0;
		}
		
		$data= new stdClass();
		$data->result = $trr;
		$data->minid = $minid;
		$data->totaldata = $totaldata;
		$data->lastdata = $minid+$length;
		$data->length = $length;
		$data->countdata = $count;
		$data->searchstr = $whereclause;
		$data->tshoot = $wc;
		Set::throws($data);
	}
	
	
	public static function getCountry($minid = null, $length = null){
		$defaultlength = 10;
		$defaultminid = 1;
		$whereclause = null;
		$searchstr = $_POST["getCountry"];
		
		if($searchstr == "all"){
			$whereclause = "";
		}else{
			$whereclause = $searchstr;
		}
		if($length == null || $length == "null") { 
			$length = $defaultlength;
		}else{
			if(isset($_POST["length"])){
				if($_POST["length"] == null || $_POST["length"]=="null"){
					$length = $defaultlength;
				}else{
					$length = $_POST["length"];					
				}
			}else{
				$length = $defaultlength;				
			}
		}
		
		if($minid == null || $minid == "null"){
			$minid = 0;
		}else{
			 if(isset($_POST["minid"])){
				if($_POST["minid"] == null || $_POST["minid"]=="null"){
					$minid = $defaultminid;
				}else{
					$minid = $_POST["minid"];
				}
			}else{
				$minid = $defaultminid;
			}
		}
		
		$set = new Set();
		$tdr= [];
		$trr = [];
		$lastdata = 0;
		$lastdatatemp = 0;
		$ownerclause = "";
		//$ownerclause = "";
		$orderby = "country.name asc limit $minid,$length";
		
		$wc = "";
		if($searchstr == "all"){
			$wc = $ownerclause;
			$TableSet = $set->MboSetRemote("country")
			->order("$orderby")
			->MboRemote("country.countrycode")->MboRemote("country.name")
			->getMboSet();
		}else{
			$wc = $whereclause;
			$TableSet = $set->MboSetRemote("country")
			->setWhere($wc)
			->order("$orderby")
			->MboRemote("country.countrycode")->MboRemote("country.name")
			->getMboSet();
		}
		
		$count = $TableSet->counts();
		for($i=0; $i<$count; $i++){
			$obj_row = new stdClass();
			$tdr = [];
			$table = $TableSet->getMbo();
			$obj_row->country = $table->getString("countrycode");
			$obj_row->name = $table->getString("name");
			array_push($trr, $obj_row);
		}
		
		
		if($searchstr == "all"){
			$set = new Set();
			$TableSet  =  $set->MboSetRemote("country")
			->MboRemote("count(countryid) totaldata")->getMboSet();
			$table = $TableSet->getMbo();
			$totaldata = $table->getString("totaldata");
			if($totaldata == "") $totaldata = 0;
			
		}else{ 
			$set = new Set();
			$TableSet  =  $set->MboSetRemote("country")
			->setWhere($wc)->MboRemote("count(countryid) totaldata")->getMboSet();
			$table= $TableSet->getMbo();
			$totaldata = $table->getString("totaldata");
			if($totaldata == "") $totaldata = 0;
		}
		
		$data= new stdClass();
		$data->result = $trr;
		$data->minid = $minid;
		$data->totaldata = $totaldata;
		$data->lastdata = $minid+$length;
		$data->length = $length;
		$data->countdata = $count;
		$data->searchstr = $whereclause;
		$data->tshoot = $wc;
		Set::throws($data);
	}
	public static function modifyMeasureUnit(){
		$measureunitcode = $_POST["modifymeasureunit"];
		$description = $_POST["measureunitdesc"];
		$orgcode = $_POST["orgcode"];
		$set = new Set();
		$chc = $set->MboSetRemote("measureunit")->setWhere("measureunitcode='$measureunitcode' and orgcode='$orgcode'")->getMboSet();
		$cnt = $chc->counts();
		if($cnt == 0){
			$set = new Set();
			$set->MboSetRemote("measureunit")->setValue("measureunitcode", $measureunitcode)->setValue("description", $description)->setValue("orgcode", $orgcode)
			->add();
		}else{
			$set = new Set();
			$set->MboSetRemote("measureunit")->setWhere("measureunitcode='$measureunitcode'")->setValue("description", $description)->setValue("orgcode", $orgcode)
			->change();
			
		}
		$msg = "saved";
		$ss = new stdClass;
		$ss->msg = $msg;
		Set::throws($ss);
	}
	
	public static function deleteMeasureUnit(){
		$measureunitcode = $_POST["deletemeasureunit"];
		$set = new Set();
		$set->MboSetRemote("measureunit")->setWhere("measureunitcode='$measureunitcode'")->remove();
		
		$msg = "deleted";
		$ss = new stdClass;
		$ss->msg = $msg;
		Set::throws($ss);
	}
	public static function getSafetyStocks($minid = null, $length = null){
		$defaultlength = 10;
		$defaultminid = 1;
		$whereclause = null;
	
		$searchstr = $_POST["getSafetyStocks"];
		$orgcode = $_POST["orgcode"];
		
		if($searchstr == "all"){
			$whereclause = "";
		}else{
			$ss = json_decode($searchstr);
			$whereclause = $searchstr;
		}
		if($length == null || $length == "null") { 
			$length = $defaultlength;
		}else{
			if(isset($_POST["length"])){
				if($_POST["length"] == null || $_POST["length"]=="null"){
					$length = $defaultlength;
				}else{
					$length = $_POST["length"];					
				}
			}else{
				$length = $defaultlength;				
			}
		}
		
		if($minid == null || $minid == "null"){
			$minid = 0;
		}else{
			 if(isset($_POST["minid"])){
				if($_POST["minid"] == null || $_POST["minid"]=="null"){
					$minid = $defaultminid;
				}else{
					$minid = $_POST["minid"];
				}
			}else{
				$minid = $defaultminid;
			}
		}
		
		$set = new Set();
		$tdr= [];
		$trr = [];
		$lastdata = 0;
		$lastdatatemp = 0;
		
		$ownerclause = "safetystock.orgcode like '%$orgcode%'";
		$orderby = "safetystock.itemnum asc limit $minid,$length";
		$safetyclause = " and currentstock <= safetystock";
		if($searchstr == "all"){
			$TableSet = $set->MboSetRemote("safetystock")
			->setWhere($ownerclause.$safetyclause)
			->group("safetystock.itemnum, safetystock.vendorcode")
			->order("$orderby")
			->MboRemote("itemnum")->MboRemote("description")->MboRemote("safetystock")
			->MboRemote("sum(currentstock) as currentstock")->MboRemote("vendorcode")->MboRemote("name")->MboRemote("contact")
			->MboRemote("address")->MboRemote("city")->MboRemote("stateprovince")->MboRemote("zippostalcode")
			->MboRemote("phone")->MboRemote("orgcode")->MboRemote("sitecode")
			->getMboSet();
		}else{
			$whereclause = $whereclause. " and " .$ownerclause.$safetyclause;
			$TableSet = $set->MboSetRemote("safetystock")
			->setWhere($whereclause)
			->group("safetystock.itemnum, safetystock.vendorcode")
			->order("$orderby")
			->MboRemote("itemnum")->MboRemote("description")->MboRemote("safetystock")
			->MboRemote("sum(currentstock) as currentstock")->MboRemote("vendorcode")->MboRemote("name")->MboRemote("contact")
			->MboRemote("address")->MboRemote("city")->MboRemote("stateprovince")->MboRemote("zippostalcode")
			->MboRemote("phone")->MboRemote("orgcode")->MboRemote("sitecode")
			->getMboSet();
		}
		
		$count = $TableSet->counts();
		for($i=0; $i<$count; $i++){
			$obj_row = new stdClass();
			$tdr = [];
			$table = $TableSet->getMbo();
			$obj_row->itemnum = $table->getString("itemnum");
			$sett = new Set();
			$itemSet = $sett->MboSetRemote("item")->setWhere("itemnum='".$obj_row->itemnum ."'")->MboRemote("issueunit")->getMboSet();
			$it = $itemSet->getMbo();
			$issueunit = $it->getString("issueunit");
			$set2 = new Set();
			$invfifolifocostSet = $set2->MboSetRemote("invfifolifocost")->setWhere("itemnum='".$obj_row->itemnum ."'")->MboRemote("warehouse")->MboRemote("racknum")->MboRemote("batchnum")->MboRemote("quantity")->getMboSet();
			$ccnt = $invfifolifocostSet->counts();
			$rr = [];
			for($j=0; $j<$ccnt; $j++){
				$infl = $invfifolifocostSet->getMbo();
				$wh = $infl->getString("warehouse");
				$qty = $infl->getString("quantity");
				$racknum = $infl->getString("racknum");
				$batchnum = $infl->getString("batchnum");
				$batchval = "<b>Batch Num:</b> $batchnum";
				if($batchval == ""){
					$batchval = "";
				}
				$rackval = " On Rack <b>$racknum</b> " ;
				if($racknum == ""){
					$rackval = "";
				}
				
				$whqty = "<b>$wh</b>, $rackval: $qty $issueunit <br>$batchval<br><br>";
				
				
				$html = $whqty;
				array_push($rr, $html);
			}
			$obj_row->warehouse = implode("<br>", $rr);
			$obj_row->itemdesc = $table->getString("description");
			$obj_row->currentstock = $table->getString("currentstock");
			$obj_row->safetystock = $table->getString("safetystock"). " $issueunit.";
			$obj_row->vendorcode = $table->getString("vendorcode");
			$obj_row->vendordesc = $table->getString("name");
			$obj_row->contactname = $table->getString("contact");
			$obj_row->address = $table->getString("address");
			$obj_row->phone = $table->getString("phone");
			$obj_row->city = $table->getString("city");
			$obj_row->province = $table->getString("stateprovince");
			$obj_row->postalcode = $table->getString("zippostalcode");
			$obj_row->orgcode = $table->getString("orgcode");
			$obj_row->sitecode = $table->getString("sitecode");	
			array_push($trr, $obj_row);
		}
		
		
		if($searchstr == "all"){ 
			$set = new Set();
			$TableSet2 = $set->MboSetRemote("safetystock")
			->setWhere($ownerclause.$safetyclause)
			->group("safetystock.itemnum, safetystock.warehouse")
			->MboRemote("itemnum")->MboRemote("description")->MboRemote("warehouse")->MboRemote("safetystock")
			->MboRemote("currentstock")->MboRemote("vendorcode")->MboRemote("name")->MboRemote("contact")
			->MboRemote("address")->MboRemote("city")->MboRemote("stateprovince")->MboRemote("zippostalcode")
			->MboRemote("phone")->MboRemote("orgcode")->MboRemote("sitecode")
			->getMboSet();
			$totaldata = $TableSet2->counts();
			
		}else{
			$set = new Set();
			$whereclause = $whereclause. " and " .$ownerclause.$safetyclause;
			$TableSet2 = $set->MboSetRemote("safetystock")
			->setWhere($whereclause)
			->group("safetystock.itemnum, safetystock.warehouse")
			->MboRemote("itemnum")->MboRemote("description")->MboRemote("warehouse")->MboRemote("safetystock")
			->MboRemote("currentstock")->MboRemote("vendorcode")->MboRemote("name")->MboRemote("contact")
			->MboRemote("address")->MboRemote("city")->MboRemote("stateprovince")->MboRemote("zippostalcode")
			->MboRemote("phone")->MboRemote("orgcode")->MboRemote("sitecode")
			->getMboSet();
			$totaldata = $TableSet2->counts();
		}
		
		$data= new stdClass();
		$data->result = $trr;
		$data->minid = $minid;
		$data->totaldata = $totaldata;
		$data->lastdata = $minid+$length;
		$data->length = $length;
		$data->countdata = $count;
		$data->searchstr = $whereclause;
		$data->orgcode = $orgcode;
		Set::throws($data);
	}
	public static function readMultiConversionStock(){
		$itemnum = $_POST["readMultiConversionStock"];
		
		$set = new Set();
		$itemSet = $set->MboSetRemote("item")->setWhere("itemnum='$itemnum'")->MboRemote("issueunit")->getMboSet();
		$item = $itemSet->getMbo();
		$issueunit = $item->getString("issueunit");
		
		$set = new Set();
		$invfifolifocostSet = $set->MboSetRemote("invfifolifocost")->setWhere("itemnum = '$itemnum'")->group("itemnum, warehouse")
		->MboRemote("sum(quantity) as totalqtyperwarehouse")
		->MboRemote("warehouse")->getMboSet();
		$countFifo = $invfifolifocostSet->counts();
		
		$totalqty = 0;
		$statement_array = [];
		if($countFifo > 0){
			for($i=0; $i<$countFifo; $i++){
				$number = $i+1;
				$invfifolifocost = $invfifolifocostSet->getMbo();
				$totalqtyperwarehouse = $invfifolifocost->getString("totalqtyperwarehouse");
				$warehouse = $invfifolifocost->getString("warehouse");
				$format_totalqtyperwarehouse = number_format($totalqtyperwarehouse);
				$statement = "$number. <b>$warehouse</b>'s Warehouse is $format_totalqtyperwarehouse $issueunit . <br>";
				array_push($statement_array, $statement);
				$totalqty = $totalqty+$totalqtyperwarehouse;
			}
		}
		
		
		$set = new Set();
		$invconversionSet = $set->MboSetRemote("invconversion")->setWhere("itemnum='$itemnum'")->MboRemote("tomeasureunit")->MboRemote("conversion")->getMboSet("distinct");
		$count = $invconversionSet->counts();
		$statement_conversion_array = [];
		
		if($count == 0){
			
		}else{
			if($totalqty >0 ){
				for($i=0; $i<$count; $i++){
					$totalqtyconversion = 0;
					$invconversion = $invconversionSet->getMbo();
					$tomeasureunit = $invconversion->getString("tomeasureunit");
					$conversion = $invconversion->getString("conversion");
					$totalqtyconversion = round($totalqty / $conversion);
					
					$format_totalqtyconversion = number_format($totalqtyconversion);
					$statement = "$format_totalqtyconversion $tomeasureunit. \n";
					array_push($statement_conversion_array, $statement);
				}
			}
		}
		
		$ss = new stdClass;
		$ss->msg = "read";
		$format_totalqty = number_format($totalqty);
		$ss->totalqty = "Total quantity: $format_totalqty $issueunit";
		$ss->stockperwarehouse = $statement_array;
		$ss->stockconversion = $statement_conversion_array;
		
		Set::throws($ss);
	}
	public static function deleteConversionUnit(){
		$itemnum = $_POST["deleteConversionUnit"];
		$tomeasureunit = $_POST["tomeasureunit"];
		
		$set2 = new Set();
		$set2->MboSetRemote("invconversion")->setWhere("itemnum='$itemnum' and tomeasureunit='$tomeasureunit'")->remove();
		
		$ss = new stdClass;
		$ss->msg = "deleted";
		Set::throws($ss);
	}
	public static function addMoreConversion(){
		$toconversion = $_POST["addMoreConversion"];
		$conversionfactor = $_POST["conversionfactor"];
		$itemnum = $_POST["itemnum"];
		
		$set = new Set();
		$findWHSet = $set->MboSetRemote("inventory")->setWhere("itemnum='$itemnum'")->MboRemote("warehouse")->getMboSet("distinct");
		$counts =  $findWHSet->counts();
		
		if($counts >0 ){
			for($i=0; $i<$counts; $i++){
				$findWH = $findWHSet->getMbo();
				$warehouse = $findWH->getString("warehouse");
				$set2 = new Set();
				$set2->MboSetRemote("invconversion")->setWhere("itemnum='$itemnum' and tomeasureunit='$toconversion'")->remove();
				
				$set3 = new Set();
				$set3->MboSetRemote("invconversion")->setValue("itemnum", $itemnum)->setValue("warehouse", $warehouse)
				->setValue("tomeasureunit", $toconversion)->setValue("conversion", $conversionfactor)->add();
				
			}
		}
		
		
		$msg = "added";
		$ss = new stdClass;
		$ss->msg = $msg;
		Set::throws($ss);
		
	}
	public static function AddConversions($minid = null, $length = null){		
		$defaultlength = 10;
		$defaultminid = 1;
		$whereclause = null;
		$searchstr = $_POST["getItemConversions"];
		
		if($searchstr == "all"){
			$whereclause = "";
		}else{
			$ss = json_decode($searchstr);
			$whereclause = $searchstr;
		}
		if($length == null || $length == "null") { 
			$length = $defaultlength;
		}else{
			if(isset($_POST["length"])){
				if($_POST["length"] == null || $_POST["length"]=="null"){
					$length = $defaultlength;
				}else{
					$length = $_POST["length"];					
				}
			}else{
				$length = $defaultlength;				
			}
		}
		
		if($minid == null || $minid == "null"){
			$minid = 0;
		}else{
			 if(isset($_POST["minid"])){
				if($_POST["minid"] == null || $_POST["minid"]=="null"){
					$minid = $defaultminid;
				}else{
					$minid = $_POST["minid"];
				}
			}else{
				$minid = $defaultminid;
			}
		}
		
		$set = new Set();
		$tdr= [];
		$trr = [];
		$lastdata = 0;
		$lastdatatemp = 0;
		$orderby = "invconversion.tomeasureunit asc limit $minid,$length";
		if($searchstr == "all"){
			$TableSet = $set->MboSetRemote("invconversion")
			->order("$orderby")
			->MboRemote("invconversion.tomeasureunit")->MboRemote("invconversion.conversion")->MboRemote("invconversion.invconversionid")
			->getMboSet();
		}else{
			$TableSet = $set->MboSetRemote("invconversion")
			->setWhere($whereclause)
			->order("$orderby")
			->MboRemote("invconversion.tomeasureunit")->MboRemote("invconversion.conversion")->MboRemote("invconversion.invconversionid")
			->getMboSet();
		}
		
		$count = $TableSet->counts();
		
		
		for($i=0; $i<$count; $i++){
			$obj_row = new stdClass();
			$tdr = [];
			$table = $TableSet->getMbo();
			$obj_row->tomeasureunit = $table->getString("tomeasureunit");
			$obj_row->conversionfactor = $table->getString("conversion");
			$obj_row->invconversionid = $table->getString("invconversionid");
			
			array_push($trr, $obj_row);
		}
		
		if($searchstr == "all"){
			$set = new Set();
			$TableSet  =  $set->MboSetRemote("invconversion")->setWhere($ownerclause)->MboRemote("count(invconversionid) totaldata")->getMboSet();
			$table = $TableSet->getMbo();
			$totaldata = $table->getString("totaldata");
			if($totaldata == "") $totaldata = 0;
			
		}else{
			$set = new Set();
			$TableSet  =  $set->MboSetRemote("invconversion")->setWhere($whereclause)->MboRemote("count(invconversionid) totaldata")->getMboSet();
			$table= $TableSet->getMbo();
			$totaldata = $table->getString("totaldata");
			if($totaldata == "") $totaldata = 0;
		}
		
		$data= new stdClass();
		$data->result = $trr;
		$data->minid = $minid;
		$data->totaldata = $totaldata;
		$data->lastdata = $minid+$length;
		$data->length = $length;
		$data->countdata = $count;
		$data->searchstr = $whereclause;
		$data->orgcode = $orgcode;
		Set::throws($data);
	}
	public static function AddVendors(){
		$vendorid = $_POST["addvendor"];
		$vendorname = $_POST["vendorname"];
		$contactname = $_POST["contactname"];
		$phone = $_POST["phone"];
		$address = $_POST["address"];
		$city = $_POST["city"];
		$province = $_POST["province"];
		$postalcode = $_POST["postalcode"];
		$orgcode = $_POST["orgcode"];
		
		$enterdate = Xbo::setEnterDate();
		$enterby = Xbo::getLoginUser();
		$changeby = $enterby;
		$changedate = $enterdate;
		$ownerclause = "vendor.vendorcode = '$vendorid'";
		$set = new set();
		$checkSet = $set->MboSetRemote("vendor")->setWhere($ownerclause)->getMboSet();
		$count = $checkSet->counts();
		if($count == 0){
			$set = new Set();
			$set->MboSetRemote("vendor")
			->setValue("vendorcode", $vendorid)
			->setValue("name", $vendorname)
			->setValue("contact", $contactname)
			->setValue("phone", $phone)
			->setValue("address", $address)
			->setValue("city", $city)
			->setValue("stateprovince", $province)
			->setValue("zippostalcode", $postalcode)
			->setValue("orgcode", $orgcode)
			->add();
		}else{
			$set = new Set();
			$set->MboSetRemote("vendor")
			->setWhere("vendorcode = '$vendorid' and orgcode='$orgcode'")
			->setValue("name", $vendorname)
			->setValue("contact", $contactname)
			->setValue("phone", $phone)
			->setValue("address", $address)
			->setValue("city", $city)
			->setValue("stateprovince", $province)
			->setValue("zippostalcode", $postalcode)
			->change();
		}
		$wccontact = "vendorcode = '$vendorid' and contact = '$contactname'";
		$set = new Set();
		$vendorContactSet = $set->MboSetRemote("vendorcontact")->setWhere($wccontact)->getMboSet();
		$cnt = $vendorContactSet->counts();
		
		if($cnt == 0){
			$set = new Set();
			$set->MboSetRemote("vendorcontact")
			->setValue("vendorcode", $vendorid)
			->setValue("contact", $contactname)
			->setValue("phone", $phone)
			->setValue("enterby", $enterby)
			->setValue("enterdate", $enterdate)
			->setValue("changeby", $changeby)
			->setValue("changedate", $changedate)
			->add();
		}else{
			$set = new Set();
			$set->MboSetRemote("vendorcontact")
			->setWhere($wccontact)
			->setValue("contact", $contactname)
			->setValue("phone", $phone)
			->setValue("enterby", $enterby)
			->setValue("enterdate", $enterdate)
			->setValue("changeby", $changeby)
			->setValue("changedate", $changedate)
			->add();
		}
		$ss = new stdClass;
		$ss->msg = "added";
		Set::throws($ss);
	}
	public static function AddWarehouse(){
		$warehouse = $_POST["addwarehouse"];
		$description = $_POST["description"];
		$addresscode = $_POST["addresscode"];
		$address = $_POST["address"];
		$orgcode = $_POST["orgcode"];
		$sitecode = $_POST["sitecode"];
		
		$wc = "warehouse='$warehouse'";
		$set = new Set();
		$warehouseSet = $set->MboSetRemote("warehouse")->setWhere($wc)->getMboSet();
		$count = $warehouseSet->counts();
		if($count == 0){
			//add
			$set = new Set();
			$set->MboSetRemote("warehouse")
			->setValue("warehouse", $warehouse)
			->setValue("description", $description)
			->setValue("shippingaddress", $addresscode)
			->setValue("billingaddress", $addresscode)
			->setValue("orgcode", $orgcode)
			->setValue("sitecode", $sitecode)
			->add();
			
		}else{
			$set = new Set();
			$set->MboSetRemote("warehouse")->setWhere($wc)
			->setValue("description", $description)
			->setValue("shippingaddress", $addresscode)
			->setValue("billingaddress", $addresscode)
			->change();
		}
		
		$wc = "addresscode = '$addresscode'";
		$set2 = new Set();
		$addressSet = $set2->MboSetRemote("address")->setWhere($wc)->getMboSet();
		$countAddress = $addressSet->counts();
		$enterdate = Xbo::setEnterDate();
		$enterby = Xbo::getLoginUser();
			
		$changeby = $enterby;
		$changedate = $enterdate;
		
		if($countAddress == 0){
			if($addresscode != null){
				$set3 = new Set();
				$set3->MboSetRemote("address")
				->setValue("addresscode", $addresscode)
				->setValue("address", $address)
				->setValue("enterby", $enterby)
				->setValue("enterdate", $enterdate)
				->setValue("changeby", $changeby)
				->setValue("changedate", $changedate)
				->setValue("orgcode", $orgcode)
				->add();
			}
			
		}else{
			$set3 = new Set();
			$set3->MboSetRemote("address")->setWhere($wc)
			->setValue("address", $address)
			->setValue("changeby", $changeby)
			->setValue("changedate", $changedate)
			->setValue("orgcode", $orgcode)
			->change();
		}
		$aa = new stdClass;
		$aa->msg = "added";
		Set::throws($aa);
	}
	
	public static function DelRacks(){
		$racknum = $_POST["delracks"];
		$warehouse = $_POST["warehouse"];
		$rackdesc = $_POST["rackdesc"];
		$orgcode = $_POST["orgcode"];
		$sitecode = $_POST["sitecode"];
		$wc = "warehouse='$warehouse' and racknum='$racknum'";
		$set = new Set();
		$set->MboSetRemote("warehouseline")->setWhere($wc)->remove();
		$ss = new stdClass;
		$ss->msg = "deleted";
		
		Set::throws($ss);
		
	}
	public static function AddRacks(){
		$racknum = $_POST["addracks"];
		$warehouse = $_POST["warehouse"];
		$rackdesc = $_POST["rackdesc"];
		$orgcode = $_POST["orgcode"];
		$sitecode = $_POST["sitecode"];
		$warehouselinenum = Xbo::getLineNumber("warehouseline", "warehouselinenum", "warehouse='$warehouse'");
		$enterdate = Xbo::setEnterDate();
		$enterby = Xbo::getLoginUser();
		
		$changeby = $enterby;
		$changedate = $enterdate;
		
		if(isset($_POST["racktype"])){
			$racktype = $_POST["racktype"];
		}else{
			$racktype = "modular-shelving";
		}
		
		if(isset($_POST["width"])){
			$width = $_POST["width"];
		}else{
			$width = null;
		}
		if(isset($_POST["height"])){
			$height = $_POST["height"];
		}else{
			$height = null;
		}
		
		
		if(isset($_POST["length"])){
			$length = $_POST["length"];
		}else{
			$length = null;
		}
		
		if(isset($_POST["volume"])){
			$volume = $_POST["volume"];
		}else{
			$volume = null;
		}
		
		$wc = "warehouse='$warehouse' and racknum='$racknum'";
		$set = new Set();
		$rackSet = $set->MboSetRemote("warehouseline")->setWhere($wc)->getMboSet();
		$rackCount = $rackSet->counts();
		
		if($rackCount == 0){
			$set = new Set();
			$set->MboSetRemote("warehouseline")
			->setValue("warehouse", $warehouse)
			->setValue("warehouselinenum", $warehouselinenum)
			->setValue("racknum", $racknum)
			->setValue("description", $rackdesc)
			->setValue("racktype", $racktype)
			->setValue("width", $width)
			->setValue("height", $height)
			->setValue("length", $length)
			->setValue("volume", $volume)
			->setValue("enterby", $enterby)
			->setValue("enterdate", $enterdate)
			->setValue("changeby", $changeby)
			->setValue("changedate", $changedate)
			->add();
		}else{
			$set = new Set();
			$set->MboSetRemote("warehouseline")
			->setWhere($wc)
			->setValue("description", $rackdesc)
			->setValue("racktype", $racktype)
			->setValue("width", $width)
			->setValue("height", $height)
			->setValue("length", $length)
			->setValue("volume", $volume)
			->setValue("changeby", $changeby)
			->setValue("changedate", $changedate)
			->change();
		}
		
		
		$set = new Set();
		$wc = "warehouse = '$warehouse'";
		$warehouseSet = $set->MboSetRemote("warehouse")->setWhere($wc)->getMboSet();
		$cnt = $warehouseSet->counts();
		
		if($cnt == 0){
			$set = new Set();
			$set->MboSetRemote("warehouse")
			->setValue("warehouse", $warehouse)
			->setValue("orgcode", $orgcode)
			->setValue("sitecode", $sitecode)
			->setValue("isinspection", "N")
			->setValue("changeby", $changeby)
			->setValue("changedate", $changedate)
			->setValue("enterby", $enterby)
			->setValue("enterdate", $enterdate)
			->add();
		}else{
			
		}
		
		$ss = new stdClass;
		$ss->msg = "added";
		
		Set::throws($ss);
		
	}


	public static function AddMeasureUnit(){
		$measureunitcode = $_POST["addMeasureUnit"];
		$description = $_POST["description"];
		$msg = "exist";
		$set = new Set();
		$chc = $set->MboSetRemote("measureunit")->setWhere("measureunitcode='$measureunitcode'")->getMboSet();
		$cnt = $chc->counts();
		if($cnt > 0){
			$msg = "exist";
		}else{
			$set = new Set();
			$set->MboSetRemote("measureunit")->setValue("measureunitcode", $measureunitcode)->setValue("description", $description)
			->add();
			$msg = "added";
		}
		
		$ss = new stdClass;
		$ss->msg = $msg;
		Set::throws($ss);
	}
	public static function refreshRecord(){
		$id = $_POST["refreshinvFifoLifoRecord"];
		$set = new Set();
		$invfifolifocostSet = $set->MboSetRemote("invfifolifocost")->setWhere("invfifolifocostid='$id'")->MboRemote("quantity")->getMboSet();
		$invfifolifocost = $invfifolifocostSet->getMbo();
		$quantity = $invfifolifocost->getString("quantity");
		
		$ss = new stdClass;
		$ss->quantity = $quantity;
		Set::throws($ss);
		
	}
	public static function getInventoryPrice($minid = null, $length = null){
		$defaultlength = 10;
		$defaultminid = 1;
		$whereclause = null;
		$searchstr = $_POST["getInventoryPrice"];
		$searchItemnum = $_POST["itemnum"];
		$searchWarehouse = $_POST["warehouse"];
		
		if($searchstr == "all"){
			$whereclause = "";
		}else{
			$whereclause = $searchstr;
		}
		if($length == null || $length == "null") { 
			$length = $defaultlength;
		}else{
			if(isset($_POST["length"])){
				if($_POST["length"] == null || $_POST["length"]=="null"){
					$length = $defaultlength;
				}else{
					$length = $_POST["length"];					
				}
			}else{
				$length = $defaultlength;				
			}
		}
		
		if($minid == null || $minid == "null"){
			$minid = 0;
		}else{
			 if(isset($_POST["minid"])){
				if($_POST["minid"] == null || $_POST["minid"]=="null"){
					$minid = $defaultminid;
				}else{
					$minid = $_POST["minid"];
				}
			}else{
				$minid = $defaultminid;
			}
		}
		
		$set = new Set();
		$tdr= [];
		$trr = [];
		$lastdata = 0;
		$lastdatatemp = 0;
		$ownerclause = "invprice.itemnum like '%$searchItemnum%' and invprice.warehouse like '%$searchWarehouse%'";
		//$ownerclause = "";
		$orderby = "invprice.pricecode asc limit $minid,$length";
		
		$wc = "";
		if($searchstr == "all"){
			$wc = $ownerclause;
			$TableSet = $set->MboSetRemote("invprice")
			->setWhere($wc)
			->order("$orderby")
			->MboRemote("invprice.itemnum")->MboRemote("invprice.conditiontype")->MboRemote("invprice.pricecode")->MboRemote("invprice.unitprice")->MboRemote("invprice.invpriceid")
			->getMboSet();
		}else{
			$wc = $whereclause . " and ".$ownerclause;
			$TableSet = $set->MboSetRemote("invprice")
			->setWhere($wc)
			->order("$orderby")
			->MboRemote("invprice.itemnum")->MboRemote("invprice.conditiontype")->MboRemote("invprice.pricecode")->MboRemote("invprice.unitprice")->MboRemote("invprice.invpriceid")
			->getMboSet();
		}
		
		$count = $TableSet->counts();
		for($i=0; $i<$count; $i++){
			$obj_row = new stdClass();
			$tdr = [];
			$table = $TableSet->getMbo();
			$obj_row->itemnum = $table->getString("itemnum");
			$obj_row->conditiontype = $table->getString("conditiontype");
			$obj_row->pricecode = $table->getString("pricecode");
			$obj_row->unitprice = $table->getString("unitprice");
			$obj_row->invpriceid = $table->getString("invpriceid");
			array_push($trr, $obj_row);
		}
		
		
		if($searchstr == "all"){
			$set = new Set();
			$TableSet  =  $set->MboSetRemote("invprice")
			->setWhere($wc)
			->MboRemote("count(invpriceid) totaldata")->getMboSet();
			$table = $TableSet->getMbo();
			$totaldata = $table->getString("totaldata");
			if($totaldata == "") $totaldata = 0;
			
		}else{
			$set = new Set();
			$TableSet  =  $set->MboSetRemote("invprice")
			->setWhere($wc)->MboRemote("count(invpriceid) totaldata")->getMboSet();
			$table= $TableSet->getMbo();
			$totaldata = $table->getString("totaldata");
			if($totaldata == "") $totaldata = 0;
		}
		
		$data= new stdClass();
		$data->result = $trr;
		$data->minid = $minid;
		$data->totaldata = $totaldata;
		$data->lastdata = $minid+$length;
		$data->length = $length;
		$data->countdata = $count;
		$data->searchstr = $whereclause;
		$data->tshoot = $wc;
		Set::throws($data);
	}
	public static function getInventoryReservation($minid = null, $length = null){
		$defaultlength = 10;
		$defaultminid = 1;
		$whereclause = null;
		$searchstr = $_POST["getInventoryReservation"];
		if($searchstr == "all"){
			$whereclause = "";
		}else{
			$ss = json_decode($searchstr);
			$whereclause = $searchstr;
		}
		if($length == null || $length == "null") { 
			$length = $defaultlength;
		}else{
			if(isset($_POST["length"])){
				if($_POST["length"] == null || $_POST["length"]=="null"){
					$length = $defaultlength;
				}else{
					$length = $_POST["length"];					
				}
			}else{
				$length = $defaultlength;				
			}
		}
		
		if($minid == null || $minid == "null"){
			$minid = 0;
		}else{
			 if(isset($_POST["minid"])){
				if($_POST["minid"] == null || $_POST["minid"]=="null"){
					$minid = $defaultminid;
				}else{
					$minid = $_POST["minid"];
				}
			}else{
				$minid = $defaultminid;
			}
		}
		
		$set = new Set();
		$tdr= [];
		$trr = [];
		$lastdata = 0;
		$lastdatatemp = 0;
		$ownerclause = "";
		$orderby = "invreserve.wonum asc limit $minid,$length";
		if($searchstr == "all"){
			$TableSet = $set->MboSetRemote("invreserve")
			->setRelationship("workorder", "invreserve.wonum = workorder.wonum")
			->setRelationship("item", "item.itemnum = invreserve.itemnum")
			->setRelationship("invbalance", "invbalance.itemnum = invreserve.itemnum and invbalance.warehouse=invreserve.warehouse and 
			(invbalance.racknum = invreserve.racknum
			or isnull(invreserve.racknum)) and 
			(invbalance.conditiontype = invreserve.conditiontype
			or isnull(invreserve.conditiontype))")
			->order("$orderby")
			->MboRemote("invreserve.wonum")->MboRemote("workorder.description as wodesc")->MboRemote("invreserve.itemnum")->MboRemote("item.description as itemdesc")
			->MboRemote("invreserve.quantity")->MboRemote("invreserve.warehouse")->MboRemote("invreserve.racknum")->MboRemote("invreserve.conditiontype")
			->MboRemote("invreserve.enterby")->MboRemote("invreserve.enterdate")->MboRemote("invreserve.invreserveid")
			->MboRemote("workorder.assetnum")->MboRemote("workorder.location")->MboRemote("workorder.owner")->MboRemote("workorder.customercode")
			->MboRemote("invbalance.curbal")->MboRemote("invreserve.mrnum")->MboRemote("item.issueunit")
			->MboRemote("invreserve.invreservenum")
			->getMboSet();
		}else{
			$TableSet = $set->MboSetRemote("invreserve")
			->setWhere($whereclause)
			->setRelationship("workorder", "invreserve.wonum = workorder.wonum")
			->setRelationship("item", "item.itemnum = invreserve.itemnum")
			->setRelationship("invbalance", "invbalance.itemnum = invreserve.itemnum and invbalance.warehouse=invreserve.warehouse and 
			(invbalance.racknum = invreserve.racknum
			or isnull(invreserve.racknum)) and 
			(invbalance.conditiontype = invreserve.conditiontype
			or isnull(invreserve.conditiontype))")
			->order("$orderby")
			->MboRemote("invreserve.wonum")->MboRemote("workorder.description as wodesc")->MboRemote("invreserve.itemnum")->MboRemote("item.description as itemdesc")
			->MboRemote("invreserve.quantity")->MboRemote("invreserve.warehouse")->MboRemote("invreserve.racknum")->MboRemote("invreserve.conditiontype")
			->MboRemote("invreserve.enterby")->MboRemote("invreserve.enterdate")->MboRemote("invreserve.invreserveid")
			->MboRemote("workorder.assetnum")->MboRemote("workorder.location")->MboRemote("workorder.owner")->MboRemote("workorder.customercode")
			->MboRemote("invbalance.curbal")->MboRemote("invreserve.mrnum")->MboRemote("item.issueunit")
			->MboRemote("invreserve.invreservenum")
			->getMboSet();
			
		}
		
		$count = $TableSet->counts();
		
		
		for($i=0; $i<$count; $i++){
			$obj_row = new stdClass();
			$tdr = [];
			$table = $TableSet->getMbo();
			$obj_row->wonum = $table->getString("wonum");
			$obj_row->mrnum = $table->getString("mrnum");
			$obj_row->wodesc = $table->getString("wodesc");
			$obj_row->itemnum = $table->getString("itemnum");
			$obj_row->itemdesc = $table->getString("itemdesc");
			$obj_row->quantity = $table->getString("quantity");
			$obj_row->curbal = $table->getString("curbal");
			$obj_row->warehouse = $table->getString("warehouse");
			$obj_row->racknum = $table->getString("racknum");
			$obj_row->conditiontype = $table->getString("conditiontype");
			$obj_row->enterby = $table->getString("enterby");
			$obj_row->enterdate = $table->getString("enterdate");
			$obj_row->invreserveid = $table->getString("invreserveid");
			$obj_row->assetnum = $table->getString("assetnum");
			$obj_row->location = $table->getString("location");
			$obj_row->owner = $table->getString("owner");
			$obj_row->customercode = $table->getString("customercode");
			$obj_row->issueunit = $table->getString("issueunit");
			$obj_row->invreservenum = $table->getString("invreservenum");
			array_push($trr, $obj_row);
		}
		
		if($searchstr == "all"){
			$set = new Set();
			$TableSet  =  $set->MboSetRemote("invreserve")
			->setRelationship("workorder", "invreserve.wonum = workorder.wonum")
			->setRelationship("item", "item.itemnum = invreserve.itemnum")
			->MboRemote("count(invreserveid) totaldata")->getMboSet();
			$table = $TableSet->getMbo();
			$totaldata = $table->getString("totaldata");
			if($totaldata == "") $totaldata = 0;
			
		}else{
			$set = new Set();
			$TableSet  =  $set->MboSetRemote("invreserve")
			->setRelationship("workorder", "invreserve.wonum = workorder.wonum")
			->setRelationship("item", "item.itemnum = invreserve.itemnum")
			->setWhere($whereclause)->MboRemote("count(invreserveid) totaldata")->getMboSet();
			$table= $TableSet->getMbo();
			$totaldata = $table->getString("totaldata");
			if($totaldata == "") $totaldata = 0;
		}
		
		$data= new stdClass();
		$data->result = $trr;
		$data->minid = $minid;
		$data->totaldata = $totaldata;
		$data->lastdata = $minid+$length;
		$data->length = $length;
		$data->countdata = $count;
		$data->searchstr = $whereclause;
		
		$data->sql = $TableSet->sql;
		Set::throws($data);
	}
	public static function getFinancialPeriod(){
		$getFinancialPeriod = $_POST["getFinancialPeriod"];
		$orgcode = $_POST["orgcode"];
		$set = new Set();
		$invSet = $set->MboSetRemote("invfifolifocost")->setWhere("orgcode='$orgcode'")->group("financialperiod")
		->MboRemote("financialperiod")
		->getMboSet();
		$rr = [];
		$cnt = $invSet->counts();
		for($i=0; $i<$cnt; $i++){
			$inv = $invSet->getMbo();
			$financialperiod = $inv->getString("financialperiod");
			array_push($rr, $financialperiod);
		}
		
			
		$ss = new stdClass;
		$ss->financialperiods = $rr;
		$ss->totaldata = $cnt;
		$ss->minid = $minid;
		Set::throws($ss);
	}
	public static function getOtherInfo(){
		$itemnum = $_POST["getOtherInfo"];
		$warehouse = $_POST["warehouse"];
		$racknum = $_POST["racknum"];
		$batchnum = $_POST["batchnum"];
		$costdate = $_POST["costdate"];
		$orgcode = $_POST["orgcode"];
		
		$rackval = Xbo::RackWhereClause($racknum);
		$wherecostdate =  Super::whereDate("costdate", $costdate);	
		
		$whereclause = "invfifolifocost.itemnum='$itemnum' and warehouse = '$warehouse' $wherecostdate $rackval"; 
		$set = new Set();
		$invfifolifocostSet = $set->MboSetRemote("invfifolifocost")->setRelationship("item", "item.itemnum = invfifolifocost.itemnum")
		->setWhere($whereclause)
		->MboRemote("invfifolifocost.quantity")->MboRemote("item.description")->MboRemote("invfifolifocost.invfifolifocostid")
		->MboRemote("item.issueunit")
		->getMboSet();
		
		$inv = $invfifolifocostSet->getMbo();
		$quantity = $inv->getString("quantity");
		$description = $inv->getString("description");
		$issueunit = $inv->getString("issueunit");
		$invfifolifocostid = $inv->getString("invfifolifocostid");
		$ss = new stdClass;
		$ss->quantity = $quantity;
		$ss->description = $description;
		$ss->invfifolifocostid = $invfifolifocostid;
		$ss->whereclause = $whereclause;
		$ss->issueunit = $issueunit;
		Set::throws($ss);
		
		
	}
	public static function getRacks($minid = null, $length = null){
		$defaultlength = 10;
		$defaultminid = 1;
		$whereclause = null;
		$searchstr = $_POST["getRacks"];
		$orgcode = $_POST["orgcode"];
		
		if($searchstr == "all"){
			$whereclause = "";
		}else{
			$ss = json_decode($searchstr);
			$whereclause = $searchstr;
		}
		if($length == null || $length == "null") { 
			$length = $defaultlength;
		}else{
			if(isset($_POST["length"])){
				if($_POST["length"] == null || $_POST["length"]=="null"){
					$length = $defaultlength;
				}else{
					$length = $_POST["length"];					
				}
			}else{
				$length = $defaultlength;				
			}
		}
		
		if($minid == null || $minid == "null"){
			$minid = 0;
		}else{
			 if(isset($_POST["minid"])){
				if($_POST["minid"] == null || $_POST["minid"]=="null"){
					$minid = $defaultminid;
				}else{
					$minid = $_POST["minid"];
				}
			}else{
				$minid = $defaultminid;
			}
		}
		
		$set = new Set();
		$tdr= [];
		$trr = [];
		$lastdata = 0;
		$lastdatatemp = 0;
		$ownerclause = "warehouse.orgcode like '%$orgcode%'";
		$orderby = "warehouseline.warehouse asc limit $minid,$length";
		if($searchstr == "all"){
			$TableSet = $set->MboSetRemote("warehouseline")->setRelationship("warehouse", "warehouse.warehouse = warehouseline.warehouse")
			->setWhere($ownerclause)
			->order("$orderby")
			->MboRemote("warehouseline.warehouse")->MboRemote("warehouseline.racknum")->MboRemote("warehouseline.description")->MboRemote("warehouseline.racktype")->MboRemote("warehouseline.warehouselineid")
			->getMboSet();
		}else{
			$TableSet = $set->MboSetRemote("warehouseline")->setRelationship("warehouse", "warehouse.warehouse = warehouseline.warehouse")
			->setWhere($whereclause. " and $ownerclause")
			->order("$orderby")
			->MboRemote("warehouseline.warehouse")->MboRemote("warehouseline.racknum")->MboRemote("warehouseline.description")->MboRemote("warehouseline.racktype")->MboRemote("warehouseline.warehouselineid")
			->getMboSet();
		}
		
		$count = $TableSet->counts();
		
		
		for($i=0; $i<$count; $i++){
			$obj_row = new stdClass();
			$tdr = [];
			$table = $TableSet->getMbo();
			$obj_row->warehouse = $table->getString("warehouse");
			$obj_row->racknum = $table->getString("racknum");
			$obj_row->racktype = $table->getString("racktype");
			$obj_row->description = $table->getString("description");
			$obj_row->warehouselineid = $table->getString("warehouselineid");
			array_push($trr, $obj_row);
		}
		
		if($searchstr == "all"){
			$set = new Set();
			$TableSet2  =  $set->MboSetRemote("warehouseline")
			->setRelationship("warehouse", "warehouse.warehouse = warehouseline.warehouse")
			->setWhere($ownerclause)->MboRemote("count(warehouselineid) totaldata")->getMboSet();
			$table = $TableSet2->getMbo();
			$totaldata = $table->getString("totaldata");
			if($totaldata == "") $totaldata = 0;
			
		}else{
			$set = new Set();
			$TableSet2  =  $set->MboSetRemote("warehouseline")
			->setRelationship("warehouse", "warehouse.warehouse = warehouseline.warehouse")
			->setWhere($whereclause. " and $ownerclause")->MboRemote("count(warehouselineid) totaldata")->getMboSet();
			$table= $TableSet2->getMbo();
			$totaldata = $table->getString("totaldata");
			if($totaldata == "") $totaldata = 0;
		}
		
		$data= new stdClass();
		$data->result = $trr;
		$data->minid = $minid;
		$data->totaldata = $totaldata;
		$data->lastdata = $minid+$length;
		$data->length = $length;
		$data->countdata = $count;
		$data->searchstr = $TableSet->sql;
		$data->orgcode = $orgcode;
		Set::throws($data);
	}
	
	public static function getIssueType($minid = null,  $length = null){
		$defaultlength = 10;
		$defaultminid = 1;
		$whereclause = null;
		$searchstr = $_POST["getIssueType"];
		
		if($searchstr == "all"){
			$whereclause = "";
		}else{
			$ss = json_decode($searchstr);
			$whereclause = $searchstr;
		}
		if($length == null || $length == "null") { 
			$length = $defaultlength;
		}else{
			if(isset($_POST["length"])){
				if($_POST["length"] == null || $_POST["length"]=="null"){
					$length = $defaultlength;
				}else{
					$length = $_POST["length"];					
				}
			}else{
				$length = $defaultlength;				
			}
		}
		
		if($minid == null || $minid == "null"){
			$minid = 0;
		}else{
			 if(isset($_POST["minid"])){
				if($_POST["minid"] == null || $_POST["minid"]=="null"){
					$minid = $defaultminid;
				}else{
					$minid = $_POST["minid"];
				}
			}else{
				$minid = $defaultminid;
			}
		}
		
		$set = new Set();
		$tdr= [];
		$trr = [];
		$lastdata = 0;
		$lastdatatemp = 0;
		$ownerclause = "mxsattrvalue.name='issuetype'";
		$orderby = "mxsattrvalue.rootvalue asc limit $minid,$length";
		if($searchstr == "all"){
			$TableSet = $set->MboSetRemote("mxsattrvalue")
			->setWhere($ownerclause)
			->order("$orderby")
			->MboRemote("mxsattrvalue.rootvalue")->MboRemote("mxsattrvalue.remarks")->MboRemote("mxsattrvalue.mxsattrvalueid")
			->getMboSet();
		}else{
			$TableSet = $set->MboSetRemote("mxsattrvalue")
			->setWhere($whereclause." and ".$ownerclause)
			->order("$orderby")
			->MboRemote("mxsattrvalue.rootvalue")->MboRemote("mxsattrvalue.remarks")->MboRemote("mxsattrvalue.mxsattrvalueid")
			->getMboSet();
		}
		
		$count = $TableSet->counts();
		
		
		for($i=0; $i<$count; $i++){
			$obj_row = new stdClass();
			$tdr = [];
			$table = $TableSet->getMbo();
			$obj_row->rootvalue = $table->getString("rootvalue");
			$obj_row->remarks = $table->getString("remarks");
			$obj_row->mxsattrvalueid = $table->getString("mxsattrvalueid");
			
			array_push($trr, $obj_row);
		}
		
		if($searchstr == "all"){
			$set = new Set();
			$TableSet  =  $set->MboSetRemote("mxsattrvalue")
			->setWhere($ownerclause)
			->MboRemote("count(mxsattrvalueid) totaldata")->getMboSet();
			$table = $TableSet->getMbo();
			$totaldata = $table->getString("totaldata");
			if($totaldata == "") $totaldata = 0;
			
		}else{
			$set = new Set();
			$TableSet  =  $set->MboSetRemote("mxsattrvalue")->setWhere($whereclause." and ".$ownerclause)->MboRemote("count(mxsattrvalueid) totaldata")->getMboSet();
			$table= $TableSet->getMbo();
			$totaldata = $table->getString("totaldata");
			if($totaldata == "") $totaldata = 0;
		}
		
		$data= new stdClass();
		$data->result = $trr;
		$data->minid = $minid;
		$data->totaldata = $totaldata;
		$data->lastdata = $minid+$length;
		$data->length = $length;
		$data->countdata = $count;
		$data->searchstr = $whereclause;
		$data->orgcode = $orgcode;
		Set::throws($data);
		
	}
	public static function getMeasureUnit($minid = null,  $length = null){
		$defaultlength = 10;
		$defaultminid = 1;
		$whereclause = null;
		$searchstr = $_POST["getMeasureUnit"];
		$orgcode = $_POST["orgcode"];
		
		if($searchstr == "all"){
			$whereclause = "orgcode='$orgcode'";
		}else{
			$ss = json_decode($searchstr);
			$whereclause = $searchstr. " and orgcode='$orgcode'";
		}
		if($length == null || $length == "null") { 
			$length = $defaultlength;
		}else{
			if(isset($_POST["length"])){
				if($_POST["length"] == null || $_POST["length"]=="null"){
					$length = $defaultlength;
				}else{
					$length = $_POST["length"];					
				}
			}else{
				$length = $defaultlength;				
			}
		}
		
		if($minid == null || $minid == "null"){
			$minid = 0;
		}else{
			 if(isset($_POST["minid"])){
				if($_POST["minid"] == null || $_POST["minid"]=="null"){
					$minid = $defaultminid;
				}else{
					$minid = $_POST["minid"];
				}
			}else{
				$minid = $defaultminid;
			}
		}
		
		$set = new Set();
		$tdr= [];
		$trr = [];
		$lastdata = 0;
		$lastdatatemp = 0;
		$ownerclause = "";
		$orderby = "measureunit.measureunitcode asc limit $minid,$length";
		if($searchstr == "all"){
			$TableSet = $set->MboSetRemote("measureunit")
			->setWhere($whereclause)
			->order("$orderby")
			->MboRemote("measureunit.measureunitcode")->MboRemote("measureunit.description")->MboRemote("measureunit.measureunitid")
			->getMboSet();
		}else{
			$TableSet = $set->MboSetRemote("measureunit")
			->setWhere($whereclause)
			->order("$orderby")
			->MboRemote("measureunit.measureunitcode")->MboRemote("measureunit.description")->MboRemote("measureunit.measureunitid")
			->getMboSet();
		}
		
		$SQLQuery = $TableSet->sql;
		$count = $TableSet->counts();
		
		
		for($i=0; $i<$count; $i++){
			$obj_row = new stdClass();
			$tdr = [];
			$table = $TableSet->getMbo();
			$obj_row->measureunitcode = $table->getString("measureunitcode");
			$obj_row->description = $table->getString("description");
			$obj_row->measureunitid = $table->getString("measureunitid");
			
			array_push($trr, $obj_row);
		}
		
		if($searchstr == "all"){
			$set = new Set();
			$TableSet  =  $set->MboSetRemote("measureunit")->MboRemote("count(measureunitid) totaldata")->getMboSet();
			$table = $TableSet->getMbo();
			$totaldata = $table->getString("totaldata");
			if($totaldata == "") $totaldata = 0;
			
		}else{
			$set = new Set();
			$TableSet  =  $set->MboSetRemote("measureunit")->setWhere($whereclause)->MboRemote("count(measureunitid) totaldata")->getMboSet();
			$table= $TableSet->getMbo();
			$totaldata = $table->getString("totaldata");
			if($totaldata == "") $totaldata = 0;
		}
		
		$data= new stdClass();
		$data->result = $trr;
		$data->minid = $minid;
		$data->totaldata = $totaldata;
		$data->lastdata = $minid+$length;
		$data->length = $length;
		$data->countdata = $count;
		$data->searchstr = $whereclause;
		$data->orgcode = $orgcode;
		$data->sqlQuery = $SQLQuery;
		Set::throws($data);
	}
	

	
	public static function getVendors($minid = null, $length = null){
		$defaultlength = 10;
		$defaultminid = 1;
		$whereclause = null;
		$searchstr = $_POST["getVendors"];
		$orgcode = $_POST["orgcode"];
		
		if($searchstr == "all"){
			$whereclause = "orgcode='$orgcode'";
		}else{
			$ss = json_decode($searchstr);
			$whereclause = $searchstr." and orgcode='$orgcode'";
		}
		if($length == null || $length == "null") { 
			$length = $defaultlength;
		}else{
			if(isset($_POST["length"])){
				if($_POST["length"] == null || $_POST["length"]=="null"){
					$length = $defaultlength;
				}else{
					$length = $_POST["length"];					
				}
			}else{
				$length = $defaultlength;				
			}
		}
		
		if($minid == null || $minid == "null"){
			$minid = 0;
		}else{
			 if(isset($_POST["minid"])){
				if($_POST["minid"] == null || $_POST["minid"]=="null"){
					$minid = $defaultminid;
				}else{
					$minid = $_POST["minid"];
				}
			}else{
				$minid = $defaultminid;
			}
		}
		
		
		$set = new Set();
		$tdr= [];
		$trr = [];
		$lastdata = 0;
		$lastdatatemp = 0;
		//$ownerclause = "orgcode like '%$orgcode%' or orgcode is null";
		$ownerclause = $whereclause;
		$orderby = "vendor.vendorcode asc limit $minid,$length";
		$set = new Set();
		if($searchstr == "all"){
			$TableSet = $set->MboSetRemote("vendor")
			->setWhere($ownerclause)
			->order("$orderby")
			->MboRemote("vendor.vendorcode")
			->MboRemote("vendor.name")
			->MboRemote("vendor.contact")
			->MboRemote("vendor.address")
			->MboRemote("vendor.city")
			->MboRemote("vendor.stateprovince")
			->MboRemote("vendor.phone")
			->MboRemote("vendor.zippostalcode")
			->getMboSet();
		}else{
			$TableSet = $set->MboSetRemote("vendor")
			->setWhere($ownerclause)
			->order("$orderby")
			->MboRemote("vendor.vendorcode")
			->MboRemote("vendor.name")
			->MboRemote("vendor.contact")
			->MboRemote("vendor.address")
			->MboRemote("vendor.city")
			->MboRemote("vendor.stateprovince")
			->MboRemote("vendor.phone")
			->MboRemote("vendor.zippostalcode")
			->getMboSet();
		}
		$count = $TableSet->counts();
		for($i=0; $i<$count; $i++){
			$obj_row = new stdClass();
			$tdr = [];
			$table = $TableSet->getMbo();
			$obj_row->vendorid = $table->getString("vendorcode");
			$obj_row->vendorname = $table->getString("name");
			$obj_row->contact = $table->getString("contact");
			$obj_row->phone = $table->getString("phone");
			$obj_row->address = $table->getString("address");
			$obj_row->province = $table->getString("stateprovince");
			$obj_row->city = $table->getString("city");
			$obj_row->postalcode = $table->getString("zippostalcode");
			array_push($trr, $obj_row);
		}
		
		if($searchstr == "all"){
			$set = new Set();
			$TableSet  =  $set->MboSetRemote("vendor")->setWhere($ownerclause)->MboRemote("count(vendorid) totaldata")->getMboSet();
			$table = $TableSet->getMbo();
			$totaldata = $table->getString("totaldata");
			if($totaldata == null) $totaldata = 0;
			
		}else{
			$set = new Set();
			$TableSet  =  $set->MboSetRemote("vendor")->setWhere($whereclause. " and $ownerclause")->MboRemote("count(vendorid) totaldata")->getMboSet();
			$table= $TableSet->getMbo();
			$totaldata = $table->getString("totaldata");
			if($totaldata == null) $totaldata = 0;
		}
		
		
		$data= new stdClass();
		$data->result = $trr;
		$data->minid = $minid;
		$data->totaldata = $totaldata;
		$data->lastdata = $minid+$length;
		$data->length = $length;
		$data->countdata = $count;
		$data->searchstr = $whereclause;
		$data->orgcode = $orgcode;
		Set::throws($data);
	}
	public static function getWarehouse($minid = null, $length = null){
		$defaultlength = 10;
		$defaultminid = 1;
		$whereclause = null;
		$searchstr = $_POST["getWarehouse"];
		$orgcode = $_POST["orgcode"];
		
		if($searchstr == "all"){
			$whereclause = "";
		}else{
			$ss = json_decode($searchstr);
			$whereclause = $searchstr;
		}
		if($length == null || $length == "null") { 
			$length = $defaultlength;
		}else{
			if(isset($_POST["length"])){
				if($_POST["length"] == null || $_POST["length"]=="null"){
					$length = $defaultlength;
				}else{
					$length = $_POST["length"];					
				}
			}else{
				$length = $defaultlength;				
			}
		}
		
		if($minid == null || $minid == "null"){
			$minid = 0;
		}else{
			 if(isset($_POST["minid"])){
				if($_POST["minid"] == null || $_POST["minid"]=="null"){
					$minid = $defaultminid;
				}else{
					$minid = $_POST["minid"];
				}
			}else{
				$minid = $defaultminid;
			}
		}
		
		
		$tdr= [];
		$trr = [];
		$lastdata = 0;
		$lastdatatemp = 0;
		$ownerclause = "orgcode like '%$orgcode%'";
		$orderby = "warehouse.warehouse asc limit $minid,$length";
		$set = new Set();
		if($searchstr == "all"){
			$TableSet = $set->MboSetRemote("warehouse")
			->setWhere($ownerclause)
			->order("$orderby")
			->MboRemote("warehouse.warehouse")->MboRemote("warehouse.description")->MboRemote("warehouse.warehouseid")
			->getMboSet();
		}else{
			$TableSet = $set->MboSetRemote("warehouse")
			->setWhere($whereclause. " and $ownerclause")
			->order("$orderby")
			->MboRemote("warehouse.warehouse")->MboRemote("warehouse.description")->MboRemote("warehouse.warehouseid")
			->getMboSet();
		}
		$count = $TableSet->counts();
		for($i=0; $i<$count; $i++){
			$obj_row = new stdClass();
			$tdr = [];
			$table = $TableSet->getMbo();
			$obj_row->warehouse = $table->getString("warehouse");
			$obj_row->description = $table->getString("description");
			$obj_row->warehouseid = $table->getString("warehouseid");
			
			array_push($trr, $obj_row);
		}
		
		if($searchstr == "all"){
			$set = new Set();
			$TableSet  =  $set->MboSetRemote("warehouse")->setWhere($ownerclause)->MboRemote("count(warehouseid) totaldata")->getMboSet();
			$table = $TableSet->getMbo();
			$totaldata = $table->getString("totaldata");
			if($totaldata == "") $totaldata = 0;
			
		}else{
			$set = new Set();
			$TableSet  =  $set->MboSetRemote("warehouse")->setWhere($whereclause. " and $ownerclause")->MboRemote("count(warehouseid) totaldata")->getMboSet();
			$table= $TableSet->getMbo();
			$totaldata = $table->getString("totaldata");
			if($totaldata == "") $totaldata = 0;
		}
		
		$data= new stdClass();
		$data->result = $trr;
		$data->minid = $minid;
		$data->totaldata = $totaldata;
		$data->lastdata = $minid+$length;
		$data->length = $length;
		$data->countdata = $count;
		$data->searchstr = $whereclause;
		$data->orgcode = $orgcode;
		Set::throws($data);
	}
	public static function getStaffs($minid = null, $length = null){
		$defaultlength = 10;
		$defaultminid = 1;
		$whereclause = null;
		$searchstr = $_POST["getStaffs"];
		$orgcode = $_POST["orgcode"];
		
		if($searchstr == "all"){
			$whereclause = "";
		}else{
			$ss = json_decode($searchstr);
			$whereclause = $searchstr;
		}
		if($length == null || $length == "null") { 
			$length = $defaultlength;
		}else{
			if(isset($_POST["length"])){
				if($_POST["length"] == null || $_POST["length"]=="null"){
					$length = $defaultlength;
				}else{
					$length = $_POST["length"];					
				}
			}else{
				$length = $defaultlength;				
			}
		}
		
		if($minid == null || $minid == "null"){
			$minid = 0;
		}else{
			 if(isset($_POST["minid"])){
				if($_POST["minid"] == null || $_POST["minid"]=="null"){
					$minid = $defaultminid;
				}else{
					$minid = $_POST["minid"];
				}
			}else{
				$minid = $defaultminid;
			}
		}
		
		$set = new Set();
		$tdr= [];
		$trr = [];
		$lastdata = 0;
		$lastdatatemp = 0;
		$ownerclause = "orgcode like '%$orgcode%'";
		$orderby = "employees.personid asc limit $minid,$length";
		$set = new Set();
		if($searchstr == "all"){
			$TableSet = $set->MboSetRemote("employees")
			->setWhere($ownerclause)
			->order("$orderby")
			->MboRemote("employees.personid")->MboRemote("employees.fullname")->MboRemote("employees.primaryphone")->MboRemote("employees.title")
			->MboRemote("employees.department")->MboRemote("employees.employeesid")
			->getMboSet();
		}else{
			$TableSet = $set->MboSetRemote("employees")
			->setWhere($whereclause. " and $ownerclause")
			->order("$orderby")
			->MboRemote("employees.personid")->MboRemote("employees.fullname")->MboRemote("employees.primaryphone")->MboRemote("employees.title")
			->MboRemote("employees.department")->MboRemote("employees.employeesid")
			->getMboSet();
		}
		
		$count = $TableSet->counts();
		
		
		for($i=0; $i<$count; $i++){
			$obj_row = new stdClass();
			$tdr = [];
			$table = $TableSet->getMbo();
			$obj_row->personid = $table->getString("personid");
			$obj_row->fullname = $table->getString("fullname");
			$obj_row->primaryphone = $table->getString("primaryphone");
			$obj_row->title = $table->getString("title");
			$obj_row->department = $table->getString("department");
			$obj_row->employeesid = $table->getString("employeesid");
			
			array_push($trr, $obj_row);
		}
		
		if($searchstr == "all"){
			$set = new Set();
			$TableSet  =  $set->MboSetRemote("employees")->setWhere($ownerclause)->MboRemote("count(employeesid) totaldata")->getMboSet();
			$table = $TableSet->getMbo();
			$totaldata = $table->getString("totaldata");
			if($totaldata == "") $totaldata = 0;
			
		}else{
			$set = new Set();
			$TableSet  =  $set->MboSetRemote("employees")->setWhere($whereclause. " and $ownerclause")->MboRemote("count(employeesid) totaldata")->getMboSet();
			$table= $TableSet->getMbo();
			$totaldata = $table->getString("totaldata");
			if($totaldata == "") $totaldata = 0;
		}
		
		$data= new stdClass();
		$data->result = $trr;
		$data->minid = $minid;
		$data->totaldata = $totaldata;
		$data->lastdata = $minid+$length;
		$data->length = $length;
		$data->countdata = $count;
		$data->searchstr = $whereclause;
		$data->orgcode = $orgcode;
		Set::throws($data);
		
	}
	public static function getWorkOrders($minid = null, $length = null){
		$defaultlength = 10;
		$defaultminid = 1;
		$whereclause = null;
	
		$searchstr = $_POST["getWorkOrders"];
		$orgcode = $_POST["orgcode"];
		
		if($searchstr == "all"){
			$whereclause = "";
		}else{
			$ss = json_decode($searchstr);
			$whereclause = $searchstr;
		}
		if($length == null || $length == "null") { 
			$length = $defaultlength;
		}else{
			if(isset($_POST["length"])){
				if($_POST["length"] == null || $_POST["length"]=="null"){
					$length = $defaultlength;
				}else{
					$length = $_POST["length"];					
				}
			}else{
				$length = $defaultlength;				
			}
		}
		
		if($minid == null || $minid == "null"){
			$minid = 0;
		}else{
			 if(isset($_POST["minid"])){
				if($_POST["minid"] == null || $_POST["minid"]=="null"){
					$minid = $defaultminid;
				}else{
					$minid = $_POST["minid"];
				}
			}else{
				$minid = $defaultminid;
			}
		}
		
		
		$tdr= [];
		$trr = [];
		$lastdata = 0;
		$lastdatatemp = 0;
		$ownerclause = "orgcode like '%$orgcode%'";
		$orderby = "workorder.wonum asc limit $minid,$length";
		$set = new Set();
		if($searchstr == "all"){
			$TableSet = $set->MboSetRemote("workorder")
			->setWhere($ownerclause)
			->order("$orderby")
			->MboRemote("workorder.wonum")->MboRemote("workorder.description")->MboRemote("workorder.assetnum")->MboRemote("workorder.customercode")
			->MboRemote("workorder.targstartdate")->MboRemote("workorder.targcompdate")->MboRemote("workorder.status")->MboRemote("workorder.owner")
			->MboRemote("workorder.workorderid")->MboRemote("workorder.requestedby")->MboRemote("workorder.requesteddate")
			->getMboSet();
		}else{
			$TableSet = $set->MboSetRemote("workorder")
			->setWhere($whereclause. " and $ownerclause")
			->order("$orderby")
			->MboRemote("workorder.wonum")->MboRemote("workorder.description")->MboRemote("workorder.assetnum")->MboRemote("workorder.customercode")
			->MboRemote("workorder.targstartdate")->MboRemote("workorder.targcompdate")->MboRemote("workorder.status")->MboRemote("workorder.owner")
			->MboRemote("workorder.workorderid")->MboRemote("workorder.requestedby")->MboRemote("workorder.requesteddate")
			->getMboSet();
		}
		
		$SQLQuery = $TableSet->sql;
		$count = $TableSet->counts();
		
		
		for($i=0; $i<$count; $i++){
			$obj_row = new stdClass();
			$tdr = [];
			$table = $TableSet->getMbo();
			$obj_row->wonum = $table->getString("wonum");
			$obj_row->description = $table->getString("description");
			$obj_row->assetnum = $table->getString("assetnum");
			$obj_row->customercode = $table->getString("customercode");
			$obj_row->targstartdate = $table->getString("targstartdate");
			$obj_row->targcompdate = $table->getString("targcompdate");
			$obj_row->status = $table->getString("status");
			$obj_row->owner = $table->getString("owner");
			$obj_row->workorderid = $table->getString("workorderid");
			$obj_row->requestedby = $table->getString("requestedby");
			$obj_row->requesteddate = $table->getString("requesteddate");
			array_push($trr, $obj_row);
		}
		
		if($searchstr == "all"){
			$set = new Set();
			$TableSet  =  $set->MboSetRemote("workorder")->setWhere($ownerclause)->MboRemote("count(workorderid) totaldata")->getMboSet();
			$table = $TableSet->getMbo();
			$totaldata = $table->getString("totaldata");
			if($totaldata == "") $totaldata = 0;
			
		}else{
			$set = new Set();
			$TableSet  =  $set->MboSetRemote("workorder")->setWhere($whereclause. " and $ownerclause")->MboRemote("count(workorderid) totaldata")->getMboSet();
			$table= $TableSet->getMbo();
			$totaldata = $table->getString("totaldata");
			if($totaldata == "") $totaldata = 0;
		}
		
		$data= new stdClass();
		$data->result = $trr;
		$data->minid = $minid;
		$data->totaldata = $totaldata;
		$data->lastdata = $minid+$length;
		$data->length = $length;
		$data->countdata = $count;
		$data->searchstr = $whereclause;
		$data->orgcode = $orgcode;
		$data->sql = $TableSet->sql;
		$data->SQLQuery = $SQLQuery;
		Set::throws($data);
		
	}
	public static function getInventoryFIFOLIFOData($minid = null, $length = null){
		$defaultlength = 10;
		$defaultminid = 1;
		$whereclause = null;
	
		$searchstr = $_POST["getInvFLData"];
		$orgcode = $_POST["orgcode"];
		
		if($searchstr == "all"){
			$whereclause = "";
		}else{
			$ss = json_decode($searchstr);
			$whereclause = $searchstr;
		}
		if($length == null || $length == "null") { 
			$length = $defaultlength;
		}else{
			if(isset($_POST["length"])){
				if($_POST["length"] == null || $_POST["length"]=="null"){
					$length = $defaultlength;
				}else{
					$length = $_POST["length"];					
				}
			}else{
				$length = $defaultlength;				
			}
		}
		
		if($minid == null || $minid == "null"){
			$minid = 0;
		}else{
			 if(isset($_POST["minid"])){
				if($_POST["minid"] == null || $_POST["minid"]=="null"){
					$minid = $defaultminid;
				}else{
					$minid = $_POST["minid"];
				}
			}else{
				$minid = $defaultminid;
			}
		}
		
		
		$tdr= [];
		$trr = [];
		$lastdata = 0;
		$lastdatatemp = 0;
		$ownerclause = "invfifolifocost.orgcode like '%$orgcode%'";
		$orderby = "invfifolifocost.itemnum asc limit $minid,$length";
		$set = new Set();
		if($searchstr == "all"){
			$TableSet = $set->MboSetRemote("invfifolifocost")->setRelationship("item", "item.itemnum  = invfifolifocost.itemnum")
			->setRelationship("invprice", "invprice.itemnum  = invfifolifocost.itemnum and invprice.warehouse = invfifolifocost.warehouse and invprice.conditiontype = invfifolifocost.conditiontype")		
			->setWhere($ownerclause)
			->order("$orderby")
			->MboRemote("invfifolifocost.itemnum")->MboRemote("item.description")->MboRemote("invfifolifocost.conditiontype")->MboRemote("invfifolifocost.expdate")->MboRemote("invfifolifocost.batchnum")
			->MboRemote("invfifolifocost.ponum")->MboRemote("invfifolifocost.polinenum")->MboRemote("invfifolifocost.wonum")->MboRemote("invfifolifocost.vendorcode")
			->MboRemote("invfifolifocost.financialperiod")->MboRemote("invfifolifocost.unitcost")->MboRemote("invfifolifocost.quantity")->MboRemote("invfifolifocost.conditionrate")
			->MboRemote("invfifolifocost.orgcode")->MboRemote("invfifolifocost.sitecode")->MboRemote("invfifolifocost.warehouse")->MboRemote("invfifolifocost.racknum")
			->MboRemote("invfifolifocost.transtype")->MboRemote("invfifolifocost.transdate")->MboRemote("invfifolifocost.enterdate")->MboRemote("invfifolifocost.costdate")
			->MboRemote("invfifolifocost.physqty")->MboRemote("invfifolifocost.physcountdate")->MboRemote("invfifolifocost.invfifolifocostid")
			->MboRemote("item.issueunit")->MboRemote("item.orderunit")->MboRemote("item.itemid")
			->getMboSet();
		}else{
			$TableSet = $set->MboSetRemote("invfifolifocost")->setRelationship("item", "item.itemnum  = invfifolifocost.itemnum")
			->setRelationship("invprice", "invprice.itemnum  = invfifolifocost.itemnum and invprice.warehouse = invfifolifocost.warehouse and invprice.conditiontype = invfifolifocost.conditiontype")
			->setWhere($whereclause. " and " .$ownerclause)
			->order("$orderby")
			->MboRemote("invfifolifocost.itemnum")->MboRemote("item.description")->MboRemote("invfifolifocost.conditiontype")->MboRemote("invfifolifocost.expdate")->MboRemote("invfifolifocost.batchnum")
			->MboRemote("invfifolifocost.ponum")->MboRemote("invfifolifocost.polinenum")->MboRemote("invfifolifocost.wonum")->MboRemote("invfifolifocost.vendorcode")
			->MboRemote("invfifolifocost.financialperiod")->MboRemote("invfifolifocost.unitcost")->MboRemote("invfifolifocost.quantity")->MboRemote("invfifolifocost.conditionrate")
			->MboRemote("invfifolifocost.orgcode")->MboRemote("invfifolifocost.sitecode")->MboRemote("invfifolifocost.warehouse")->MboRemote("invfifolifocost.racknum")
			->MboRemote("invfifolifocost.transtype")->MboRemote("invfifolifocost.transdate")->MboRemote("invfifolifocost.enterdate")->MboRemote("invfifolifocost.costdate")
			->MboRemote("invfifolifocost.physqty")->MboRemote("invfifolifocost.physcountdate")->MboRemote("invfifolifocost.invfifolifocostid")
			->MboRemote("item.issueunit")->MboRemote("item.orderunit")->MboRemote("item.itemid")
			->getMboSet();
		}
		
		$count = $TableSet->counts();
		
		
		
		
		$fsname = ContextPath::getFullHostName();
		$origPath = $fsname . "/attachments/";
		for($i=0; $i<$count; $i++){
			$obj_row = new stdClass();
			$tdr = [];
			$table = $TableSet->getMbo();
			$itemid = $table->getString("itemid");
			
			$setf = new Set();
			$fileinfoSet = $setf->MboSetRemote("fileinfo")
			->setWhere("fileinfo.ownerid='$itemid' and fileinfo.ownertable='item'")->MboRemote("fileinfo.ownerid")
			->MboRemote("fileinfo.description")->MboRemote("fileinfo.filename")
			->getMboSet();
			$cntFp = $fileinfoSet->counts();
			if($cntFp>0){
				for($fp=0; $fp<1; $fp++){
					$as = new stdClass;
					$fileinfo = $fileinfoSet->getMbo();
					$filename = $fileinfo->getString("filename");
					$filedesc = $fileinfo->getString("description");
					$obj_row->filepath = $origPath . $filename;
				}
			}else{
				$obj_row->filepath = "";
			}
			
		
			$obj_row->itemid = $itemid;
			$obj_row->itemnum = $table->getString("itemnum");
			$obj_row->conditiontype = $table->getString("conditiontype");
			$obj_row->expdate = $table->getString("expdate");
			$obj_row->batchnum = $table->getString("batchnum");
			$obj_row->description = $table->getString("description");
			$obj_row->ponum = $table->getString("ponum");
			$obj_row->polinenum = $table->getString("polinenum");
			$obj_row->wonum = $table->getString("wonum");
			$obj_row->vendorcode = $table->getString("vendorcode");
			$obj_row->financialperiod = $table->getString("financialperiod");
			$obj_row->unitcost = $table->getString("unitcost");
			$obj_row->quantity = $table->getString("quantity");
			$obj_row->conditionrate = $table->getString("conditionrate");
			$obj_row->orgcode = $table->getString("orgcode");
			$obj_row->sitecode = $table->getString("sitecode");
			$obj_row->warehouse = $table->getString("warehouse");
			$obj_row->racknum = $table->getString("racknum");
			$obj_row->transtype = $table->getString("transtype");
			$obj_row->transdate = $table->getString("transdate");
			$obj_row->enterdate = $table->getString("enterdate");
			$obj_row->costdate = $table->getString("costdate");
			$obj_row->physqty = $table->getString("physqty");
			$obj_row->physcountdate = $table->getString("physcountdate");
			$obj_row->invfifolifocostid = $table->getString("invfifolifocostid");
			$obj_row->issueunit = $table->getString("issueunit");
			$obj_row->orderunit = $table->getString("orderunit");
			array_push($trr, $obj_row);
		}
		
		
		if($searchstr == "all"){
			$set = new Set();
			$TableSet  =  $set->MboSetRemote("invfifolifocost")->setRelationship("item", "item.itemnum  = invfifolifocost.itemnum")
			->setWhere($ownerclause)->MboRemote("count(invfifolifocostid) totaldata")->getMboSet();
			$table = $TableSet->getMbo();
			$totaldata = $table->getString("totaldata");
			if($totaldata == "") $totaldata = 0;
			
			$set = new Set();
			$TableSet  =  $set->MboSetRemote("invfifolifocost")->setRelationship("item", "item.itemnum  = invfifolifocost.itemnum")
			->setWhere($ownerclause)->MboRemote("sum(unitcost) totalunitcost")->getMboSet();
			$table= $TableSet->getMbo();
			$totalunitcost = $table->getString("totalunitcost");
			if($totalunitcost == "") $totalunitcost = 0;
			
			
		}else{
			$set = new Set();
			$TableSet  =  $set->MboSetRemote("invfifolifocost")->setRelationship("item", "item.itemnum  = invfifolifocost.itemnum")
			->setWhere($whereclause. " and $ownerclause")->MboRemote("count(invfifolifocostid) totaldata")->getMboSet();
			$table= $TableSet->getMbo();
			$totaldata = $table->getString("totaldata");
			if($totaldata == "") $totaldata = 0;
			
			$set = new Set();
			$TableSet  =  $set->MboSetRemote("invfifolifocost")->setRelationship("item", "item.itemnum  = invfifolifocost.itemnum")
			->setWhere($whereclause. " and $ownerclause")->MboRemote("sum(unitcost) totalunitcost")->getMboSet();
			$table= $TableSet->getMbo();
			$totalunitcost = $table->getString("totalunitcost");
			if($totalunitcost == "") $totalunitcost = 0;
			
		}
		
		$data= new stdClass();
		$data->result = $trr;
		$data->minid = $minid;
		$data->totaldata = $totaldata;
		$data->lastdata = $minid+$length;
		$data->length = $length;
		$data->countdata = $count;
		$data->totalunitcost = $totalunitcost;
		$data->searchstr = $whereclause;
		$data->orgcode = $orgcode;
		Set::throws($data);
		
	}
}


?>