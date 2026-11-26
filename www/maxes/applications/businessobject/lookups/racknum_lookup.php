<?php 
include ("../eat.php");

class RackLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$whereArr = array();
		$itemnum = Super::getParam("itemnum");
		$warehouse = Super::getParam("warehouse");
		$conditiontype = Super::getParam("conditiontype");

		if (!empty($itemnum)) {
			$whereArr[] = "itemnum = '" . $itemnum . "'";
		}

		if (!empty($warehouse)) {
			$whereArr[] = "warehouse = '" . $warehouse . "'";
		}

		if (!empty($conditiontype)) {
			$whereArr[] = "conditiontype = '" . $conditiontype . "'";
		}

		$where = implode(" AND ", $whereArr);  // Gunakan AND dengan huruf kapital
		if (!empty($where)) {
		  $where .= " AND racknum IS NOT NULL"; // Gunakan AND untuk menggabungkan kondisi
		} else {
		  $where = "racknum IS NOT NULL"; // Jika tidak ada kondisi lain, hanya gunakan racknum IS NOT NULL
		}

		Xbo::select("Rack/Bin", "racknum", $where); // Asumsikan Xbo::select menerima $where sebagai argumen
		Xbo::select("Warehouse", "warehouse", $where); // Asumsikan Xbo::select menerima $where sebagai argumen
		
		XboSet::getXboSet("v_invbalance", $where);
		XboSet::displayData(null, "distinct");
	}
	public static function init(){
		RackLookup::displayData();
	}
	
}
RackLookup::init();
?>