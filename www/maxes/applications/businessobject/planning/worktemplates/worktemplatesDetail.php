<?php 
include ("../../eat.php");

class WorkTemplatesDetail  {
	public static function showDetail(){
		XboSet::getXboSet("worktemplates");
		XboSet::showDetail();	
	}	
}

WorkTemplatesDetail::showDetail();


?>