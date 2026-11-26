<?php 
include ("../eat.php");

class MXSWFROLE extends Xbo {

	public function __destruct()
    {
		return null;
    }
	public static function displayData(){
		
		Xbo::select("Role Type", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks",  "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='mxswfroletype'");
		XboSet::displayData();
	}
	public static function init(){
		MXSWFROLE::displayData();
	}
	
}
MXSWFROLE::init();
?>