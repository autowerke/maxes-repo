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
			$whereParts[] = "matusetrans.wonum = '" . $wonum . "'";
		}

		$assetnum = Super::getParam("assetnum");
		if (!empty($assetnum)) {
			$whereParts[] = "matusetrans.assetnum = '" . $assetnum . "'";
		}

		$itemnum = Super::getParam("itemnum");
		if (!empty($itemnum)) {
			$whereParts[] = "matusetrans.itemnum = '" . $itemnum . "'";
		}
		
		$whereParts[] = "transtype = 'ISSUE'";
		$where = !empty($whereParts) ? implode(" AND ", $whereParts) : null;
				
				
		
		
		Xbo::select("WO", "wonum");
		Xbo::select("Description", "workorder.description");
		Xbo::select("assetnum", "assetnum");
		Xbo::select("Item", "itemnum");
		Xbo::select("Item Description", "item.description");
		Xbo::select("Quantity", "quantity", "decimal");
		Xbo::select("Unit Cost", "unitcost", "decimal");
		Xbo::select("Line Cost", "linecost", "decimal");
		Xbo::select("MR", "mrnum");
		XboSet::getXboSet("matusetrans", $where);
		XboSet::displayData(["matusetrans.wonum = workorder.wonum", "item.itemnum = matusetrans.itemnum"]);
	
	}
	
	public static function init(){
		MatuseLookup::displayData();
	}
	
}

MatuseLookup::init();




?>