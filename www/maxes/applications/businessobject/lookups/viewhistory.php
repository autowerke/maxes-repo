<?php 
include ("../eat.php");

class ViewHistory extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$Parts = null;
		
		$xboSet = Super::getParam("xboSet");
		if(!empty($xboSet)) $Parts = urldecode($xboSet);
		
		$part = explode("|", $Parts);
		$xboset = $part[0];
		$colvalue = explode("`", $part[1]);
		$column = $colvalue[0];
		$value =$colvalue[1];
		$clause = "$column = '$value'";
		
		Xbo::select("Status", "status");
		Xbo::select("Changed Date", "changedate", "date");
		Xbo::select("Changed By", "changeby");
		Xbo::select("Remarks",  "remarks");
		Xbo::select("Notify To", "notifcode");
		XboSet::getXboSet("$xboset", $clause);
		XboSet::displayData();
	}
	public static function init(){
		ViewHistory::displayData();
	}
	
}
ViewHistory::init();
?>