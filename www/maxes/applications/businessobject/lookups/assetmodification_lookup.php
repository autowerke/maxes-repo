<?php 
include ("../eat.php");

class AssetModification extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
					
		Xbo::select("Subject", "subject");
		Xbo::select("Description", "description");
		Xbo::select("Status", "status");
		Xbo::select("Is Approved?", "isapproved");
		XboSet::getXboSet("assetmod");
		XboSet::displayData();
	}
	public static function init(){
		AssetModification::displayData();
	}
	
}
AssetModification::init();
?>