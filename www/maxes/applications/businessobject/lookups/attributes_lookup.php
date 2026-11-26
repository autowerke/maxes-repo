<?php 
include ("../eat.php");

class AttributesLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		Xbo::select("Attribute Name", "attributename");
		Xbo::select("Description", "description");
		XboSet::getXboSet("attribute");
		XboSet::displayData();
	}
	public static function init(){
		AttributesLookup::displayData();
	}
}
AttributesLookup::init();
?>