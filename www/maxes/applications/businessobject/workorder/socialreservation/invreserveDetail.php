<?php
include ("../../eat.php");

class InvreserveDetail  {
	public static function showDetail(){
		XboSet::getXboSet("invreserve");
		XboSet::showDetail();
	}
}

InvreserveDetail::showDetail();
?>