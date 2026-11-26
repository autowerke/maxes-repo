<?php 
include ("../eat.php");

class SeatType extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("Type", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks", "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='seatconfig'");
		XboSet::displayData();
	}
	public static function init(){
		SeatType::displayData();
	}
	
}
SeatType::init();
?>