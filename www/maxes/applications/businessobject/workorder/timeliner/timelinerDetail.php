<?php 
include ("../../eat.php");

class TimelinerDetail  {
	public static function showDetail(){
		XboSet::getXboSet("timeliner");
		XboSet::showDetail();	
	}	
}

TimelinerDetail::showDetail();


?>