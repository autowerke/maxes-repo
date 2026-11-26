<?php 
include ("../eat.php");

class LaundryType extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		Xbo::select("Tipe Layanan", "laundrytype");
		Xbo::select("Description", "description");
		Xbo::select("Harga", "price", "decimal");
		XboSet::getXboSet("laundrytype");
		XboSet::displayData();
	}
	public static function init(){
		LaundryType::displayData();
	}
	
}
LaundryType::init();
?>