<?php 
include ("../eat.php");

class Assets extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
					
		
		Xbo::select("Asset", "assetnum");
		Xbo::select("Description", "description");
		Xbo::select("Status", "status");
		Xbo::select("Location", "locnum");
		Xbo::select("Model Series", "modelseries");
		Xbo::select("Manufacturer", "manufacturer");
		XboSet::getXboSet("assets");
		XboSet::displayData();
	}
	public static function init(){
		Assets::displayData();
	}
	
}
Assets::init();
?>