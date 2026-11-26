<?php 
include ("../eat.php");

class ChangeInspection extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayCols(){
		$x = Super::getParam("changeinsp");
		$x = explode("|", $x);
		$itemnum = $x[0];
		$wh = $x[1];
		$orgcode = $x[2];
		$sitecode = $x[3];
		
		Xbo::select("Item", "itemnum");
		Xbo::select("Warehouse", "warehouse");
		Xbo::select("Rack/Bin", "racknum");
		Xbo::select("Current Stock", "curbal", "decimal");
		Xbo::select("Inspection Status", "inspectstatus");
		Xbo::select("Status Baru", "_input_text_inspectstatus_inspectstatus");
		XboSet::getXboSet("invbalance", "curbal>0 and itemnum='$itemnum' and warehouse='$wh' and orgcode='$orgcode' and sitecode='$sitecode'");
		XboSet::displayData();
		
	}
	public static function init(){
		ChangeInspection::displayCols();
	}
	
}
ChangeInspection::init();
?>