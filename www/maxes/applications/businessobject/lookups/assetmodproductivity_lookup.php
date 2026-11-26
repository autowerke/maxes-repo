<?php 
include ("../eat.php");

class AssetModificationProductivity extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		if(isset($_GET['assetmodid'])){
			$where  = "assetmodid = '".Super::getParam("assetmodid")."'";
		}else{
			$where = null;
		}
		Xbo::select("Asset", "assetnum");
		Xbo::select("Prod. Parameter", "assetparamnum");
		Xbo::select("Description", "assetparam.description");
		Xbo::select("Potential Value", "value", "decimal");
		Xbo::select("Prediction Value", "estimatedvalue","decimal");
		Xbo::select("Measure Unit", "measureunit");
		Xbo::select("Est. Year", "estyear");
		XboSet::getXboSet("assetmodproductivity", $where);
		XboSet::displayData(["assetmodproductivity.assetparamnum = assetparam.assetparamnum"]);
	}
	public static function init(){
		AssetModificationProductivity::displayData();
	}
	
}
AssetModificationProductivity::init();
?>