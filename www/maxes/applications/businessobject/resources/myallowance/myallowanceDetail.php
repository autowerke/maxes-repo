<?php 
include ("../../eat.php");

class MyAllowance  {
	public static function showDetail(){
		XboSet::getXboSet("myallowance");
		XboSet::showDetail();	
	}	
}

MyAllowance::showDetail();


?>