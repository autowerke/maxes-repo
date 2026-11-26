<?php 
include ("../../eat.php");

class ToolsDetail  {
	public static function showDetail(){
		XboSet::getXboSet("toolstock");
		XboSet::showDetail();	
	}	
}

ToolsDetail::showDetail();


?>