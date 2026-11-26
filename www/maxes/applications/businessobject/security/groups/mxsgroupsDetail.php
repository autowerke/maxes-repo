<?php 
include ("../../eat.php");

class MxsGroupsDetail  {
	public static function showDetail(){
		XboSet::getXboSet("mxsgroups");
		XboSet::showDetail();	
	}	
}

MxsGroupsDetail::showDetail();


?>