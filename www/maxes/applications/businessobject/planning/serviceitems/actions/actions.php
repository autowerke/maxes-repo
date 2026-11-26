<?php
require_once ("../../../eat.php");

Class ActionSet {
	public static function addPrecaution(){
		$precautions = $_POST["addPrecaution"];
		$hazardnum = $_POST["hazardnum"];
		$set = new Set();
		$tb = "hazardprecaution";
		$len = count($precautions);
		for($i=0; $i<$len; $i++){
			$precaution = $precautions[$i]["precaution"];
			$hazardPrecautionSet = $set->MboSetRemote($tb)->setWhere("hazardnum='$hazardnum' and precaution = '$precaution'")
			->getMboSet();
			
			$cnt = $hazardPrecautionSet->counts();
			$precautionlinenum = Xbo::getLineNumber($tb, "precautionlinenum", "hazardnum='$hazardnum'");
			if($cnt == 0){
				$set =  new Set();
				$set->MboSetRemote($tb)->setValue("hazardnum", $hazardnum)->setValue("precaution", $precaution)->setValue("precautionlinenum", $precautionlinenum)->add();
				$msg = "added";
			}else{
				$msg = "exist";
			}
		}
		
	
		
		Set::throws($msg);
	}
} 

Class ValidateTables {
	
}
	

?>