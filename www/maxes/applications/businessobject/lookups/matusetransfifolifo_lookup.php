<?php 
include ("../eat.php");

class MatuseLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		
		$itemnum = Super::getParam("itemnum");
		$warehouse = Super::getParam("warehouse");
		if(!empty($itemnum)) $where = "itemnum ='".$itemnum."' and issuetype in ('FIFO', 'LIFO', 'AVG', 'EXPDATE') and warehouse = '$warehouse'";	
		else  $where =null;
		
		Xbo::select("Item", "itemnum");
		Xbo::select("Issue Qty Terakhir", "quantity", "decimal");
		Xbo::select("Condition Type", "conditiontype");
		Xbo::select("Warehouse", "warehouse");
		Xbo::select("Rack/Bin", "racknum");
		Xbo::select("Unit Cost", "unitcost", "decimal");
		Xbo::select("Line Cost", "linecost", "decimal");
		Xbo::select("Issue Type", "issuetype");
		Xbo::select("Batch Num.", "batchnum");
		Xbo::select("Expired Date", "expireddate", "date");
		Xbo::select("Cost Date", "costdate", "date");
		Xbo::select("WO", "wonum");
		Xbo::select("Returned Qty.", "_input_text_retqty", "decimal");
		Xbo::select("Warehouse", "_input_text_towarehouse_warehouse");
		Xbo::select("Rack/Bin", "_input_text_toracknum_rack@towarehouse");
		Xbo::select("Remarks", "_input_text_remarks");
		Xbo::select("Tgl. Return", "_input_text_issuedate", "date");
		XboSet::getXboSet("matusereturn", $where);
		XboSet::displayData();
	
	}
	
	public static function init(){
		MatuseLookup::displayData();
	}
	
}

MatuseLookup::init();




?>