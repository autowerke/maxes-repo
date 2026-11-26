<?php 
include ("../eat.php");

class MXSWFROLE extends Xbo {

	public function __destruct()
    {
		return null;
    }
	public static function displayData(){
		
		Xbo::select("Role", "mxsrole");
		Xbo::select("Type", "type");
		Xbo::select("Application", "appname");
		Xbo::select("Value", "value");
		Xbo::select("Email ?", "isemail");
		Xbo::select("Broadcast ?", "isbroadcast");
		Xbo::select("Follow Up Rule", "followuprule");
		XboSet::getXboSet("mxswfrole");
		XboSet::displayData();
	}
	public static function init(){
		MXSWFROLE::displayData();
	}
	
}
MXSWFROLE::init();
?>