<?php 
include ("../eat.php");

class taxLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$taxtype = Super::getParam("taxtype");
		if(!empty($taxtype)) $where = "taxtype ='".$taxtype."'";
		else $where = null;
		
		Xbo::select("Tax Code", "taxcode");
		Xbo::select("Description", "description");
		Xbo::select("Value", "value", "decimal");
		Xbo::select("Tax Type", "taxtype");
		XboSet::getXboSet("tax", "taxtype = 'purchasing'");
		XboSet::displayData();
	}
	public static function init(){
		taxLookup::displayData();
	}
	
}
taxLookup::init();
?>