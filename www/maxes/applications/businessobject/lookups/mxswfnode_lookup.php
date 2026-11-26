<?php 
include ("../eat.php");

class MXSWFNODE extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$wfname = Super::getParam("wfname");
		if(!empty($wfname)) $where = "wfname='".$wfname."'";
		else $where = null;
		
		Xbo::select("Line Num.", "nodeline");			
		Xbo::select("Title", "title");
		Xbo::select("Description", "description");		
		Xbo::select("Node Type", "nodetype");
		
		XboSet::getXboSet("mxswfnode", $where);
		XboSet::displayData();
	}
	public static function init(){
		MXSWFNODE::displayData();
	}
	
}
MXSWFNODE::init();
?>