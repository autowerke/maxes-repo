<?php 
include ("../eat.php");

class AssetSubType extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("Tipe Asset", "assetsubtype");
		Xbo::select("Description", "description");
		XboSet::getXboSet("assetsubtype");
		XboSet::displayData();
	}
	public static function init(){
		AssetSubType::displayData();
	}
	
}
AssetSubType::init();
?>