<?php 
include ("../eat.php");

class DocTicket extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
					
		Xbo::select("Status", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks", "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='docticket'");
		XboSet::displayData();
	}
	public static function init(){
		DocTicket::displayData();
	}
	
}
DocTicket::init();
?>