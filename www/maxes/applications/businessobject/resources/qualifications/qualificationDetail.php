<?php 
include ("../../eat.php");

class QualificationDetail  {
	
	public static function showDetail(){
		XboSet::getXboSet("qualification");
		XboSet::showDetail();
	}
	
}

QualificationDetail::showDetail();


?>