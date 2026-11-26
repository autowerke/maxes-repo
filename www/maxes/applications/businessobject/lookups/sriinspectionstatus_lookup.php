<?php 
include ("../eat.php");

class SRIInspectionStatus extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
					
		Xbo::select("Status", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks", "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='sriinspection'");
		XboSet::displayData();
	}
	public static function init(){
		SRIInspectionStatus::displayData();
	}
	
}
SRIInspectionStatus::init();
?>