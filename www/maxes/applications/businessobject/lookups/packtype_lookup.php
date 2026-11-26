<?php 
include ("../eat.php");

class PackType extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("Type", "packtype");
		Xbo::select("Description", "description");
		XboSet::getXboSet("packtype");
		XboSet::displayData();
	}
	public static function init(){
		PackType::displayData();
	}
	
}
PackType::init();
?>