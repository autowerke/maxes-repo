<?php 
include ("../eat.php");

class AssetModificationMaintenance extends Xbo {

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
		Xbo::select("Current YTD Cost", "currentytdcost", "decimal");
		Xbo::select("YTD Cost Change", "ytdcostchange", "decimal");
		Xbo::select("YTD Prediction Value", "predictedytdcost", "decimal");
		Xbo::select("Remarks", "remarks");
		Xbo::select("Year", "year");
		Xbo::select("Est. Year", "estyear");
		XboSet::getXboSet("assetmodmaintenance", $where);
		XboSet::displayData();
	}
	public static function init(){
		AssetModificationMaintenance::displayData();
	}
	
}
AssetModificationMaintenance::init();
?>