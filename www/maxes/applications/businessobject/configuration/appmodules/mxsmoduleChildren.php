<?php 
require_once ("../../eat.php");

class MXSMODULEChildren {

	public static function init(){
		XboSet::validateChildren();	
		MXSMODULEChildren::displayData();
	}
	
	public static function displayData(){
		$getOwner = XboSet::getOwner();	
		$tableid = Xbo::getTableID();	
		if($tableid=="mxtable_mxsapp_child"){
			Xbo::select("Application Name", "appname");
			Xbo::select("Description", "description");
			Xbo::select("Module", "modulename");
			Xbo::select("URL", "url");
			Xbo::select("Sort", "sort");
			Xbo::select("Icon", "icon");
			XboSet::getXboSet("mxsapp", $getOwner);
			XboSet::displayData();
		}
		
		if($tableid=="mxtable_mxsaction_child") MXSMODULEChildren :: AppActions($getOwner);
		if($tableid=="mxtable_mxsimportdata_child") MXSMODULEChildren :: AppDataImport($getOwner);
		
		$linktable = Super::getParam("linktable");
		if(!empty($linktable)){	
			$where = Xbo::getLinkTableWhere($linktable); 
			$tableName = Xbo::getLinkTableName($linktable);
		
			if($tableName=="mxtable_mxsaction_child")  MXSMODULEChildren :: AppActions($where);
			if($tableName=="mxtable_mxsimportdata_child")  { MXSMODULEChildren :: AppDataImport($where); }
		}
	}
	
	public static function AppActions($param){
			Xbo::select("Action Name", "actionname");
			Xbo::select("Description", "description");
			Xbo::select("Sort", "sort");
			XboSet::getXboSet("mxsaction", $param);
			XboSet::displayData();
	}
	
	public static function AppDataImport($param){
			
			Xbo::select("File Name", "filename");
			Xbo::select("Object", "objectname");
			Xbo::select("Action", "actionname");
			Xbo::select("Description", "description");
			XboSet::getXboSet("mxsimportdata", $param);
			XboSet::displayData();
	}
	
}

MXSMODULEChildren::init();

?>