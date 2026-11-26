<?php 
include ("../eat.php");

class HeaderFooter extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		Xbo::select("Header", "header");
		Xbo::select("Footer", "footer");
		Xbo::select("Application", "appname");
		Xbo::select("Company", "orgcode");
		Xbo::select("Site", "sitecode");
		XboSet::getXboSet("headerfooter");
		XboSet::displayData();
	}
	public static function init(){
		HeaderFooter::displayData();
	}
	
}
HeaderFooter::init();
?>