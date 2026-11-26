<?php 
include ("../eat.php");

class appname extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("Application", "app");
		Xbo::select("Key Attribute", "keyattr");
		Xbo::select("Primary?", "isprimary");
		Xbo::select("Prefix", "prefix");
		Xbo::select("Nilai Selanjutnya", "nextval", "data-type='decimal'");
		Xbo::select("Suffix", "suffix");
		Xbo::select("Jumlah Digit", "pad");
		XboSet::getXboSet("mxsnextval");
		XboSet::displayData();
	}
	public static function init(){
		appname::displayData();
	}
	
}
appname::init();
?>