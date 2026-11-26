<?php 
include ("../eat.php");

class Banks extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		Xbo::select("Kode Bank", "bankcode");
		Xbo::select("Bank Name", "bankname");
		XboSet::getXboSet("banks");
		XboSet::displayData();
	}
	public static function init(){
		Banks::displayData();
	}
	
}
Banks::init();
?>