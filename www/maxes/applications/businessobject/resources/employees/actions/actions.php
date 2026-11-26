<?php
require_once ("../../../eat.php");
require_once ("dataloader.php");
Class ActionSet {
	public static function CalculateAllowance($whereclause, $basesalary, $yearlygrossincomecom, $personid){
		
		$set = new Set();
		$allowanceSet = $set->MboSetRemote("allowance")->setWhere($whereclause)->MboRemote("allowancecode")
		->MboRemote("costprice")->MboRemote("costpercent")->MboRemote("deduction")->MboRemote("frombasesalary")->MboRemote("fromgrossincome")->MboRemote("maximumvalue")->getMboSet();
		
		$aw = $allowanceSet->getMbo();
		$allowancecode = $aw->getString("allowancecode");
		$costprice = $aw->getString("costprice");
		$costpercent = $aw->getString("costpercent");
		$deduction = $aw->getString("deduction");
		$frombasesalary = $aw->getString("frombasesalary");
		$fromgrossincome = $aw->getString("fromgrossincome");
		$maximumvalue = $aw->getString("maximumvalue");
		$cost = 0;
		$allowancecost = 0;
		if ($costpercent == 0) {
			$cost = $costprice;
			$allowancecost = $costprice;
		}
		if ($costprice == 0) {
			$cost = $costpercent;
			if ($frombasesalary == "Y") {
				$allowancecost = ($cost / 100) * $basesalary;
			}
			else {

				if($fromgrossincome == "Y"){
					$allowancecost = ($cost / 100) * ($yearlygrossincomecom/12);
				}else{
					array_push($allowancecoder, $allowancecode);
					$allowancecost = $cost;
				}
				
			}
			if($maximumvalue >0){
				if($allowancecost > $maximumvalue){
					$allowancecost = $maximumvalue;
				}
			}
			
		}
		
		if ($deduction == "Y") $allowancecost = $allowancecost * -1;
		$set3 = new Set();
		$isAllExist = new Set();
		$isExist = $isAllExist->MboSetRemote("empallowance")->setWhere("personid='$personid' and allowancecode='$allowancecode'")->MboRemote("allowancecode")->getMboSet()->counts();
		
		if ($isExist == 0) $set->MboSetRemote("empallowance")->setValue("personid", $personid)->setValue("allowancecode", $allowancecode)->setValue("cost", $allowancecost)->add();
		if ($isExist == 1) $set->MboSetRemote("empallowance")->setWhere("personid='$personid' and allowancecode = '$allowancecode' ")->setValue("cost", $allowancecost)->change();
	}

	public static function CopyAllowance()

	{
		$allowance = $_POST["copyallowance"]; 
		
		$recordid = explode("^", $_POST["recordid"]);
		$set = new Set();
		$employeesid = $recordid[1];
		$employeesSet = $set->MboSetRemote("employees")->setWhere("employeesid='$employeesid'")->MboRemote("personid")->MboRemote("taxexemptcost")->MboRemote("basesalary")->MboRemote("yearlygrossincomecom")
		->getMboSet();
		$employees = $employeesSet->getMbo();
		
		$taxexemptcost = $employees->getString("taxexemptcost");
		if($taxexemptcost == null) $taxexemptcost=0;
		$basesalary = $employees->getString("basesalary");
		
		if($basesalary == null) $basesalary=0;
		$yearlygrossincomecom = $employees->getString("yearlygrossincomecom");
		
		if($yearlygrossincomecom == null) $yearlygrossincomecom=0;
		$personid = $employees->getString("personid");
		
		if (empty($taxexemptcost)) $taxexemptcost = 0;
	
		$allowancecoder = array();
		
		for ($i = 0; $i < count($allowance); $i++) {
			$x = explode("`", $allowance[$i]);
			$allowanceid = $x[1];
			ActionSet::CalculateAllowance("allowanceid='$allowanceid'", $basesalary, $yearlygrossincomecom, $personid);
		}
		
	}
	public static function UpdateAllTax()
	{
		$xset = new Set();
		if ($_GET["updalltax"] == "all") {
			$employeesSet = $xset->MboSetRemote("employees")->MboRemote("employeesid")->getMboSet();
			$empCounts = $employeesSet->counts();
			for ($i = 0; $i < $empCounts; $i++) {
				$emp = $employeesSet->getMbo();
				$empid = $emp->getString("employeesid");
				ActionSet::SumAllowance($empid);
			}
			echo "complete";
		}
	}
	public static function UpdateBaseSalary()
	{
		$basesalary = $_POST["updbasesalary"];
		$changeby = $_POST["changeby"];
		$recordid = explode("^", $_POST["recordid"]) [1];
		$set = new Set();
		$empSet = $set->MboSetRemote("employees")->setWhere("employeesid='" . $recordid . "'")->MboRemote("allowance")->MboRemote("personid")->MboRemote("commision")->getMboSet();
		$emp = $empSet->getMbo();
		$allowance = $emp->getString("allowance");
		$commision = $emp->getString("commision");
		$personid = $emp->getString("personid");
		$grossincome = $allowance+$basesalary;
		
		$yearlygrossincomecom = ($grossincome*12)+$commision;
		
		
		$set = new Set();
		$empAllowanceSet = $set->MboSetRemote("empallowance")->setWhere("personid='$personid'")->MboRemote("allowancecode")->getMboSet();
		$cnt = $empAllowanceSet->counts();
		for($i=0; $i<$cnt; $i++){
			$empal = $empAllowanceSet->getMbo();
			$allowancecode = $empal->getString("allowancecode");
			ActionSet::CalculateAllowance("allowancecode='$allowancecode'", $basesalary, $yearlygrossincomecom, $personid);
		}
		
		
		$set2 = new Set();
		$set2->MboSetRemote("employees")->setWhere("personid='$personid'")->setValue("basesalary", $basesalary)->setValue("grossincome", $grossincome)->change();
		Set::throws(["basesalary"=>$basesalary, "grossincome"=>$grossincome]);
		
		
	}
	public static function SumAllowance($empid = null)

	{
		$yearlytaxcharge=0;
		$monthlytaxcharge = 0;
		$yearlytaxcharge2 = 0; // PPH21 TAHUNAN - NON NPWP
		$monthlytaxcharge2 = 0;// PPH21 BULANAN - NON NPWP
		if ($empid == null) {
			$recordid = explode("^", $_POST["sumallowance"]);
			$employeesid = $recordid[1];
		}
		else {
			$employeesid = $empid;
		}
		$set = new Set();
		$empSet = $set->MboSetRemote("employees")->setWhere("employeesid=$employeesid")->MboRemote("basesalary")->MboRemote("allowance")->MboRemote("otherallowance")
		->MboRemote("bpjsallowance")->MboRemote("commision")->MboRemote("potbpjs")->MboRemote("taxexemptcost")->MboRemote("personid")->getMboSet();
		$emp = $empSet->getMbo();
		$personid = $emp->getString("personid");
		$basesalary = $emp->getString("basesalary");
		$allowance = $emp->getString("allowance");
		$bpjsallowance = $emp->getString("bpjsallowance");
		$commision = $emp->getString("commision");
		$potbpjs = $emp->getString("potbpjs");
		$otherallowance = $emp->getString("otherallowance");
		$taxexemptcost  = $emp->getString("taxexemptcost");
		if($basesalary == null) $basesalary=0;
		if($allowance == null) $allowance=0;
		if($bpjsallowance == null) $bpjsallowance=0;
		if($commision == null) $commision=0;
		if($potbpjs == null) $potbpjs=0;
		if($otherallowance == null) $otherallowance=0;
		
		if($basesalary >0 ){
			$grossincome = $basesalary+$allowance+$otherallowance+$bpjsallowance;
			$grossincomecom = $grossincome;
			$yearlygrossincomecom = ($grossincomecom*12)+$commision;
			
			$set2 = new Set();
			$totalallowance =$set2->MboSetRemote("empallowance")->setWhere("personid='$personid'")->MboRemote("sum(cost) as totalallowance")->getMboSet()->getMbo()->getString("totalallowance");
			if($totalallowance==null) $totalallowance=0;
			
			$nettincome = $grossincome+$totalallowance;
			$yearlynettincome = $nettincome*12;
			$yearlytaxcost = $yearlynettincome-$taxexemptcost-$potbpjs;
			$yearlytaxcost = floor($yearlytaxcost);
			if($yearlytaxcost>1000){
				$pkp_pembulatan = substr($yearlytaxcost, 0, -3) . str_repeat('0', 3);
				if($yearlytaxcost>0){
					$yearlytaxcost=$pkp_pembulatan;
					}else{
						$yearlytaxcost=0;
					}
			}else{
				$yearlytaxcost=0;
			}
			if($yearlytaxcost>0){
				$progressivecost_1 = 50000000;
				$progressivecost_2 = 200000000;
				$progressivecost_3 = 250000000;
							
				$progressive_1 = $progressivecost_1*(5/100); //2500000
				$progressive_2 = $progressivecost_2*(15/100); //30 000 000
				$progressive_3 = $progressivecost_3*(25/100); //62500000
				$progressive_n = 0;
							
				if($yearlytaxcost>0 && $yearlytaxcost <50000000) {
					$yearlytaxcharge = (5/100)*$yearlytaxcost; // PPH21 TAHUNAN
					}else{
						if($yearlytaxcost>50000000 && $yearlytaxcost <250000000) {
							$progressivecost = $progressivecost_1;
							$progressivecost_n = $yearlytaxcost-$progressivecost;
							$progressive_n = $progressivecost_n*(15/100);
							$yearlytaxcharge= $progressive_1+$progressive_n;
							}else{
							if($yearlytaxcost>=250000000 && $yearlytaxcost <500000000) {
								$progressivecost = $progressivecost_1+$progressivecost_2;
								$progressivecost_n = $yearlytaxcost-$progressivecost;
								$progressive_n = $progressivecost_n*(25/100);
								$yearlytaxcharge= $progressive_1+$progressive_2+$progressive_n;
										
							}else{
								if($yearlytaxcost>=500000000) {
									$progressivecost = $progressivecost_1+$progressivecost_2+$progressivecost_3;
									$progressivecost_n = $yearlytaxcost-$progressivecost;
									$progressive_n = $progressivecost_n*(30/100);
									$yearlytaxcharge= $progressive_1+$progressive_2+$progressive_3+$progressive_n;
								}
							}
						}
					}
							
					$monthlytaxcharge = floor($yearlytaxcharge/12);
					$yearlytaxcharge2 = $yearlytaxcharge*(120/100); // PPH21 TAHUNAN - NON NPWP
					$monthlytaxcharge2 = $monthlytaxcharge*(120/100);// PPH21 BULANAN - NON NPWP
							
				}else{
					$yearlytaxcost=0;
			}
			
			Set::throws([
				"grossincome"=>$grossincome,
				"nettincome"=>$nettincome,
				"yearlynettincome"=>$yearlynettincome,
				"yearlytaxcost" => $yearlytaxcost,
				"monthlytaxcharge" =>$monthlytaxcharge,
				"yearlytaxcharge" =>$yearlytaxcharge,
				"monthlytaxcharge2" => $monthlytaxcharge2,
				"yearlytaxcharge2" => $yearlytaxcharge2,
				"yearlygrossincomecom" => $yearlygrossincomecom
			]);
		}else{
			Set::throws([
				"grossincome"=>0,
				"nettincome"=>0,
				"yearlynettincome"=>0,
				"yearlytaxcost" => 0,
				"monthlytaxcharge" =>0,
				"yearlytaxcharge" =>0,
				"monthlytaxcharge2" => 0,
				"yearlytaxcharge2" => 0,
				"yearlygrossincomecom" => 0
			]);
		}
		
		
	}
	public static function TaxExempt()
	{
		$taxexempt = $_GET["taxexempt"];
		$taxexempt = trim($taxexempt, " ");
		$set = new Set();
		$price = $set->MboSetRemote("tax")->setWhere("taxcode='$taxexempt'")->MboRemote("value")->getMboSet()->getMbo()->getString("value");
		
		echo $price;
	}
	
	public static function TotalOtherAllowance($empid = null, $wanttoprint = null)
	{
		if ($empid == null) {
			$recordid = explode("^", $_GET["totalotall"]);
			$employeesid = $recordid[1];
		}
		else {
			$employeesid = $empid;
		}
		$set = new Set();
		$personid = $set->MboSetRemote("employees")->setWhere("employeesid='$employeesid'")->MboRemote("personid")->getMboSet()->getMbo()->getString("personid");
		$sumotall = $set->MboSetRemote("myallowance")->setWhere("personid='$personid' " . ActionSet::varallowance() . "")->MboRemote("sum(totalcost) as sumotall")->getMboSet()->getMbo()->getString("sumotall");
		if ($wanttoprint == null) {
			echo $sumotall;
		}
		if ($wanttoprint == "dontprint") {
			return $sumotall;
		}
	}
	public static function varallowance()

	{
		$financialperiod = Xbo::getFinancialPeriod();
		$whereclause = "and financialperiod='$financialperiod' and status='approved'";
		return $whereclause;
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