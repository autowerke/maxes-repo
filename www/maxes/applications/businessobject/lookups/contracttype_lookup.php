<?php 
include ("../eat.php");

class ContractType extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("Contract Type", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks",  "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='contracttype'");
		XboSet::displayData();
	}
	public static function init(){
		ContractType::displayData();
	}
	
}
ContractType::init();
?>