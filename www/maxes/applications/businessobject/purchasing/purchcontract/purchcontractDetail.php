<?php 
include ("../../eat.php");

class ContractDetail  {
	public static function showDetail(){
		XboSet::getXboSet("purchcontract");
		XboSet::showDetail();
	}	
}

ContractDetail::showDetail();


?>