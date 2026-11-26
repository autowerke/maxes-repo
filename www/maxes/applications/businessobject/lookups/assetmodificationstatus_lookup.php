<?php 
include ("../eat.php");

class AssetModificationStatus extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		Xbo::select("Status", "rootvalue");		
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks", "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='assetmodificationstatus'");
		XboSet::displayData();
	}
	public static function init(){
		AssetModificationStatus::displayData();
	}
	
}
AssetModificationStatus::init();
?>