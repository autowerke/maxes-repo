<?php 
include ("../eat.php");

class MatsaleLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		
		$itemnum = Super::getParam("itemnum");
		$warehouse = Super::getParam("warehouse");
		if(!empty($itemnum)) $where = "itemnum ='".$itemnum."' and issuetype in ('FIFO', 'LIFO', 'EXPDATE') and warehouse = '$warehouse'";	
		else  $where =null;
		Xbo::select("No. Trans.", "transnum");
		Xbo::select("Item", "itemnum");
		Xbo::select("Issue Qty", "quantity", "decimal");
		Xbo::select("Warehouse", "warehouse");
		Xbo::select("From Rack", "fromrack");
		Xbo::select("Unit Cost", "unitcost", "decimal");
		Xbo::select("Unit Price", "unitprice", "decimal");
		Xbo::select("Issue Type", "issuetype");
		Xbo::select("Batch Num.", "batchnum");
		Xbo::select("Expired Date", "expdate", "date");
		Xbo::select("Cost Date", "costdate", "date");
		Xbo::select("SO", "sonum");
		Xbo::select("Customer Code", "customercode");
		Xbo::select("Invoice", "invoicenum");
		Xbo::select("Returned Qty.", "_input_text_retqty", "decimal");
		Xbo::select("Warehouse", "_input_text_warehouse_warehouse");
		Xbo::select("Rack/Bin", "_input_text_racknum_racknum");
		Xbo::select("Invoice", "_input_text_refinvoicenum_invoicetrans");
		Xbo::select("Remarks", "_input_text_retremarks");
		XboSet::getXboSet("matsalereturn", $where);
		XboSet::displayData();
	
	}
	
	public static function init(){
		MatsaleLookup::displayData();
	}
	
}

MatsaleLookup::init();




?>