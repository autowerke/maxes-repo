<?php 
include ("../eat.php");

class LaundryType2 extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		Xbo::select("Tipe Laundry", "laundrytype");
		Xbo::select("Description", "description");
		Xbo::select("Harga", "price", "decimal");
		XboSet::getXboSet("laundrytype2");
		XboSet::displayData();
	}
	public static function init(){
		LaundryType2::displayData();
	}
	
}
LaundryType2::init();
?>