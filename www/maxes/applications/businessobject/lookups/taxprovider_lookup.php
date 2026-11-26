<?php 
include ("../eat.php");

class TaxProviderLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
					
		Xbo::select("Tax Provider", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks",  "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='taxprovider'");
		XboSet::displayData();
	}
	public static function init(){
		TaxProviderLookup::displayData();
	}
	
}
TaxProviderLookup::init();
?>