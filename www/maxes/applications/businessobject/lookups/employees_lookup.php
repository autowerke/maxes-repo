<?php 
include ("../eat.php");

class EmployeeLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	
	
	public static function displayData(){
		$orgcode = Super::getParam("orgcode");
		if(!empty($orgcode))$where  = "orgcode ='".$orgcode."'";		
		else $where = null;
		
		$personid = Xbo::getString("personid");			
		Xbo::select("Staff", "personid");
		Xbo::select("Full Name", "fullname");
		Xbo::select("Title", "title");
		Xbo::select("Department", "department");
		Xbo::select("Section", "section");
		XboSet::getXboSet("employees", $where);
		XboSet::displayData();
	
	}
	
	public static function init(){
		EmployeeLookup::displayData();
	}
	
}

EmployeeLookup::init();




?>