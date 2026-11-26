<?php 
include ("../../eat.php");

class WorkInstructionDetail  {
	public static function showDetail(){
		XboSet::getXboSet("workinstr");
		XboSet::showDetail();	
	}	
}

WorkInstructionDetail::showDetail();


?>