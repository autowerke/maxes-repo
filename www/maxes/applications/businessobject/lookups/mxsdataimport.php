<?php 
include ("../eat.php");

class DataImport extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		$where =null;
		$appname = Super::getParam("appname");
		if(!empty($appname) ) $where  = "appname ='".$appname."' ";	
		else $where = null;
		Xbo::select("Application", "appname");
		Xbo::select("Description", "description");
		Xbo::select("File Name", "filename");
		XboSet::getXboSet("mxsimportdata", $where);
		XboSet::displayData();
	
	}
	
	public static function init(){
		DataImport::displayData();
	}
	
}

DataImport::init();




?>