<?php 
include ("../eat.php");

class ItemGroupLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("Group Name", "groupname");
		Xbo::select("Description", "description");
		Xbo::select("Account Code", "glaccount");
		Xbo::select("Company", "orgcode");
		Xbo::select("Site", "sitecode");
		XboSet::getXboSet("itemgroup");
		XboSet::displayData();
	}
	public static function init(){
		ItemGroupLookup::displayData();
	}
	
}
ItemGroupLookup::init();
?>