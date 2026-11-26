<?php 
include ("../eat.php");

class AssetSubType extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("kode", "seatcode");
		Xbo::select("Type", "seattype");
		Xbo::select("Description", "description");
		Xbo::select("Class", "class");
		XboSet::getXboSet("assetseat");
		XboSet::displayData();
	}
	public static function init(){
		AssetSubType::displayData();
	}
	
}
AssetSubType::init();
?>