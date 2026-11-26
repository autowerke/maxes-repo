<?php 
include ("../eat.php");

class MatuseLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		
		$whereParts = [];

		$wonum = Super::getParam("wonum");
		if (!empty($wonum)) {
			$whereParts[] = "wonum = '" . $wonum . "'";
		}

		$assetnum = Super::getParam("assetnum");
		if (!empty($assetnum)) {
			$whereParts[] = "assetnum = '" . $assetnum . "'";
		}

		$itemnum = Super::getParam("itemnum");
		if (!empty($itemnum)) {
			$whereParts[] = "itemnum = '" . $itemnum . "'";
		}

		// Gabungkan semua kondisi dengan 'AND'
		$where = !empty($whereParts) ? implode(" AND ", $whereParts) : null;
				
				
		
		
		Xbo::select("WO", "wonum");
		Xbo::select("Description", "description");
		Xbo::select("assetnum", "assetnum");
		Xbo::select("Item", "itemnum");
		Xbo::select("Quantity", "quantity", "decimal");
		Xbo::select("Unit Cost", "unitcost", "decimal");
		Xbo::select("Line Cost", "linecost", "decimal");
		Xbo::select("MR", "mrnum");
		XboSet::getXboSet("matusetrans", $where);
		XboSet::displayData();
	
	}
	
	public static function init(){
		MatuseLookup::displayData();
	}
	
}

MatuseLookup::init();




?>