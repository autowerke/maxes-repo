<?php 
include ("../../eat.php");

class MXSMODULE  {
	public static function showDetail(){
		XboSet::getXboSet("mxsmodule");
		XboSet::showDetail();	
	}	
}

MXSMODULE::showDetail();


?>