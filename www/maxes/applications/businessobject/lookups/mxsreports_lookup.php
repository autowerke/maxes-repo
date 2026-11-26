<?php 
include ("../eat.php");

class MXSREPORTS extends Xbo {

	public function __destruct()
    {
		return null;
    }
	public static function displayData(){
		
		Xbo::select("Nama Report", "reportname");
		Xbo::select("Description", "description");
		Xbo::select("Application", "appname");
		XboSet::getXboSet("report");
		XboSet::displayData();
	}
	public static function init(){
		MXSREPORTS::displayData();
	}
	
}
MXSREPORTS::init();
?>