<?php 
include ("../eat.php");

class ItemConditionLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$appname = Super::getParam("appname");
		if(!empty($appname)) $where  = "appname ='".$appname."'";	
		else $where = null;
				
		Xbo::select("Action", "actionname");
		Xbo::select("Description", "description");
		Xbo::select("Application", "appname");
		XboSet::getXboSet("mxsaction", $where);
		XboSet::displayData();
	}
	public static function init(){
		ItemConditionLookup::displayData();
	}
	
}
ItemConditionLookup::init();
?>