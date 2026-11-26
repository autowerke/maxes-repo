<?php 
include ("../eat.php");

class AssetProdHistory extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
	
		$where =null;
		
		$assetnum = Super::getParam("assetnum");
		$assetnum = ($assetnum !== null) ? $assetnum : '';
		

		$where = (!empty($assetnum))
			? "assetnum = '$assetnum'"
			: null;
			
		
		Xbo::select("Parameter", "assetparamnum");
		Xbo::select("Description", "assetparam.description");
		Xbo::select("Value", "value", "decimal");
		Xbo::select("Variance", "variance", "decimal");		
		Xbo::select("Change By", "changeby");
		Xbo::select("Change Date", "changedate", "date");
		XboSet::getXboSet("assetprodhistory", $where);
		XboSet::displayData(["assetprodhistory.assetparamnum = assetparam.assetparamnum"]);
	}
	
	public static function init(){
		AssetProdHistory::displayData();
	}
	
}

AssetProdHistory::init();




?>