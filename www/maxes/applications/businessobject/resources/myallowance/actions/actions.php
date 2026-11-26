<?php
require_once ("../../../eat.php");

Class ActionSet {
	public static function GetFinancialPeriod(){
		$set = new Set();
		$financialperiod = Xbo::getFinancialPeriod();
		Set::throws(["financialperiod"=>$financialperiod]);
	}
	public static function UpdateMyAllowance()
	{
		$set = new Set();
		$recid = explode("^", $_GET["updmyallowance"]) [1];
		$set = new Set();
		$financialperiod = Xbo::getFinancialPeriod();
		$set->MboSetRemote("myallowance")->setWhere("myallowanceid='$recid'")->setValue("financialperiod", $financialperiod)->change();
	}
	public static function SumAllowanceCost()
	{
		$value = $_GET['sumallowancecost'];
		$parts = explode("|", $value);
		$table = $parts[0];
		$owner = explode("`", $parts[1]);
		$col = $owner[0];
		$val = "'" . $owner[1] . "'";
		$set = new Set();
		$totalcost = $set->MboSetRemote("myallowanceline")->setWhere("$col=$val")->MboRemote("sum(linecost) as totalcost ")->getMboSet()->getMbo()->getString("totalcost");
		echo json_encode(["totalcost" => $totalcost]);
	}
	public static function UpdateBaseSalary()
	{
		$basesalary = $_POST["updbasesalary"];
		$changeby = $_POST["changeby"];
		$recordid = explode("^", $_POST["recordid"]) [1];
		$set = new Set();
		$set->MboSetRemote("employees")->setWhere("employeesid='" . $recordid . "'")->setValue("basesalary", $basesalary)->change();
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
		$empallowanceSet = $set2->MboSetRemote("empallowance")->setRelationship("allowance", "allowance.allowancecode = empallowance.allowancecode")->setWhere("personid='$personid'")->MboRemote("empallowance.allowancecode")->MboRemote("allowance.costpercent")->MboRemote("allowance.costprice")->MboRemote("allowance.frombasesalary")->MboRemote("allowance.fromgrossincome")->MboRemote("allowance.deduction")->getMboSet();
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
		$otherAllowance = Xbo::totalOtherAllowance($employeesid, "dontprint");
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
		$sumotall = $set->MboSetRemote("myallowance")->setWhere("personid='$personid' " . Xbo::varallowance() . "")->MboRemote("sum(totalcost) as sumotall")->getMboSet()->getMbo()->getString("sumotall");
		if ($wanttoprint == null) {
			echo $sumotall;
		}
		if ($wanttoprint == "dontprint") {
			return $sumotall;
		}
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