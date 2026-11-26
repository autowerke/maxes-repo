<?php 
include ("../eat.php");

class ViewHistory extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$customercode = Super::getParam("customercode");
		$clause = "customercode = '$customercode'";
		Xbo::select("From Acc. Rep.", "fromsalesman");
		Xbo::select("To Acc. Rep", "tosalesman");
		Xbo::select("Remarks",  "remarks");
		
		Xbo::select("Changed Date", "changedate", "date");
		Xbo::select("Changed By", "changeby");
		Xbo::select("Requested By", "requestedby");
		XboSet::getXboSet("salesrephistory", $clause);
		XboSet::displayData();
	}
	public static function init(){
		ViewHistory::displayData();
	}
	
}
ViewHistory::init();
?>