<?php 
include ("../eat.php");

class WFNODETYPE extends Xbo {

	public function __destruct()
    {
		return null;
    }
	public static function displayData(){
		
		Xbo::select("Node Type", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks",  "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='wfnodetype'");
		XboSet::displayData();
	}
	public static function init(){
		WFNODETYPE::displayData();
	}
	
}
WFNODETYPE::init();
?>