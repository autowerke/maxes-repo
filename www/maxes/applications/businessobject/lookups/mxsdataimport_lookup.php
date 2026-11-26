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
		Xbo::select("File Name", "filename");
		Xbo::select("Load", "description");
		Xbo::select("Module", "modulename");
		Xbo::select("Nama Object", "objectname");
		Xbo::select("Application", "appname");
		Xbo::select("Action Name", "actionname");
		
	
		XboSet::getXboSet("mxsimportdata", $where);
		XboSet::displayData();
	}
	
	public static function init(){
		DataImport::displayData();
	}
	
}

DataImport::init();




?>