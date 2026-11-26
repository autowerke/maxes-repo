<?php 
include ("../eat.php");

class MXSModuleLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
					
		Xbo::select("Nama Modul", "modulename");
		Xbo::select("Description", "description");
		
		XboSet::getXboSet("mxsmodule");
		XboSet::displayData();
	}
	public static function init(){
		MXSModuleLookup::displayData();
	}
	
}
MXSModuleLookup::init();
?>