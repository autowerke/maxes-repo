<?php 
include ("../../eat.php");

class EmployeeDetail  {
	public static function showDetail(){
		XboSet::getXboSet("employees");
		XboSet::showDetail();	
	}	
}

EmployeeDetail::showDetail();


?>