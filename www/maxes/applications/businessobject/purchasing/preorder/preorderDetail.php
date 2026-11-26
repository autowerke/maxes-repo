<?php 
include ("../../eat.php");

class PreorderDetail  {
	public static function showDetail(){
		XboSet::getXboSet("preorder");
		XboSet::showDetail();	
	}	
}

PreorderDetail::showDetail();


?>