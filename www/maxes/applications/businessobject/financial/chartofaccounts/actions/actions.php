<?php
require_once ("../../../eat.php");
require_once ("dataloader.php");
Class ActionSet {

	public static function CoAOwner(){
		$recid = explode("^", $_POST["coaowner"]);
		$tbl = $recid[0];
		$id = $recid[1];
		
		$set = new Set();
		$maingl = $set->MboSetRemote("chartofaccount")->setWhere("chartofaccountid='$id'")->MboRemote("glaccount")->getMboSet()->getMbo()->getString("glaccount");
		
		$msg = null;
		$data = $_POST["data"];
	
		$cnt = count($data);
		for($i=0; $i<$cnt; $i++){
			$p = explode("`", $data[$i]);
			$cid = $p[1];
			
			$set2 = new Set();
			$glaccount2 = $set2->MboSetRemote("chartofaccount")->setWhere("chartofaccountid='$cid'")->MboRemote("glaccount")->getMboSet()->getMbo()->getString("glaccount");
			
			$set3= new Set();
			$cntt = $set3->MboSetRemote("chartofaccountline")->setWhere("glaccount='$maingl' and glaccount2 = '$glaccount2'")->getMboSet()->counts();
			if($cntt == 0){
				$set4 = new Set();
				$set4->MboSetRemote("chartofaccountline")->setValue("glaccount", $maingl)->setValue("glaccount2", $glaccount2)->add();
				$msg = "ok";
			}else{
				$msg = "exist";
				break;
			}
		}
		Set::throws(["msg"=>$msg]);
	}

}


	

?>