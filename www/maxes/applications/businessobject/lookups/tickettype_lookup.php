<?php 
include ("../eat.php");

class tickettype extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("Ticket Type", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks",  "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='tickettype'");
		XboSet::displayData();
	}
	public static function init(){
		tickettype::displayData();
	}
	
}
tickettype::init();
?>