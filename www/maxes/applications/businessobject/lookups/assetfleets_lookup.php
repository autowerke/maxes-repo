<?php 
include ("../eat.php");

class AssetFleets extends Xbo {

	public function __destruct()
    {
        return null;
    }
	
	
	public static function displayData(){
		
				
		Xbo::select("Armada", "assetnum");
		Xbo::select("Type", "assettype");
		Xbo::select("Jenis", "subtype");
		Xbo::select("Description", "description");
		Xbo::select("Status", "status");
		XboSet::getXboSet("assets", "assettype='transporter'");
		XboSet::displayData();
	
	}
	
	public static function init(){
		AssetFleets::displayData();
	}
	
}

AssetFleets::init();




?>