<?php 
include ("../eat.php");

class OrganizationLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
					
		Xbo::select("Company", "orgcode");
		Xbo::select("Description", "description");
		
		XboSet::getXboSet("organization");
		XboSet::displayData();
	}
	public static function init(){
		OrganizationLookup::displayData();
	}
	
}
OrganizationLookup::init();
?>