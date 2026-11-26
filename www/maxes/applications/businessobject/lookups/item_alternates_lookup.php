<?php 
include ("../eat.php");

class ItemLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$itemnum = Super::getParam("itemnum");
		if(!empty($itemnum)) $where = "itemalternate.itemnum='".$itemnum."'";
		else $where = null;
		
		if($where != null) {
			$where .= " and invfifolifocost.quantity > 0";
		}
		
		
		Xbo::select("Item Alternate", "relateditem");
		Xbo::select("Description", "item.description");		
		Xbo::select("Specification", "item.spectext");
		Xbo::select("Item Group", "item.itemgroup");
		Xbo::select("Order Unit", "item.orderunit");
		Xbo::select("Issue Unit", "item.issueunit");
		Xbo::select("Current Stock", "invfifolifocost.quantity");
		Xbo::select("Unit Cost", "invfifolifocost.unitcost", "decimal");
		Xbo::select("Warehouse", "invfifolifocost.warehouse");
		Xbo::select("Rack", "invfifolifocost.racknum");
		Xbo::select("Condition Type", "invfifolifocost.conditiontype");
		Xbo::select("Cost Date(FIFO/LIFO)", "invfifolifocost.costdate");
		XboSet::getXboSet("itemalternate", $where);
		XboSet::displayData(["itemalternate.relateditem = item.itemnum", "invfifolifocost.itemnum = itemalternate.relateditem"]);
	}
	public static function init(){
		ItemLookup::displayData();
	}
	
}
ItemLookup::init();
?>