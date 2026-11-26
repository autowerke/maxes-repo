<?php 
include ("../../eat.php");

class BulletinBoardDetail  {
	public static function showDetail(){
		XboSet::getXboSet("bboard");
		XboSet::showDetail();	
	}	
}

BulletinBoardDetail::showDetail();


?>