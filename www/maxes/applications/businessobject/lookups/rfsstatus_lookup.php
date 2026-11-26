<?php 
include ("../eat.php");

class RFSStatus extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
					
		Xbo::select("Status", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks", "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='rfs'");
		XboSet::displayData();
	}
	public static function init(){
		RFSStatus::displayData();
	}
	
}
RFSStatus::init();
?>