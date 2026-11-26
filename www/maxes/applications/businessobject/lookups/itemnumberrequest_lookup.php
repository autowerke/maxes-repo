<?php 
include ("../eat.php");

class ItemNumberRequest extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
					
		Xbo::select("Object", "object");
		Xbo::select("Object ID", "objectkey");
		Xbo::select("Description",  "description");
		Xbo::select("Remarks",  "remarks");
		Xbo::select("Item Number",  "_input_text_itemnum_item");
		XboSet::getXboSet("itemrequest", "itemnum is null");
		XboSet::displayData();
	}
	public static function init(){
		ItemNumberRequest::displayData();
	}
	
}
ItemNumberRequest::init();
?>