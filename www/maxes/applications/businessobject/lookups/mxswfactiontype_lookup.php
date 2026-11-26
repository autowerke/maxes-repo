<?php 
include ("../eat.php");

class mxswfactiontype extends Xbo {

	public function __destruct()
    {
		return null;
    }
	public static function displayData(){
		
		Xbo::select("Tipe Attribute", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks",  "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='mxswfactiontype'");
		XboSet::displayData();
	}
	public static function init(){
		mxswfactiontype::displayData();
	}
	
}
mxswfactiontype::init();
?>