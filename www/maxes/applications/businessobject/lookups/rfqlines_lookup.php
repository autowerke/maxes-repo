<?php 
include ("../eat.php");

class RFQLines extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayCols(){
		$x = Super::getParam("rfqnum");
		
		$x = explode("|", $x);
		$rfqnum = $x[0];
		$orgcode = $x[2];
		$sitecode = $x[3];
		
		Xbo::select("Line Num.", "rfqlinenum");
		Xbo::select("Type", "linetype");
		Xbo::select("Item", "itemnum");
		Xbo::select("Description", "description");
		Xbo::select("Quantity", "quantity");
		Xbo::select("Order Unit", "orderunit");
		XboSet::getXboSet("rfqline", "rfqnum='$rfqnum' ");
		XboSet::displayData();
		
	}
	public static function init(){
		RFQLines::displayCols();
	}
	
}
RFQLines::init();
?>