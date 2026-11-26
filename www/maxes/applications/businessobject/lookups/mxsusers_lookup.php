<?php 
include ("../eat.php");

class EmployeeLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	
	
	public static function displayData(){
		Xbo::select("User", "userid");
		Xbo::select("Status", "status");
		Xbo::select("Employee ID", "personid");
		Xbo::select("Full Name", "employees.fullname");
		XboSet::getXboSet("mxsusers", "status='ACTIVE'");
		XboSet::displayData(["employees.personid = mxsusers.personid"]);
	
	}
	
	public static function init(){
		EmployeeLookup::displayData();
	}
	
}

EmployeeLookup::init();




?>