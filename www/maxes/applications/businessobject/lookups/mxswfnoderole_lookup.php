<?php 
include ("../eat.php");

class MXSWFNODEROLE extends Xbo {

	public function __destruct()
    {
		return null;
    }
	public static function displayData(){
		$wfname = Super::getParam("wfname");
		$nodeline = Super::getParam("nodeline");
		if(!empty($wfname))$where  = "wfname ='".$wfname."' and nodeline='$nodeline'";			
		else $where = null;
		Xbo::select("Role ID", "mxsrole");
		Xbo::select("Node Line", "nodeline");
		Xbo::select("Node Title", "nodetitle");
		Xbo::select("WF Name", "wfname");
		XboSet::getXboSet("mxswfnoderole", $where);
		XboSet::displayData();
	}
	public static function init(){
		MXSWFNODEROLE::displayData();
	}
	
}
MXSWFNODEROLE::init();
?>