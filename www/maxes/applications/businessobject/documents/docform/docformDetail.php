<?php 
include ("../../eat.php");

class DocFormDetail  {
	public static function showDetail(){
		XboSet::getXboSet("docform");
		XboSet::showDetail();	
	}	
}

DocFormDetail::showDetail();


?>