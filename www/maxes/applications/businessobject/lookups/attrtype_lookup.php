<?php 
include ("../eat.php");

class AttributeType extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("Tipe Attribute", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks", "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='attrtype'");
		XboSet::displayData();
	}
	public static function init(){
		AttributeType::displayData();
	}
	
}
AttributeType::init();
?>