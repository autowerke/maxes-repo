<?php
require_once ("../../../eat.php");

Class ActionSet {
	
	public static function setExistingGL(){
		$orgcode = $_POST["setExistingGL"];
		$sitecode = $_POST["sitecode"];
		$whereclause = "orgcode='$orgcode' and sitecode='$sitecode'";
		$ss = new Set();
		$stchck = $ss->MboSetRemote("orggldefault")->setWhere($whereclause)->getMboSet();
		
		$stcnt = $stchck->counts();
		$aa = new stdClass();
		if($stcnt == 0){
			$aa->msg = "notexist";
		}else{
			$aa->msg = "exist";
			$st = $stchck->getMbo();
			$aa->vrbniglaccount = $st->getString("vrbniglaccount");
			$aa->vapglaccount =  $st->getString("vapglaccount");
			$aa->crbniglaccount =  $st->getString("crbniglaccount");
			$aa->capglacount =  $st->getString("capglacount");
			$aa->orgcode = $st->getString("orgcode");
			$aa->sitecode =  $st->getString("sitecode");
			$aa->cashonbankacct =  $st->getString("cashonbankacct");
			$aa->cashonhandacct =  $st->getString("cashonhandacct");
			
			$aa->custshrinkacct =  $st->getString("custshrinkacct");
			$aa->custpriceacct =  $st->getString("custpriceacct");
			$aa->custglaccount =  $st->getString("custglaccount");
			$aa->whcostacct =  $st->getString("whcostacct");
			$aa->whshrinkacct =  $st->getString("whshrinkacct");
			$aa->whglaccount =  $st->getString("whglaccount");
		}
		Set::throws($aa);
		
	}
	public static function AddGLDefault(){
		$vrbniglaccount = $_POST["vrbniglaccount"];
		$vapglaccount = $_POST["vapglaccount"];
		$crbniglaccount = $_POST["crbniglaccount"];
		$capglacount = $_POST["capglacount"];
		
		$cashonhandacct = $_POST["cashonhandacct"];
		$cashonbankacct = $_POST["cashonbankacct"];
		$custshrinkacct = $_POST["custshrinkacct"];
		$custpriceacct = $_POST["custpriceacct"];
		$whcostacct = $_POST["whcostacct"];
		$whshrinkacct = $_POST["whshrinkacct"];
		$whglaccount = $_POST["whglaccount"];
		$custglaccount = $_POST["custglaccount"];
		
		$orgcode = $_POST["orgcode"];
		$sitecode = $_POST["sitecode"];
		
		$arr = ["vrbniglaccount" => $vrbniglaccount, "vapglaccount"=>$vapglaccount,
				"crbniglaccount"=> $crbniglaccount, "capglacount" =>$capglacount, 
				"orgcode" =>$orgcode, "sitecode" =>$sitecode,
				"cashonhandacct" =>$cashonhandacct, "cashonbankacct" =>$cashonbankacct,
				"custshrinkacct" =>$custshrinkacct, "custpriceacct" =>$custpriceacct,
				"whshrinkacct" =>$whshrinkacct, "whcostacct" =>$whcostacct,
				"custglaccount" =>$custglaccount, "whglaccount" =>$whglaccount
				];
		$arrcount = count($arr);
		
		$whereclause = "orgcode='$orgcode' and sitecode='$sitecode'";
		$ss = new Set();
		$stchck = $ss->MboSetRemote("orggldefault")->setWhere($whereclause)->getMboSet();
		//echo $stchck->sql;
		$stcnt = $stchck->counts();
		if($stcnt == 0){
			$st = new Set();
			$stin = $st->MboSetRemote("orggldefault");
			foreach($arr as $col => $val) {
				$stin = $stin->setValue($col, $val);
			}
			
			$stin->add();
		}else{
			$st = new Set();
			$stup = $st->MboSetRemote("orggldefault")->setWhere($whereclause);
			foreach($arr as $col => $val) {
				$stup = $stup->setValue($col, $val);
			}
			$stup->change();
		}
		
		Set::throws(["msg"=>"ok"]);
		
	}
	public static function AddCapital(){
		$capital = $_POST["addcapital"];
		$orgcode = $_POST["orgcode"];
		$sitecode = $_POST["sitecode"];
		$remarks = $_POST["remarks"];
		$gldebitacct = $_POST["gldebitacct"];
		$glcreditacct = $_POST["glcreditacct"];
		
		$enterdate = Xbo::setEnterDate();
		$enterby = Xbo::getLoginUser();
		$changeby = $enterby;
		$changedate = $enterdate;
		
		$set = new Set();
		$set->MboSetRemote("orgcapital")->setValue("orgcode", $orgcode)->setValue("sitecode", $sitecode)->setValue("capital", $capital)->setValue("remarks", $remarks)
		->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)->setValue("changeby", $changeby)->setValue("changedate", $changedate)->setValue("gldebitacct", $gldebitacct)
		->setValue("glcreditacct", $glcreditacct)
		->add();
		
		Set::throws(["msg"=>"added"]);
	}
} 

Class ValidateTables {
	
}
	

?>