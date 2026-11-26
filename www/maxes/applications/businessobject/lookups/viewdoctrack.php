<?php 
include ("../eat.php");

class ViewFileTrack extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$Parts=null;
		$xboSet = Super::getParam("xboSet");
		if(!empty($xboSet)) $Parts = urldecode($xboSet);	
		$part = explode("|", $Parts);
		$xboset = $part[0];
		$colvalue = explode("`", $part[1]);
		$column = $colvalue[0];
		$value =$colvalue[1];
		$ownertable = $colvalue[2];
		
		Xbo::select("Process Num", "processnum");
		Xbo::select("Status", "status");
		Xbo::select("Sent By", "sentby");
		Xbo::select("Remarks",  "remarks");
		Xbo::select("Received By", "employees.fullname");
		Xbo::select("Accepted Date", "accepteddate", "date");
		Xbo::select("Delta(hours:min)", "delta");
		/*
		Xbo::select("Response By", "respby");
		Xbo::select("Resp. Status", "respstatus");		
		Xbo::select("Resp. Remarks", "respremarks");
		Xbo::select("Resp. Date", "respdate", "date");
		*/
		XboSet::getXboSet("$xboset", "$xboset.$column = '$value'");
		XboSet::displayData("employees.personid = $xboset.receivedby");
	}
	public static function init(){
		ViewFileTrack::displayData();
	}
	
}
ViewFileTrack::init();
?>