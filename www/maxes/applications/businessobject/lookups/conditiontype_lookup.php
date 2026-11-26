<?php 
include ("../eat.php");

class ConditionTypeLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
					
		Xbo::select("Condition Type", "conditiontype");
		Xbo::select("Description", "description");
		Xbo::select("Condition Type (%)", "conditionrate", "decimal");
		XboSet::getXboSet("conditiontype");
		XboSet::displayData();
	}
	public static function init(){
		ConditionTypeLookup::displayData();
	}
	
}
ConditionTypeLookup::init();
?>