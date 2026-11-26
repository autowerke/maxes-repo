<?php 
include ("../eat.php");

class ChangeInspection extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayCols(){
		$x = Super::getParam("viewins");
		$x = explode("|", $x);
		$itemnum = $x[0];
		$wh = $x[1];
		
		Xbo::select("Rack/Bin", "racknum");
		Xbo::select("PO", "ponum");
		Xbo::select("Status", "status");
		Xbo::select("Start Date", "startdate", "date");
		Xbo::select("Finish Date", "finishdate", "date");
		Xbo::select("Remarks",  "remarks");
		XboSet::getXboSet("inspectionstatus", "itemnum='$itemnum' and warehouse='$wh' ");
		XboSet::displayData();
		
	}
	public static function init(){
		ChangeInspection::displayCols();
	}
	
}
ChangeInspection::init();
?>