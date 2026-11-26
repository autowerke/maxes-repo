<?php 
include ("../eat.php");

class InvPriceLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$whereArr=array();
		
		$itemnum = Super::getParam("itemnum");
		if(!empty($itemnum)) array_push($whereArr, "itemnum ='".$itemnum."'");
		
		$warehouse = Super::getParam("warehouse");
		if(!empty($warehouse)) array_push($whereArr, "warehouse ='".$warehouse."'");	
		
		$fromwarehouse = Super::getParam("fromwarehouse");
		if(!empty($fromwarehouse)) array_push($whereArr, "warehouse ='".$fromwarehouse."'");
		
		$conditiontype = Super::getParam("conditiontype");
		if(!empty($conditiontype)) {
			$condval="conditiontype ='".$conditiontype."'";
			if ($conditiontype==""||empty($conditiontype)){
				$condval = "conditiontype is null";
			}
			array_push($whereArr, $condval);
		}
		
		
		$where = implode(" and ", $whereArr);
		
		Xbo::select("Price Code", "pricecode");
		Xbo::select("Condition Type", "conditiontype");
		Xbo::select("Unit Price", "unitprice", "decimal");
		Xbo::select("invpriceid", "invpriceid");
		
		XboSet::getXboSet("invprice", $where);
		XboSet::displayData("distinct");
	}
	public static function init(){
		InvPriceLookup::displayData();
	}
	
}
InvPriceLookup::init();
?>