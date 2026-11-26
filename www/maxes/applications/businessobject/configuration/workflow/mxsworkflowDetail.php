<?php 
include ("../../eat.php");

class MXSWORKFLOW  {
	public static function showDetail(){
		XboSet::getXboSet("mxsworkflow");
		XboSet::showDetail();	
	}	
}

MXSWORKFLOW::showDetail();


?>