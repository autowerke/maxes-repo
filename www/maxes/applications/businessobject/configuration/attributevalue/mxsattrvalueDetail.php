<?php 
include ("../../eat.php");

class AttributeValueDetail  {
	public static function showDetail(){
		XboSet::getXboSet("mxsattrvalue");
		XboSet::showDetail();	
	}	
}

AttributeValueDetail::showDetail();


?>