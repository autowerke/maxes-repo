<?php
require_once ("../../../eat.php");

Class ActionSet {
	public static function PriceChange(){
		$fleetroutenum = $_POST["fleetroutenum"];
		$pricechange = $_POST["pricechange"];
		$percent = $_POST["valuechange"];
		$roundup = $_POST["roundup"];
		//"goesdown"
		//"raiseup"
		$set = new Set();
		$fleetroutepriceSet = $set->MboSetRemote("fleetrouteprice")->setWhere("fleetroutenum='$fleetroutenum'")->MboRemote("price")->MboRemote("fleetroutepriceid")->getMboSet();
		$cnt = $fleetroutepriceSet->counts();
		
		for($i=0; $i<$cnt; $i++){
			$fleetrouteprice = $fleetroutepriceSet->getMbo();
			$oldprice = $fleetrouteprice->getString("price");
			$fleetroutepriceid = $fleetrouteprice->getString("fleetroutepriceid");
			
			if(empty($oldprice)){
				$oldprice = 0;
			}
			$pricepercent = $oldprice*($percent/100);
			
			if($pricechange == "goesdown"){
				$newprice = $oldprice-$pricepercent;
			}else{
				if($pricechange == "raiseup"){
					$newprice = $oldprice+$pricepercent;
				}
			}
			if($roundup == "Y"){
				$newprice = floor($newprice);
				$newprice = substr($newprice, 0, -3) . str_repeat('0', 3);
			}else{
				$newprice = $newprice;
			}
			
			$set2 = new Set();
			$set2->MboSetRemote("fleetrouteprice")->setWhere("fleetroutepriceid='$fleetroutepriceid'")->setValue("price", $newprice)->change();
		}
		
		$ob = new stdClass();
		$ob->msg = "ok";
		Set::throws($ob);
	}
	
	public static function AddLocations(){
		$fleetroutenum = $_POST["addlocation"];
		$cntlocs = count($_POST["addlocations"]);
		for($i=0; $i<$cntlocs; $i++){
			$set = new Set();
			$id = explode("`", $_POST["addlocations"][$i])[1];
			
			$locationsSet = $set->MboSetRemote("locations")->setWhere("locationsid='$id'")->MboRemote("locnum")->getMboSet();
			$locations = $locationsSet->getMbo();
			$locnum = $locations->getString("locnum");
			
			$setChk = new Set();
			$setChk->MboSetRemote("fleetroutelocations")->setWhere("locnum = '$locnum' and fleetroutenum='$fleetroutenum'")->MboRemote("locnum")->getMboSet();
			$cnt = $setChk->counts();
			
			if($cnt==0){
				$fleetroutelocnum = Xbo::getLineNumber("fleetroutelocations", "fleetroutelocnum", "fleetroutenum='$fleetroutenum'");
				$set2 = new Set();
				$set2->MboSetRemote("fleetroutelocations")
				->setValue("fleetroutenum", $fleetroutenum)
				->setValue("fleetroutelocnum", $fleetroutelocnum)
				->setValue("locnum", $locnum)
				->add();
			}
			
		}
		
		$ob = new stdClass();
		$ob->cnt = $cntlocs;
		Set::throws($ob);
		
	}
	public static function AddFleets(){
		$fleetroutenum = $_POST["addfleet"];
		$cntfleets = count($_POST["addfleets"]);
		$enterdate = Xbo::setEnterDate();
		$enterby = Xbo::getLoginUser();
		$changeby = $enterby;
		$changedate = $enterdate;
		
		$set3 = new Set();
		$assetseatSet = $set3->MboSetRemote("assetseat")->MboRemote("seattype")->MboRemote("seatcode")->getMboSet();
		$cntSeats = $assetseatSet->counts();
				
		for($j=0; $j<$cntSeats; $j++){
			$assetseat = $assetseatSet->getMbo();
			$seattype = $assetseat->getString("seattype");
			$seatcode = $assetseat->getString("seatcode");
			$set4 = new Set();
			$fleetroutepriceSet = $set4->MboSetRemote("fleetprice")->setWhere("seattype='$seattype' and fleetroutenum='$fleetroutenum'")->MboRemote("seattype")->getMboSet();
			$frpCnt = $fleetroutepriceSet->counts();
			if($frpCnt == 0){
				$set5 = new Set();
				$set5->MboSetRemote("fleetprice")
				->setValue("fleetroutenum", $fleetroutenum)
				->setValue("seatcode", $seatcode)
				->setValue("seattype", $seattype)
				->add();
			}
			
		}
				
				
		
		for($i=0; $i<$cntfleets; $i++){
			$set = new Set();
			$id = explode("`", $_POST["addfleets"][$i])[1];
				
			$assetfleetsSet = $set->MboSetRemote("assets")->setWhere("assetsid='$id'")->MboRemote("assetnum")->MboRemote("description")->getMboSet();
			$assetfleets = $assetfleetsSet->getMbo();
			$assetnum = $assetfleets->getString("assetnum");
			
			$setChk = new Set();
			$setChk->MboSetRemote("fleetrouteline")->setWhere("assetnum='$assetnum' and fleetroutenum='$fleetroutenum'")->MboRemote("assetnum")->getMboSet();
			$cnt = $setChk->counts();
			
			if($cnt==0){
				$description = $assetfleets->getString("description");
				$fleetroutelinenum = Xbo::getLineNumber("fleetrouteline", "fleetroutelinenum", "fleetroutenum='$fleetroutenum'");
				$set2 = new Set();
				$set2->MboSetRemote("fleetrouteline")
				->setValue("fleetroutenum", $fleetroutenum)
				->setValue("fleetroutelinenum", $fleetroutelinenum)
				->setValue("assetnum", $assetnum)
				->setValue("description", $description)
				->setValue("enterby", $enterby)
				->setValue("enterdate", $enterdate)
				->setValue("changeby", $changeby)
				->setValue("changedate", $changedate)
				->add();
				
				$set3 = new Set();
				$assetseatSet = $set3->MboSetRemote("assetseat")->MboRemote("seattype")->MboRemote("seatcode")->getMboSet();
				$cntSeats = $assetseatSet->counts();
				
				for($j=0; $j<$cntSeats; $j++){
					$assetseat = $assetseatSet->getMbo();
					$seattype = $assetseat->getString("seattype");
					$seatcode = $assetseat->getString("seatcode");
					
					$set4 = new Set();
					$fleetroutepriceSet = $set4->MboSetRemote("fleetrouteprice")->setWhere("seattype='$seattype' and assetnum='$assetnum' and fleetroutenum='$fleetroutenum'")->MboRemote("seattype")->getMboSet();
					$frpCnt = $fleetroutepriceSet->counts();
					if($frpCnt == 0){
						$fleetroutepricenum = Xbo::getLineNumber("fleetrouteprice", "fleetroutepricenum", "fleetroutenum='$fleetroutenum'");
						$set5 = new Set();
						$set5->MboSetRemote("fleetrouteprice")
						->setValue("fleetroutenum", $fleetroutenum)
						->setValue("fleetroutepricenum", $fleetroutepricenum)
						->setValue("assetnum", $assetnum)
						->setValue("seatcode", $seatcode)
						->setValue("seattype", $seattype)
						->setValue("enterby", $enterby)
						->setValue("enterdate", $enterdate)
						->setValue("changeby", $changeby)
						->setValue("changedate", $changedate)
						->add();
					}
					
				}
				
				$set4 = new Set();
				$PackTypeSet = $set4->MboSetRemote("packtype")->MboRemote("packtype")->getMboSet();
				$cntPack = $PackTypeSet->counts();
				
				for($j=0; $j<$cntPack; $j++){
					$PackType = $PackTypeSet->getMbo();
					$packtype = $PackType->getString("packtype");
					
					$set5 = new Set();
					$fleetrouteprice2Set = $set5->MboSetRemote("fleetrouteprice2")->setWhere("packtype='$packtype' and assetnum='$assetnum' and fleetroutenum='$fleetroutenum'")->MboRemote("packtype")->getMboSet();
					$frp2Cnt = $fleetrouteprice2Set->counts();
					if($frp2Cnt == 0){
						$fleetrouteprice2num = Xbo::getLineNumber("fleetrouteprice2", "fleetrouteprice2num", "fleetroutenum='$fleetroutenum'");
						$set6 = new Set();
						$set6->MboSetRemote("fleetrouteprice2")
						->setValue("fleetroutenum", $fleetroutenum)
						->setValue("fleetrouteprice2num", $fleetrouteprice2num)
						->setValue("assetnum", $assetnum)
						->setValue("packtype", $packtype)
						->setValue("enterby", $enterby)
						->setValue("enterdate", $enterdate)
						->setValue("changeby", $changeby)
						->setValue("changedate", $changedate)
						->add();
					}
					
					
				}
				
			}else{
			}
		}
		$ob = new stdClass();
		$ob->cnt = $cntfleets;
		Set::throws($ob);
	}
	
} 

Class ValidateTables {
	public static function tbcx(){
		$dl = explode("|", $_GET["tbcx"]);
		
		$ownerTable = $dl[0];
		$okp = explode("`", $dl[1]);
		$ownerKey = $okp[0];
		$ownerKeyValue = $okp[1];
		
		$childrenTable = $dl[2];
		
		$set = new Set();
		if ( $ownerTable == "fleetroute") {
			if ($childrenTable == "fleetrouteline") {
				$childrenTableParts = explode("^", $dl[3]);
				for ($i = 0; $i < count($childrenTableParts); $i++) {
					$tableid = explode("`", $childrenTableParts[$i]) [1];
					$sets = new Set();
					$assetnum = $sets->MboSetRemote("fleetrouteline")->setWhere("fleetroutenum='$ownerKeyValue' and fleetroutelineid = '$tableid'")->MboRemote("assetnum")->getMboSet()->getMbo()->getString("assetnum");
					
					$set = new Set();
					$set->MboSetRemote("fleetrouteprice")->setWhere("fleetroutenum='$ownerKeyValue' and assetnum='$assetnum'")->remove();
					$set = new Set();
					$set->MboSetRemote("fleetrouteprice2")->setWhere("fleetroutenum='$ownerKeyValue' and assetnum='$assetnum'")->remove();
					
				}
				echo json_encode(["msg" => "omdelete"]);
			}
		}
	}
}

