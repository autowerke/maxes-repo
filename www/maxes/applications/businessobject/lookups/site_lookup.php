<?php 
include ("../eat.php");

class QualificationLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
					
		Xbo::select("Site", "sitecode");
		Xbo::select("Description", "description");
		Xbo::select("Site Contact", "sitecontact");
		
		XboSet::getXboSet("site");
		XboSet::displayData();
	}
	public static function init(){
		QualificationLookup::displayData();
	}
	
}
QualificationLookup::init();
?>