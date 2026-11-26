<?php 
include ("../eat.php");

class ItemConditionLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$itemnum = Super::getParam("itemnum");
		if(!empty($itemnum)) $where = "itemnum='".$itemnum."'";
		else $where = null;
		Xbo::select("Condition Type", "conditiontype");
		Xbo::select("Condition Type (%)", "conditionrate", "decimal");
		XboSet::getXboSet("itemcondition", $where);
		XboSet::displayData(null,"distinct");
	}
	public static function init(){
		ItemConditionLookup::displayData();
	}
	
}
ItemConditionLookup::init();
?>