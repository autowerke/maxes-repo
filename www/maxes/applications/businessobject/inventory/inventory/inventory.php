<?php 
require_once ("../../eat.php");

class Inventory extends Xbo {
	public function __construct(){
		$this->itemnum = Xbo::getString("itemnum");
		$this->warehouse = Xbo::getString("warehouse");
	}
	public function __destruct(){
       return null;
    }
	public static function selectData(){
		Xbo::select("Item", "itemnum");
		Xbo::select("Description", "item.description");
		Xbo::select("Specification", "item.spectext");
		Xbo::select("Item Group", "item.itemgroup");
		Xbo::select("Warehouse", "warehouse");
		Xbo::select("Stock Quantity", "curbal", "decimal");
		Xbo::select("Safety Stock", "safetystock", "decimal");
		Xbo::select("Issue Unit", "item.issueunit");
		Xbo::select("Usage Ranking", "item.usageranking");
		Xbo::select("Lead Time", "leadtime", "decimal");
		Xbo::select("Site", "sitecode");
	}
	public static function displayData(){
		$defaultjoin = ["item.itemnum = inventory.itemnum and item.orgcode = inventory.orgcode and item.sitecode = inventory.sitecode"];
		if(isset($_GET['mxsdvsearch'])){
			$a = new Super();
			$b = $a->getWhere($_GET['mxsdvsearch'], "inventory");
			$jointable = $b->join;
			$where = $b->where;
			Inventory::selectData();
			XboSet::getXboSet("inventory", $where);
			renderData($defaultjoin, $jointable);	
		}else{
			Inventory::selectData();
			$whereclause = Super::IDWhereClause("inventoryid");
			XboSet::getXboSet("inventory", $whereclause);
			renderData($defaultjoin);	
		}	
	}

	public static function init(){
		error_reporting(0);
		$get = new Inventory();		
		$itemnum = $get->itemnum;
		$warehouse = $get->warehouse;
	
		if(isset($itemnum)) {
			XboSet::getXboSet("inventory", "itemnum = '$itemnum' and warehouse='$warehouse'");
			$result = Xboset::validate();
			
			if ($result->num_rows){
				$exec = Inventory::changes();
				}else{
				$exec = Inventory::add();
			}
		}
		
		Inventory::displayData();
	}
	
	
	public static function add(){
		XboSet::getXboSet("inventory");
		XboSet::add();
	}

	public static function changes(){
		$get = new Inventory();		
		$itemnum = $get->itemnum;
		$warehouse = $get->warehouse;
		
		XboSet::getXboSet("inventory", "itemnum = '$itemnum' and warehouse='$warehouse'");
		XboSet::changes();
	}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

Inventory::init();




?>