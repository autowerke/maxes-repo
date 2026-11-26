<?php
require_once ("../../../eat.php");
Class ActionSet {

	public static function updateNewBudgetPO(){
		$msg = "";
		$records = $_POST["updateNewBudgetPO"];
		$count_recs = count($records);
		$selected_year = null;
		$ponum = null;
		$newbudgetnum = null;
		
		if($count_recs > 0){
			for($i = 0; $i < $count_recs; $i++){
				$ponum = $records[$i]["ponum"];
				$polinenum = $records[$i]["polinenum"];
				$selected_year = $records[$i]["selected_year"];
				$newbudgetnum = $records[$i]["newbudgetnum"];
				$newbudgetlinenum = $records[$i]["newbudgetlinenum"];
				$set = new Set();
				$sqlbudgetnum = " budgetnum = '$newbudgetnum' and budgetlinenum = '$newbudgetlinenum'";
				$num_rows  = $set -> MboSetRemote("budgetline")-> setWhere($sqlbudgetnum)->getMboSet() -> counts();
				if($num_rows ==0){
					 Set::throws(["msg" => "Budget & Budgetline Number is not registered into database"]); 
					 return;
				 }
				$set2 = new Set();
				$where = "ponum = '$ponum' AND polinenum = '$polinenum' ";
				
				$updateMboSet = $set2->MboSetRemote("annualbudgetalloc")->setWhere($where. "  AND budgetyear = '$selected_year'")
				->setValue("budgetnum", $newbudgetnum)
				->setValue("budgetlineNum", $newbudgetlinenum)
				->setValue("status", 'active')
				->change();	
				
				
				$set3 = new Set();
				$set3->MboSetRemote("poline")->setWhere($where)->setValue("budgetlinenum", $newbudgetlinenum)->change();
			
				$msg .= "Record has been updated for PONUM: $ponum, POLINENUM: $polinenum, Year: $selected_year. <br>";
			}
			
			$set = new Set();
            $whereInactive = "ponum = '$ponum' AND budgetyear < '$selected_year'";
            $inactiveMboSet = $set->MboSetRemote("annualbudgetalloc")->setWhere($whereInactive)
                ->setValue("status", 'inactive')  // Set ke 'inactive'
                ->change();

			$set = new Set();
			$set->MboSetRemote("po")->setWhere("ponum='$ponum'")->setValue("budgetnum", $newbudgetnum)->change();
		} else {
			$msg = "No records to update.";
		}
		Set::throws(["msg" => $msg]);
	}

}


	

?>