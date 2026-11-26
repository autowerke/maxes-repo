<?php 
include ("../eat.php");

class InvreserveLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		$sonum = Super::getParam("sonum");
		$where  = "sonum='$sonum'";
		Xbo::select("SO", "sonum");
		Xbo::select("Line", "solinenum");
		Xbo::select("Description", "description");
		Xbo::select("Qty", "sellqty", "decimal");
		Xbo::select("Distributed Qty", "distributedqty", "decimal");
		Xbo::select("Remaining Qty", "remainingqty", "decimal");
		Xbo::select("Assigned Qty", "_input_text_assignedqty", "decimal");
		Xbo::select("Workshop", "_input_text_location_locations");
		Xbo::select("Line Cost", "linecost", "decimal");
		Xbo::select("Distr. Line Cost", "_input_text_distrlinecost", "decimal");
		XboSet::getXboSet("v_solinewo", $where);
		XboSet::displayData();	
	}
	public static function init(){
		InvreserveLookup::displayData();
	}
	
}
InvreserveLookup::init();
?>