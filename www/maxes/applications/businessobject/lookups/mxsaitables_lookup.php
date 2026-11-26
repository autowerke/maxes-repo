<?php 
include ("../eat.php");

class Mxsaitables extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
					
		
		Xbo::select("Table Name", "tablename");
		Xbo::select("Description", "description");
		Xbo::select("Ontology", "objectname");
		Xbo::select("Is entry point?", "isentrypoint");
		Xbo::select("Is Base Object?", "isbaseobject");
		XboSet::getXboSet("mxsaitables");
		XboSet::displayData();
	}
	public static function init(){
		Mxsaitables::displayData();
	}
	
}
Mxsaitables::init();
?>