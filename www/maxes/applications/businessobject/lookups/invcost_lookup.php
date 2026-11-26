<?php 
include ("../eat.php");

class ItemConditionLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData() {
		$itemnum = trim(Super::getParam("itemnum"));
		$warehouse = trim(Super::getParam("warehouse"));
		
		$conditions = [];

		if (!empty($itemnum)) {
			$conditions[] = "itemnum = '{$itemnum}'";
		}

		if (!empty($itemnum) && !empty($warehouse)) {
			$conditions[] = "warehouse = '{$warehouse}'";
		}

		$where = implode(" AND ", $conditions);

		Xbo::select("Condition Type", "conditiontype");
		Xbo::select("Condition Type (%)", "conditionrate", "decimal");
		XboSet::getXboSet("invcost", $where);
		XboSet::displayData(null, "distinct");
	}

	public static function init(){
		ItemConditionLookup::displayData();
	}
	
}
ItemConditionLookup::init();
?>