<?php 
include ("../eat.php");

class ItemReviewLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("Customer Code", "customercode");
		Xbo::select("Name", "nickname");
		Xbo::select("Rating", "Rating");
		Xbo::select("Title", "reviewtitle");
		Xbo::select("Review", "review");
		Xbo::select("Location", "location");
		Xbo::select("Age", "age");
		Xbo::select("Entered By", "enterby");
		Xbo::select("Approved?", "approved");
		//XboSet::getXboSet("itemreview", "itemnum='$itemnum'");
		XboSet::getXboSet("itemreview");
		XboSet::displayData();	
	}
	public static function init(){
		ItemReviewLookup::displayData();
	}
	
}
ItemReviewLookup::init();
?>