<?php 
include ("../eat.php");

class MXSWFTRACKING extends Xbo {

	public function __destruct()
    {
		return null;
    }
	public static function displayData(){
		$appname = Super::getParam("appname");
		$appkey = Super::getParam("appkey");
		if(!empty($appname))$where  = "appname ='".$appname."' and appkey='$appkey'";			
		else $where = null;
		$joinst = ["mxswfnode.wfname = mxswftracking.wfname and mxswfnode.nodeline = mxswftracking.currentnodeline"];
		Xbo::select("Workflow Name", "wfname");
		Xbo::select("Role", "rolevalue");
		Xbo::select("Instruction", "mxswfnode.description");
		Xbo::select("Action", "roleaction");
		Xbo::select("Node Line", "currentnodeline");
		Xbo::select("Changed By", "changeby");
		Xbo::select("Entered Date", "enterdate", "date");
		Xbo::select("Lead Time", "leadtime");
		XboSet::getXboSet("mxswftracking", $where);
		XboSet::displayData($joinst);
	}
	public static function init(){
		MXSWFTRACKING::displayData();
	}
	
}
MXSWFTRACKING::init();
?>