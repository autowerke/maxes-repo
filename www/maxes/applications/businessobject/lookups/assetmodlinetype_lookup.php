<?php 
include ("../eat.php");

class AssetModificationLineType extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
					
		Xbo::select("Type", "value");
		Xbo::select("Description", "valuetext");
		Xbo::select("Remarks",  "remarks");
		XboSet::getXboSet("mxsattrvalue", "name='assetmodlinetype'");
		XboSet::displayData();
	}
	public static function init(){
		AssetModificationLineType::displayData();
	}
	
}
AssetModificationLineType::init();
?>