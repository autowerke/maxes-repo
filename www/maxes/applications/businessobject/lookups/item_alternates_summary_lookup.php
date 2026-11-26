<?php 
include ("../eat.php");

class ItemLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$itemnum = Super::getParam("itemnum");
		if(!empty($itemnum)) $where = "item_alternate_summary.itemnum='".$itemnum."'";
		else $where = null;
		
		if($where != null) {
			$where .= " and item_alternate_summary.total_quantity > 0";
		}
		
		
		Xbo::select("Item Alternate", "relateditem");
		Xbo::select("Description", "item.description");		
		Xbo::select("Specification", "item.spectext");
		Xbo::select("Item Group", "item.itemgroup");
		Xbo::select("Order Unit", "item.orderunit");
		Xbo::select("Issue Unit", "item.issueunit");
		Xbo::select("Current Stock", "total_quantity");
		Xbo::select("Warehouse", "warehouse");
		XboSet::getXboSet("item_alternate_summary", $where);
		XboSet::displayData(["item_alternate_summary.relateditem = item.itemnum"]);
	}
	public static function init(){
		ItemLookup::displayData();
	}
	
}
ItemLookup::init();
?>