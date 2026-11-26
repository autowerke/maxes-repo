<?php 
include ("../../eat.php");

class WarehouseDetail  {
	public static function showDetail(){
		XboSet::getXboSet("warehouse");
		XboSet::showDetail();	
	}	
}

WarehouseDetail::showDetail();


?>