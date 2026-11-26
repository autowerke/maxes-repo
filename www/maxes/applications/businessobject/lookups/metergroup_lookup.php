<?php 
include ("../eat.php");

class MeterGroupLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	
	
	public static function displayData(){
		
				
		Xbo::select("Meter Group", "metergroupnum");
		Xbo::select("Description", "description");
		
		XboSet::getXboSet("metergroup");
		XboSet::displayData();
	
	}
	
	public static function init(){
		MeterGroupLookup::displayData();
	}
	
}

MeterGroupLookup::init();




?>