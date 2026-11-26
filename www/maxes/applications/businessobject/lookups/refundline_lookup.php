<?php 
include ("../eat.php");

class ItemSellable extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$refundnum = Super::getParam("refundnum");
		$where  = "refundnum='$refundnum'";
		Xbo::select("Line Num.", "refundlinenum");
		Xbo::select("Line Type", "linetype");
		Xbo::select("Item", "itemnum");
		Xbo::select("Description", "description");
		Xbo::select("Unit Cost", "unitcost");
		Xbo::select("Line Cost", "linecost");
		XboSet::getXboSet("refundline", $where);
		XboSet::displayData();	
	}
	public static function init(){
		ItemSellable::displayData();
	}
	
}
ItemSellable::init();
?>