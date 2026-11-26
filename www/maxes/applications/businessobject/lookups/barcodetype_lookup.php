<?php 
include ("../eat.php");

class BarcodeType extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("Tipe Barcode", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks", "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='barcodetype'");
		XboSet::displayData();
	}
	public static function init(){
		BarcodeType::displayData();
	}
	
}
BarcodeType::init();
?>