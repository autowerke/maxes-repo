<?php 
include ("../eat.php");

class Precautions extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("Precaution ID", "precaution");
		Xbo::select("Description", "description");
		XboSet::getXboSet("precautions");
		XboSet::displayData();	
	}
	public static function init(){
		Precautions::displayData();
	}
	
}
Precautions::init();
?>