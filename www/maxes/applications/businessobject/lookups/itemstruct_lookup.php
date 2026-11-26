<?php 
include ("../eat.php");

class ItemStruct extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$itemnum = Super::getParam("sitecode");
		if(!empty($itemnum)) $where = "itemnum='".$sitecode."'";
		else $where = null;
		
					
		Xbo::select("Item", "subitemnum");
		Xbo::select("Description", "item.description");		
		Xbo::select("Quantity", "quantity", "decimal");
		Xbo::select("Estimated Unit Cost", "unitcost", "decimal");
		XboSet::getXboSet("itemstruct", $where);
		XboSet::displayData(["itemstruct.subitemnum = item.itemnum"]);
	}
	public static function init(){
		ItemStruct::displayData();
	}
	
}
ItemStruct::init();
?>