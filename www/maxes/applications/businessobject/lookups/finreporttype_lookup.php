<?php 
include ("../eat.php");

class FinReportType extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("Financial Report Type", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks",  "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='finreporttype'");
		XboSet::displayData();
	}
	public static function init(){
		FinReportType::displayData();
	}
	
}
FinReportType::init();
?>