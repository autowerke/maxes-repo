<?php 
include ("../eat.php");

class WTStatus extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
					
		Xbo::select("Status", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks", "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='worktemplates'");
		XboSet::displayData();
	}
	public static function init(){
		WTStatus::displayData();
	}
	
}
WTStatus::init();
?>