<?php 
include ("../eat.php");

class TicketStatus extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
					
		Xbo::select("Status", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks", "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='ticketstatus'");
		XboSet::displayData();
	}
	public static function init(){
		TicketStatus::displayData();
	}
	
}
TicketStatus::init();
?>