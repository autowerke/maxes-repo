<?php 
include ("../eat.php");

class EnterAssetParam extends Xbo {

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
		Xbo::select("Actual Value", "_input_text_paramvalue");
		Xbo::select("Unit Cost", "_input_text_unitcost");
		XboSet::getXboSet("assetproductivity", $where);
		XboSet::displayData(["assetproductivity.assetparamnum = assetparam.assetparamnum"]);
	}
	
	public static function init(){
		EnterAssetParam::displayData();
	}
	
}

EnterAssetParam::init();




?>