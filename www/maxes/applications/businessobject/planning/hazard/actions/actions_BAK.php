<?php
require_once ("../../../eat.php");

Class ActionSet {
	public static function CopyAllowance()

	{
		$allowance = $_POST["copyallowance"];
		$recordid = explode("^", $_POST["recordid"]);
		$set = new Set();
		$employeesid = $recordid[1];
		$employeesSet = $set->MboSetRemote("employees")->setWhere("employeesid='$employeesid'")->MboRemote("personid")->MboRemote("taxexemptcost")->MboRemote("basesalary")->getMboSet();
		$employees = $employeesSet->getMbo();
		$taxexemptcost = $employees->getString("taxexemptcost");
		$basesalary = $employees->getString("basesalary");
		$personid = $employees->getString("personid");
		if (empty($taxexemptcost)) $taxexemptcost = 0;
		$allowancecoder = array();
		$isAllExist = new Set();
		for ($i = 0; $i < count($allowance); $i++) {
			$x = explode("`", $allowance[$i]);
			$allowanceid = $x[1];
			$set2 = new Set();
			$allowanceSet = $set2->MboSetRemote("allowance")->setWhere("allowanceid='$allowanceid'")->MboRemote("allowancecode")->MboRemote("glaccount")->MboRemote("costprice")->MboRemote("costpercent")->MboRemote("deduction")->MboRemote("frombasesalary")->MboRemote("fromgrossincome")->getMboSet();
			$aw = $allowanceSet->getMbo();
			$allowancecode = $aw->getString("allowancecode");
			$glaccount = $aw->getString("glaccount");
			$costprice = $aw->getString("costprice");
			$costpercent = $aw->getString("costpercent");
			$deduction = $aw->getString("deduction");
			$frombasesalary = $aw->getString("frombasesalary");
			$fromgrossincome = $aw->getString("fromgrossincome");
			$cost = 0;
			$allowancecost = 0;
			echo ">> $allowancecode";
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
					array_push($allowancecoder, $allowancecode);
					$allowancecost = $cost;
				}
			}
			
			if ($deduction == "Y") $allowancecost = $allowancecost * -1;
			$set3 = new Set();
			$isExist = $isAllExist->MboSetRemote("empallowance")->setWhere("personid='$personid' and allowancecode='$allowancecode'")->MboRemote("allowancecode")->getMboSet()->counts();
			if ($isExist == 0) $set->MboSetRemote("empallowance")->setValue("personid", $personid)->setValue("allowancecode", $allowancecode)->setValue("cost", $allowancecost)->add();
		}
		$set4 = new Set();
		$grossincome = $set4->MboSetRemote("empallowance")->setWhere("personid='$personid' and cost>-1")->MboRemote("sum(cost) as grossincome")->getMboSet()->getMbo()->getString("grossincome");
		$otherAllowance = ActionSet::totalOtherAllowance($employeesid, "dontprint");
		if($otherAllowance == null) $otherAllowance =0;
		$grossincome = $grossincome + $otherAllowance;
		$grossincome2 = $basesalary + $grossincome;
		$set4->MboSetRemote("employees")->setWhere("personid='$personid'")->setValue("grossincome", $grossincome2)->change();
		$set5 = new Set();
		for ($i = 0; $i < count($allowancecoder); $i++) {
			$allowancecode = $allowancecoder[$i];
			$set = new Set();
			$set2 = new Set();
			$empallSet = $set->MboSetRemote("empallowance")->setWhere("personid='$personid' and allowancecode='$allowancecode'");
			$empall = $empallSet->MboRemote("cost")->getMboSet()->getMbo();
			$cost = $empall->getString("cost");
			$newcost = ($cost / 100) * $grossincome2;
			$set2->MboSetRemote("empallowance")->setWhere("personid='$personid' and allowancecode='$allowancecode'")->setValue("cost", $newcost)->change();
		}
		$netincome = $set5->MboSetRemote("empallowance")->setWhere("personid='$personid'")->MboRemote("sum(cost) as netincome")->getMboSet()->getMbo()->getString("netincome");
		$netincome = $netincome + $otherAllowance;
		$nettincome2 = $basesalary + $netincome;
		$yearlynettincome = $nettincome2 * 12;
		$set4->MboSetRemote("employees")->setWhere("personid='$personid'")->setValue("nettincome", $nettincome2)->setValue("yearlynettincome", $yearlynettincome)->change();
		$yearlytaxcost = $yearlynettincome - $taxexemptcost;
		
		$rounddowntaxcost=0;
		if($yearlytaxcost>1000){
			$rounddowntaxcost = substr($yearlytaxcost, 0, -3) . str_repeat('0', 3);
			if($yearlytaxcost>0){
				$yearlytaxcost=$rounddowntaxcost;
			}else{
				$yearlytaxcost=0;
			}
		}
		
		if ($yearlytaxcost > 0) {
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
				$monthlytaxcharge2 = $monthlytaxcharge*(120/100);
			//$set4->MboSetRemote("employees")->setWhere("personid='$personid'")->setValue("yearlytaxcost", $rounddowntaxcost)->change();
		}
		else {
			$yearlytaxcost=0;
			//$set4->MboSetRemote("employees")->setWhere("personid='$personid'")->setValue("yearlytaxcost", 0)->change();
		}
		/*
		Tarif PPh 21 :
			5% untuk penerima penghasilan sampai dengan Rp 50 juta per tahun.
			15% untuk penerima penghasilan Rp 50 juta - Rp 250 juta per tahun.
			25% untuk penerima penghasilan Rp 250 juta - Rp 500 juta per tahun.
			30% untuk penerima penghasilan lebih dari Rp 500 juta per tahun.
			Bagi penerima penghasilan yang tidak memiliki NPWP maka dikenakan tarif PPh 21 sebesar 20% lebih tingggi.
		*/
		$pphcut = 0;
		if ($rounddowntaxcost > 0 && $rounddowntaxcost <= 50000000) $pphcut = 5;
		if ($rounddowntaxcost > 50000000 && $rounddowntaxcost < 250000000) $pphcut = 15;
		if ($rounddowntaxcost >= 250000000 && $rounddowntaxcost < 500000000) $pphcut = 25;
		if ($rounddowntaxcost >= 500000000) $pphcut = 30;
		$yearlytaxcharge = ($pphcut / 100) * $rounddowntaxcost;
		$monthlytaxcharge = floor($yearlytaxcharge / 12);
		$nonnpwpyearlytaxcharge = $yearlytaxcharge * (120 / 100);
		$nonnpwmonthlytaxcharge = $monthlytaxcharge * (120 / 100);
		
		$set4->MboSetRemote("employees")->setWhere("personid='$personid'")
		->setValue("yearlytaxcharge", $yearlytaxcharge)->setValue("monthlytaxcharge", $monthlytaxcharge)
		->setValue("monthlytaxcharge2", $nonnpwmonthlytaxcharge)->setValue("yearlytaxcharge2", $nonnpwpyearlytaxcharge)
		->change();
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
				ActionSet::sumallowance($empid);
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
		$allowance = $set->MboSetRemote("employees")->setWhere("employeesid='" . $recordid . "'")->MboRemote("allowance")->getMboSet()->getMbo()->getString("allowance");
		$grossincome = $allowance+$basesalary;
		$set->MboSetRemote("employees")->setWhere("employeesid='" . $recordid . "'")->setValue("basesalary", $basesalary)->setValue("grossincome", $grossincome)->change();
		
	}
	public static function SumAllowance($empid = null)

	{
		if ($empid == null) {
			$recordid = explode("^", $_GET["sumallowance"]);
			$employeesid = $recordid[1];
		}
		else {
			$employeesid = $empid;
		}
		$set = new Set();
		$employeesSet = $set->MboSetRemote("employees")->setWhere("employeesid='$employeesid'")
			->MboRemote("personid")->MboRemote("taxexemptcost")->MboRemote("basesalary")->getMboSet();
			
		$employees = $employeesSet->getMbo();
		$taxexemptcost = $employees->getString("taxexemptcost");
		$basesalary = $employees->getString("basesalary");
		$personid = $employees->getString("personid");
		
		if (empty($taxexemptcost)) $taxexemptcost = 0;
		$set2 = new Set();
		$empallowanceSet = $set2->MboSetRemote("empallowance")->setRelationship("allowance", "allowance.allowancecode = empallowance.allowancecode")->setWhere("personid='$personid'")
		->MboRemote("empallowance.allowancecode")->MboRemote("allowance.costpercent")->MboRemote("allowance.costprice")->MboRemote("allowance.frombasesalary")
		->MboRemote("allowance.fromgrossincome")->MboRemote("allowance.deduction")->MboRemote("allowance.maximumvalue")->getMboSet();
		$empCount = $empallowanceSet->counts();
		$allowancecoder = array();
		for ($i = 0; $i < $empCount; $i++) {
			$set3 = new Set();
			$empallowance = $empallowanceSet->getMbo();
			$allowancecode = $empallowance->getString("allowancecode");
			$costpercent = $empallowance->getString("costpercent");
			$costprice = $empallowance->getString("costprice");
			$frombasesalary = $empallowance->getString("frombasesalary");
			$fromgrossincome = $empallowance->getString("fromgrossincome");
			$maximumvalue = $empallowance->getString("maximumvalue");
			$deduction = $empallowance->getString("deduction");
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
					if($maximumvalue > 0){
						if($allowancecost > $maximumvalue) {
							$allowancecost = $maximumvalue;
						}else{
							$allowancecost = $allowancecost;
						}
					}
				}
				else {
					array_push($allowancecoder, $allowancecode);
					$allowancecost = $cost;
				}
			}
			if ($deduction == "Y") $allowancecost = $allowancecost * -1;
			$set3->MboSetRemote("empallowance")->setWhere("allowancecode='$allowancecode' and personid='$personid'")->setValue("cost", $allowancecost)->change();
		}
		$set4 = new Set();
		$grossincome = $set4->MboSetRemote("empallowance")->setWhere("personid='$personid' and cost>-1")->MboRemote("sum(cost) as grossincome")->getMboSet()->getMbo()->getString("grossincome");
		// add other allowances
		$otherAllowance = ActionSet::totalOtherAllowance($employeesid, "dontprint");
		if($grossincome==null) $grossincome=0;
		if($otherAllowance==null) $otherAllowance=0;
		if($basesalary==null) $basesalary=0;
		$grossincome = $grossincome + $otherAllowance;
		$grossincome2 = $basesalary + $grossincome;
		$setemp = new Set();
		$setemp->MboSetRemote("employees")->setWhere("personid='$personid'")->setValue("grossincome", $grossincome2)->change();
		for ($i = 0; $i < count($allowancecoder); $i++){
			$allowancecode = $allowancecoder[$i];
			$set = new Set();
			$set2 = new Set();
			$empallSet = $set->MboSetRemote("empallowance")->setWhere("personid='$personid' and allowancecode='$allowancecode'");
			$empall = $empallSet->MboRemote("cost")->getMboSet()->getMbo();
			$cost = $empall->getString("cost");
			$newcost = ($cost / 100) * $grossincome2;
			$set2->MboSetRemote("empallowance")->setWhere("personid='$personid' and allowancecode='$allowancecode'")->setValue("cost", $newcost)->change();
		}
		$set5 = new Set();
		$netincome = $set5->MboSetRemote("empallowance")->setWhere("personid='$personid'")->MboRemote("sum(cost) as netincome")->getMboSet()->getMbo()->getString("netincome");
		
		if($netincome==null) $netincome=0;
		$netincome = $netincome + $otherAllowance;
		$nettincome2 = $basesalary + $netincome;
		$yearlynettincome = $nettincome2 * 12;
		$set4->MboSetRemote("employees")->setWhere("personid='$personid'")->setValue("nettincome", $nettincome2)->setValue("yearlynettincome", $yearlynettincome)->change();
		$yearlytaxcost = $yearlynettincome - $taxexemptcost;
		
		$rounddowntaxcost=0;
		if($yearlytaxcost>1000){
			$rounddowntaxcost = substr($yearlytaxcost, 0, -3) . str_repeat('0', 3);
			if($yearlytaxcost>0){
				$yearlytaxcost=$rounddowntaxcost;
			}else{
				$yearlytaxcost=0;
			}
		}
		
		if ($yearlytaxcost > 0) {
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
			//$set4->MboSetRemote("employees")->setWhere("personid='$personid'")->setValue("yearlytaxcost", $yearlytaxcost)->change();
		}
		else {
			$yearlytaxcost=0;
			//$set4->MboSetRemote("employees")->setWhere("personid='$personid'")->setValue("yearlytaxcost", 0)->change();
		}
		/*
		Tarif PPh 21 :
		5% untuk penerima penghasilan sampai dengan Rp 50 juta per tahun.
		15% untuk penerima penghasilan Rp 50 juta - Rp 250 juta per tahun.
		25% untuk penerima penghasilan Rp 250 juta - Rp 500 juta per tahun.
		30% untuk penerima penghasilan lebih dari Rp 500 juta per tahun.
		Bagi penerima penghasilan yang tidak memiliki NPWP maka dikenakan tarif PPh 21 sebesar 20% lebih tingggi.
		*/
		$pphcut = 0;
		if ($rounddowntaxcost > 0 && $rounddowntaxcost <= 50000000) $pphcut = 5;
		if ($rounddowntaxcost > 50000000 && $rounddowntaxcost < 250000000) $pphcut = 15;
		if ($rounddowntaxcost >= 250000000 && $rounddowntaxcost < 500000000) $pphcut = 25;
		if ($rounddowntaxcost >= 500000000) $pphcut = 30;
		$yearlytaxcharge = ($pphcut / 100) * $rounddowntaxcost;
		$monthlytaxcharge = floor($yearlytaxcharge / 12);
		$nonnpwpyearlytaxcharge = $yearlytaxcharge * (120 / 100);
		$nonnpwmonthlytaxcharge = $monthlytaxcharge * (120 / 100);
		$set4->MboSetRemote("employees")->setWhere("personid='$personid'")
			->setValue("yearlytaxcharge", $yearlytaxcharge)
			->setValue("monthlytaxcharge", $monthlytaxcharge)
			->setValue("monthlytaxcharge2", $nonnpwmonthlytaxcharge)
			->setValue("yearlytaxcharge2", $nonnpwpyearlytaxcharge)->change();
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