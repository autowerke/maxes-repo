<?php
include ("../eat.php");

class sri_summary extends Xbo {

	public function __destruct()
    {
        return null;
    }

	public static function displayData(){
		$srinum = Super::getParam("srinum");
		$where = null;

		if (!empty($srinum)) {
			$where = "sri_summary.srinum = '$srinum' and sri_summary.remaining_qty >0";
		}
		
		Xbo::select("Line", "srilinenum");
		Xbo::select("PO", "ponum"); 
		Xbo::select("PO Line", "polinenum"); 
		Xbo::select("Description", "description"); 
		Xbo::select("Technician", "staffname");
		Xbo::select("Order Qty", "orderqty", "decimal"); 
		Xbo::select("Order Unit", "orderunit"); 
		Xbo::select("Inspected Qty", "total_inspection_qty", "decimal"); 
		Xbo::select("Remaining Qty", "remaining_qty", "decimal"); 
		XboSet::getXboSet("sri_summary", $where);
		XboSet::displayData();
	
	}
	
	public static function init(){
		sri_summary::displayData();
	}
	
}

sri_summary::init();