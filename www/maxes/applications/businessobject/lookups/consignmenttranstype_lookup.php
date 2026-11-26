<?php 
include ("../eat.php");

class ConsigmentTransTypeLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
					
		Xbo::select("Tipe Konsinyasi", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks",  "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='consignmenttranstype'");
		XboSet::displayData();
	}
	public static function init(){
		ConsigmentTransTypeLookup::displayData();
	}
ConsigmentTransTypeLookup::init();
