<?php 
include ("../eat.php");

class RackLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$whereArr=array();
		
		$warehouse = Super::getParam("warehouse");
		if(!empty($warehouse)) array_push($whereArr, "warehouse ='".$warehouse."'");	
		
		if($warehouse == ""){
			$warehouse = Super::getParam("towarehouse");
			if(!empty($warehouse)) array_push($whereArr, "warehouse ='".$warehouse."'");	
		}
		$where = implode(" and ", $whereArr);
		if(isset($_GET["disassemble_warehouse"])) {
			$warehouse = $_GET["disassemble_warehouse"];
			$where = "warehouse='$warehouse'";
		}
		
		
		Xbo::select("Rack/Bin", "racknum");
		Xbo::select("Type", "racktype");
		Xbo::select("Warehouse", "warehouse");
		
		XboSet::getXboSet("warehouseline", $where);
		XboSet::displayData();
	}
	public static function init(){
		RackLookup::displayData();
	}
	
}
RackLookup::init();
?>