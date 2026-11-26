<?php 
/*author : Tommy Nitidiryo Sumarsono (c) 2015
*/

include ("../../eat.php");

class MxsUsersDetail  {
	public static function showDetail(){
		XboSet::getXboSet("mxsusers");
		XboSet::showDetail();	
	}	
}

MxsUsersDetail::showDetail();


?>