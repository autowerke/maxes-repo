<?php 
include ("../eat.php");

class DocCategoryLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("Num.", "doccategorynum");
		Xbo::select("Category Name", "doccategoryname");
		Xbo::select("Name", "categorycode");
		Xbo::select("Description", "description");
		Xbo::select("Remarks", "remarks");
		XboSet::getXboSet("doccategory");
		XboSet::displayData();
	}
	public static function init(){
		DocCategoryLookup::displayData();
	}
	
}
DocCategoryLookup::init();
?>