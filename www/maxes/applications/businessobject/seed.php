<?php
include ("seed_onetime.php");

class Seed {
	public static function SeedRoot(){
		$sr = $_POST["seedroot"];
		$parts = explode("|", $sr);
		$userid = $parts[0];
		$gendate = Xbo::getDate($parts[1]);
		$set = new Set();
		$isExist = $set->MboSetRemote("mxsusersexp")->setWhere("userid='$userid'")->MboRemote("userid")->getMboSet()->counts();
		$enterby = Xbo::getLoginUser();
		$enterdate = date("Y-m-d h:i");
		$changeby = $enterby;
		$changedate = $enterdate;
		$srStr = Xbo::setActid($sr);
		if ($isExist == 0) {
			$set->MboSetRemote("mxsusersexp")->setValue("userid", $userid)->setValue("actid", $srStr)->setValue("gendate", $gendate)->setValue("status", "wlc")
			->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)->setValue("changeby", $changeby)->setValue("changedate", $changedate)->add();
			echo $srStr;
		}
		else {
			$mxsusersexpSet = $set->MboSetRemote("mxsusersexp")->setWhere("userid='$userid'")->MboRemote("actid")->MboRemote("gendate")->MboRemote("status")->getMboSet();
			$mxsusersexp = $mxsusersexpSet->getMbo();
			$existingSR = $mxsusersexp->getString("actid");
			$existingGendate = $mxsusersexp->getString("gendate");
			$status = $mxsusersexp->getString("status");
			echo "alreadygeneratedlic^$existingSR^$existingGendate^$status";
		}
	}
	
	public static function SetActid($seedroot){
		$arrRev = Xbo::setSR($seedroot);
		$partsArr = array();
		$parts2Arr = array();
		for ($i = 0; $i < count($arrRev) / 2; $i++) {
			array_push($partsArr, $arrRev[$i]);
		}
		$srStr = implode("-", $partsArr);
		return $srStr;
	}
	
	public static function CheckSum(){
		$srparts = explode("^", $_POST["checksum"]);
		$arrRev = Xbo::setSR($srparts[0]);
		$arrRevStr = implode("-", $arrRev);
		$parts = $srparts[2] . "-" . $srparts[1];
		$msg = "";
		if ($arrRevStr == $parts) {
			$msg = "useractivated";
		}
		else {
			$msg = "userwrongserialkey";
		}
		if ($msg == "useractivated") {
			$mxsusersactid = Xbo::getMaxTableID("mxsusersact");
			$enparts = explode("|", $srparts[0]);
			$userid = $enparts[0];
			$actDateFormat = Xbo::getDate($srparts[3]);
			
			$gendateFormat = Xbo::getDate($enparts[1]);
			$actdate = strtotime($actDateFormat);
			$lic = License::OneYear();
			
			$nextdate = $actdate + $lic;
			
			$expdate = date("Y-m-d H:i", $nextdate); 
			$set = new Set();
			$countuser = $set->MboSetRemote("mxsusersact")->setWhere("activedate='$actdate' and expireddate='$expdate'")->MboRemote("count(userid) as countuser")->getMboSet()->getMbo()->getString("countuser");
			
			if ($countuser == 0) {
				$actid = $srparts[2];
				$ul = $srparts[1];
				$seedroot = $gendateFormat . "|" . $actDateFormat;
				$srr = Xbo::setSR($seedroot);
				$sr = implode("-", $srr);
				
				$set->MboSetRemote("mxsusersact")->setValue("userid", $userid)->setValue("gendate", $gendateFormat)->setValue("activedate", $actDateFormat)->setValue("expireddate", $expdate)->setValue("actid", $actid)->setValue("ul", $ul)->setValue("sr", $sr)->add();
				$set->MboSetRemote("mxsusers")->setWhere("userid='$userid'")->setValue("ischanged", 0)->setValue("status", "ACTIVE")->change();
				$set->MboSetRemote("mxsusersexp")->setWhere("userid='$userid'")->remove();
			}
			echo $msg;
		}
		if ($msg == "userwrongserialkey") {
			$set = new Set();
			echo $msg;
			$enparts = explode("|", $srparts[0]);
			$userid = $enparts[0];
			$ischanged = $set->MboSetRemote("mxsusers")->setWhere("userid='$userid'")->MboRemote("ischanged")->getMboSet()->getMbo()->getString("ischanged");
			if ($ischanged <= 3) {
				$ischanged++;
				$set->MboSetRemote("mxsusers")->setWhere("userid='$userid'")->setValue("ischanged", $ischanged)->change();
			}
			else {
				$msg = "#maximumtrycount";
				echo $msg;
				$set->MboSetRemote("mxsusers")->setWhere("status not in('LOCKED') and userid not in ('maxesadmin', 'eatadmin')")->setValue("status", "LOCKED")->change();
			}
		}
	}
	
	public static function SetSR($seedroot){
		$sr = $seedroot;
		$sr = strtoupper(md5($sr));
		$srCount = strlen($sr);
		$srArr = array();
		for ($i = 0; $i < $srCount; $i++) {
			if ($i == 0 || $i == 4 || $i == 8 || $i == 12 || $i == 16 || $i == 20 || $i == 24 || $i == 28 || $i == 32) {
				$out = substr($sr, $i, 4);
				$outrev = strrev($out);
				array_push($srArr, $outrev);
			}
		}
		$arrRev = array_reverse($srArr);
		return $arrRev;
	}
	public static function Xbor($inc=""){
		if ($inc != "") {
			$trx = "http://javarave.com/jvr/ui/common.php?pgr=" . $_GET['inc'];
			echo $trx;
		}
		else {
			$isc = UI::isConnected();
			if ($isc == "connected") {
				echo $isc;
			}
			else {
				echo $isc;
			}
		}
	}
	public static function Registered(){
		$setDec = new Super();
		$upreg = $setDec->decodeUpreg($_GET['upreg']);
		$set = new Super();
		$jwt = new JWT();
		$pl = $set->breakapart($upreg);
		$iss = $pl->pl->iss;
		$regDate = $pl->pl->iat;
		$user = $pl->pl->user;
		$roothost = $pl->pl->roothost;
		$expsoft = $pl->pl->exp;
		
		$syspath = Super::setSysPath() . "\\";
		$sysfile = $syspath . 'system.xml';
		$handle = fopen($sysfile, 'w') or die('Cannot open file:  ' . $sysfile);
		$data = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<properties>
	<regdate>" . $regDate . "</regdate>
	<reguser>" . $user . "</reguser>
	<roothost>" . $roothost . "</roothost>
	<expsupport>" . $expsoft . "</expsupport>
</properties>";
		fwrite($handle, $data);
		$ts = filectime($sysfile);
		$jwtclaims = base64_encode($jwt->headr(json_encode(["alg" => "HS256", "typ" => "JWT"]))->payload(json_encode(["iss" => $iss, "iat" => $regDate, "exp" => $expsoft, "user" => $user, "roothost" => $roothost, "ts" => $ts]))->signature("masnarendradekmaheswara")->generate());
		$jwtenc = $set->encodeUpreg($jwtclaims);
		$jwtdec = $set->decodeUpregTracking($jwtenc);
	
		$set2 = new Set();
		$rowstamp = $set2->MboSetRemote("mxstracking")->MboRemote("min(logindate) as logindate")->MboRemote("rowstamp")->setWhere("username='maxesadmin'")->getMboSet()->getMbo()->getString("rowstamp");
		$set2->MboSetRemote("mxstracking")->setWhere("rowstamp='$rowstamp'")->setValue("rowstamp", $jwtenc)->change();
		echo "Bringing you to your Maxes Software...";
		header("refresh:2;url=" . $roothost);
	}
	
	public static function XboSession($v, $x){
		$set = new Set();
		$tsc = $set->MboSetRemote("mxstracking")->MboRemote("username")->getMboSet()->counts();
		$ret = "ls";
		
		//activation
		/*
		if ($v == "maxesadmin") {
		
			if ($tsc == 0) {
				$ret = "lx";
			}
			else {
				error_reporting(0);
				$isr = new JWT();
				$ret = $isr->getIssuer()->ret;
				
			}
		}
		else {
			error_reporting(0);
			$isr = new JWT();
			$ret = $isr->getIssuer()->ret;
		}
		*/
		//end activation
		if ($ret == "ls") {
			session_start();
			echo $ret;
			$_SESSION['username'] = $v;
			$_SESSION['password'] = $x;
			$_SESSION['invested'] = uniqid();
			Super::Investor($_SESSION['username'], $_SESSION['invested']);
		}
		else {
			echo $ret;
		}
	}
	
	public static function EncodeUpreg($jwtclaims){
		$jwttemp = $jwtclaims;
		for ($i = 1; $i <= 7; $i++) {
			$jwtloop = base64_encode($jwttemp);
			$jwttemp = $jwtloop;
		}
		return $jwttemp;
	}
	
	public static function DecodeUpregTracking($jwtclaims){
		
		$jwttemp = $jwtclaims;
		for ($i = 1; $i <= 8; $i++) {
			$jwtloop = base64_decode($jwttemp);
			$jwttemp = $jwtloop;
		}
		
		return $jwttemp;
	}
	public static function XPreg(){
		$enterdate = Xbo::setEnterDate();
		$ipaddr = new Super();
		$ip = $ipaddr->getIPAddress();
		$set = new Set();
		$upreg = $_GET['upreg'];
		
		$trunc = "truncate table mxstracking";
		DB::exec($trunc);
		$set->MboSetRemote("mxstracking")->setValue("username", "maxesadmin")->setValue("ipaddress", $ip)->setValue("logindate", $enterdate)->setValue("rowstamp", $upreg)->add();
		$set = new Super();
		$set->registered();
	}
	
	public static function Invested($y, $x, $z, $licensetype = null){
		$usernameValue = $y;
		$passwordValue = $x;
		$status = $z;
		$set = new Set();
		SelectedSeed::SetSeed($licensetype, $usernameValue, $passwordValue, $status);
	}
	
}


?>