<?php 
include ("../../eat.php");

class InventoryDetail  {
	public static function showDetail(){
		XboSet::getXboSet("inventory");
		XboSet::showDetail();	
	}	
}

InventoryDetail::showDetail();


?>