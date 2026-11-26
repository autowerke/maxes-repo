<?php 
include ("../eat.php");

class Mxsaitableapps extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
					
		Xbo::select("Table Name", "tablename");
		Xbo::select("Application Name", "appname");
		XboSet::getXboSet("mxsaitableapps");
		XboSet::displayData();
	}
	public static function init(){
		Mxsaitableapps::displayData();
	}
	
}
Mxsaitableapps::init();
?>