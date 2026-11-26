<?php 
include ("../eat.php");

class MXSATTRVALUETYPE extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("Type", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks",  "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='mxsattrvaluetype'");
		XboSet::displayData();
	}
	public static function init(){
		MXSATTRVALUETYPE::displayData();
	}
	
}
MXSATTRVALUETYPE::init();
?>