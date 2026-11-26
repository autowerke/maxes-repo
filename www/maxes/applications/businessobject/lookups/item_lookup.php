<?php 
include ("../eat.php");

class ItemLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$sitecode = Super::getParam("sitecode");
		if(!empty($sitecode)) $where = "sitecode='".$sitecode."'";
		else $where = null;
		
					
		Xbo::select("Item", "itemnum");
		Xbo::select("Description", "description");		
		Xbo::select("Specification", "spectext");
		Xbo::select("Item Group", "itemgroup");
		Xbo::select("Current Stock", "curbal");
		Xbo::select("Order Unit", "orderunit");
		Xbo::select("Issue Unit", "issueunit");
		XboSet::getXboSet("item", $where);
		XboSet::displayData();
	}
	public static function init(){
		ItemLookup::displayData();
	}
	
}
ItemLookup::init();
?>