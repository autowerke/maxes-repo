<?php 
include ("../eat.php");

class MXSWFREPORTS extends Xbo {

	public function __destruct()
    {
		return null;
    }
	public static function displayData(){
		$wfname = Super::getParam("wfname");
		$nodeline = Super::getParam("nodeline");
		if(!empty($wfname))$where  = "wfname ='".$wfname."' and nodeline='$nodeline'";			
		else $where = null;
		Xbo::select("Nama Report", "reportname");
		Xbo::select("Node Line", "nodeline");
		Xbo::select("Node Title", "nodetitle");
		Xbo::select("WF Name", "wfname");
		XboSet::getXboSet("MXSWFREPORTS", $where);
		XboSet::displayData();
	}
	public static function init(){
		MXSWFREPORTS::displayData();
	}
	
}
MXSWFREPORTS::init();
?>