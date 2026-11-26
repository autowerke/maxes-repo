<?php
class License {
	public static function OneYear(){
		$oneyear = 31536000;
		return $oneyear;
	}
	public static function ThreeYears(){
		$oneyear = License::OneYear();
		$threeyears = $oneyear*3;
		
		return $threeyears;
	}
}
class SelectedSeed {
	public static function SetSeed($value, $usernameValue, $passwordValue, $status){
		$msgResponse = null;
		if($value == "recurring"){
			if ($status == "INACTIVE") {
				$msgResponse =  "yq";
				$mboset = "(select gendate,activedate,expireddate,actid,ul from mxsusersact where expireddate in (select max(expireddate) from mxsusersact where userid='$usernameValue')) maxmxsusersact";
				$data = $set->MboSetRemote($mboset)->MboRemote("gendate")->MboRemote("activedate")->MboRemote("expireddate")->MboRemote("actid")->MboRemote("ul")->getMboSet()->getMbo();
				$activedate = $data->getString("activedate");
				$expireddate = $data->getString("expireddate");
				$oActDate = $data->getString("activedate");
				if (strtotime($expireddate) < strtotime("now")) {
					$set->MboSetRemote("mxsusers")->setWhere("userid='$usernameValue' and userid not in ('maxesadmin', 'eatadmin')")->setValue("status", "EXPIRED")->change();
				}
			}
				if ($status == "BLOCKED") $msgResponse =  "bx";
				if ($status == "DELETED") $msgResponse =  "dx";
				if ($status == "LOCKED") $msgResponse =  "cx";
				if ($status == "EXPIRED") $msgResponse =  "exx";
				if ($status == "ACTIVE") {
			
				if ($usernameValue != 'maxesadmin' && $usernameValue != 'eatadmin') {
					$set = new Set();
					$mboset = "( select gendate,activedate,expireddate,actid,ul, sr from mxsusersact where expireddate in (	select max(expireddate) from mxsusersact where userid='$usernameValue' )) maxmxsusersact";
					$dataSet = $set->MboSetRemote($mboset)->MboRemote("gendate")->MboRemote("activedate")->MboRemote("expireddate")->MboRemote("actid")->MboRemote("ul")->MboRemote("sr")->getMboSet();
					$data = $dataSet->getMbo();
					$srkey = $data->getString("sr");
					$generatedDate = $data->getString("gendate");
					$activedate = $data->getString("activedate");
					$expireddate = $data->getString("expireddate");
					$actid = $data->getString("actid");
					$ul = $data->getString("ul");
					$gendate = date_create($generatedDate);
					$gendateFormat = date_format($gendate, "d/m/Y H:i");
					$actdate = date_create($activedate);
					$activedateFormat = date_format($actdate, "d/m/Y H:i");
					$seedroot = $usernameValue . "|" . $gendateFormat;
					$actidUl = $actid . "-" . $ul;
					$oActDate = strtotime($activedate);
					$oExpiredDate = strtotime($expireddate);
					$oDate = $oExpiredDate - $oActDate;
					//echo "Exp Date : $expireddate ";
					if (strtotime($expireddate) < strtotime("now")) {
						$msgResponse =  "lex";
						$wherecls = "userid not in (select userid from mxsusersact where expireddate>now()) and status not in ('EXPIRED')";
						$userSet = $set->MboSetRemote("mxsusers")->setWhere($wherecls)->MboRemote("userid")->getMboSet("remove_duplicate");
						$numrows = $userSet->counts();
						$sets = new Set();
						if ($numrows > 0) {
							for ($i = 0; $i < $numrows; $i++) {
								$user = $userSet->getMbo();
								$userid = $user->getString("userid");
								$sets->MboSetRemote("mxsusers")->setWhere("userid='$userid' and userid not in ('maxesadmin', 'eatadmin')")->setValue("status", "EXPIRED")->change()->getStatement("update");
							}
						}
						else {
							$sets->MboSetRemote("mxsusers")->setWhere("userid='$usernameValue' and userid not in ('maxesadmin', 'eatadmin')")->setValue("status", "EXPIRED")->change();
						}
					}
					else {
						$lic = License::OneYear();
						if ($oDate <= $lic) {
							$arrRev = Xbo::setSR($seedroot);
							$arrStr = implode("-", $arrRev);
							if ($arrStr == $actidUl) {
								$sr2 = $generatedDate . "|" . $activedate;
								$srr = Xbo::setSR($sr2);
								$sr = implode("-", $srr);
								if ($sr == $srkey) {
									$super = new Super();
									$as = $super->XboSession($usernameValue, $passwordValue);
									$_SESSION['username'] = $usernameValue;
									$_SESSION['password'] = $passwordValue;
								}
								else {
									$msgResponse =  "fk";
								}
							}
							else {
								$msgResponse =  "fk";
							}
						}
						else {
							$msgResponse =  "fk";
						}
					}
				}
				else {
					Seed::XboSession($usernameValue, $passwordValue);
				}
			}
		}else{
			if($value == "onetime"){
				if ($status == "INACTIVE") $msgResponse =  "yq";
				if ($status == "BLOCKED") $msgResponse =  "bx";
				if ($status == "DELETED") $msgResponse =  "dx";
				if ($status == "LOCKED") $msgResponse =  "cx";
				if ($status == "EXPIRED") {
					if($value == "onetime"){
						Seed::XboSession($usernameValue, $passwordValue);
					}else{
						$msgResponse =  "exx";
					}
				}
				if ($status == "ACTIVE") {
					Seed::XboSession($usernameValue, $passwordValue);
				}
			}
		}
		
		echo $msgResponse;
	}
}
?>