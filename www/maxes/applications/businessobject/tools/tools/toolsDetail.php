<?php 
include ("../../eat.php");

class ToolsDetail  {
	public static function showDetail(){
		XboSet::getXboSet("tools");
		XboSet::showDetail();	
	}	
}

ToolsDetail::showDetail();


?>