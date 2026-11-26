<?php 
include ("../eat.php");

class CountryLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("Kode Negara", "countrycode");
		Xbo::select("Nama Negara", "name");
		XboSet::getXboSet("country");
		XboSet::displayData();
	}
	public static function init(){
		CountryLookup::displayData();
	}
	
}
CountryLookup::init();
?>