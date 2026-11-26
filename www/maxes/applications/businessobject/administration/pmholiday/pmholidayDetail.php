<?php 
include ("../../eat.php");

class PmHoliday  {
	public static function showDetail(){
		XboSet::getXboSet("pmholiday");
		XboSet::showDetail();	
	}	
}

PmHoliday::showDetail();


?>