<?php 
include ("../eat.php");

class ProcurerLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	
	
	public static function displayData(){
		$orgcode = Super::getParam("orgcode");
		if(!empty($orgcode))$where  = "orgcode ='".$orgcode."' and title='PROCURER'";		
		else $where = " title='PROCURER'";
		
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
		ProcurerLookup::displayData();
	}
	
}

ProcurerLookup::init();




?>