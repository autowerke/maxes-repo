<?php 
include ("../eat.php");

class AssetParam extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
	
		$where =null;
		
		Xbo::select("Asset Parameter", "assetparamnum");
		Xbo::select("Description", "description");
		Xbo::select("Measure Unit", "measureunit");
		Xbo::select("Is Active?", "isactive");
		Xbo::select("Sort Order", "sortorder");
		XboSet::getXboSet("assetparam", $where);
		XboSet::displayData();
	}
	
	public static function init(){
		AssetParam::displayData();
	}
	
}

AssetParam::init();




?>