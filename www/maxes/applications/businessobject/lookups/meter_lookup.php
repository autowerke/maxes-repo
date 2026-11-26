<?php 
include ("../eat.php");

class MeterLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	
	
	public static function displayData(){
		
				
		Xbo::select("Meter", "meternum");
		Xbo::select("Description", "description");
		
		XboSet::getXboSet("meter");
		XboSet::displayData();
	
	}
	
	public static function init(){
		MeterLookup::displayData();
	}
	
}

MeterLookup::init();




?>