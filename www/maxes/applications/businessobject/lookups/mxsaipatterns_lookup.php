<?php 
include ("../eat.php");

class Mxsaipatterns extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
					
		
		Xbo::select("Table Name", "pattern");
		Xbo::select("Description", "description");
		Xbo::select("Table Name", "tablename");
		Xbo::select("Target Column", "targetcolumn");
		XboSet::getXboSet("mxsaipatterns");
		XboSet::displayData();
	}
	public static function init(){
		Mxsaipatterns::displayData();
	}
	
}
Mxsaipatterns::init();
?>