<?php 
include ("../eat.php");

class EmailProtocolLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("Email Protocol", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks",  "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='emailprotocol'");
		XboSet::displayData();
	}
	public static function init(){
		EmailProtocolLookup::displayData();
	}
	
}
EmailProtocolLookup::init();
?>