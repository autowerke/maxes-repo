<?php 
include ("../eat.php");

class ItemSubGroupLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$itemgroup = Super::getParam("itemgroup");
		if(!empty($itemgroup)) $where = "groupname ='".$itemgroup."'";
		else $where = null;
		
		
		
		Xbo::select("Sub Group Name", "subgroupname");
		Xbo::select("Description", "description");
		Xbo::select("Group Name", "groupname");
		XboSet::getXboSet("itemsubgroup", $where);
		XboSet::displayData();
	}
	public static function init(){
		ItemSubGroupLookup::displayData();
	}
	
}
ItemSubGroupLookup::init();
?>