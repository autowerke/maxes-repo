<?php 
include ("../../eat.php");

class MXSChartDetail  {
	public static function showDetail(){
		XboSet::getXboSet("mxschart");
		XboSet::showDetail();	
	}	
}

MXSChartDetail::showDetail();


?>