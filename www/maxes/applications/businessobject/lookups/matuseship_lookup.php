<?php 
include ("../eat.php");

class MatuseshipLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		
		$wonum = Super::getParam("wonum");
		
		if(!empty($wonum)) $where = " wonum ='".$wonum."' and transtype in ('issue') and (shipstatus not in ('DELIVERED') or shipstatus is null) ";	
		else  $where =null;
		
		Xbo::select("Item", "itemnum");
		Xbo::select("Name", "item.description");
		Xbo::select("Jml. Dikirim", "quantity", "decimal");
		Xbo::select("Unit Cost", "unitcost", "decimal");
		Xbo::select("Line Cost", "linecost", "decimal");
		Xbo::select("Issue Type", "issuetype");
		Xbo::select("Batch Num.", "batchnum");
		Xbo::select("Tgl. Masuk", "costdate", "date");
		Xbo::select("Ship Agent", "_input_text_shipagent");
		Xbo::select("Remarks",  "_input_text_remarks");
		Xbo::select("Long.Lat.", "_input_text_longlat");
		Xbo::select("Status Pengiriman", "_input_text_shipstatus_shipstatuswo");
		Xbo::select("Tgl. Kirim", "_input_text_actualdate", "date");
		XboSet::getXboSet("matusetrans", $where);
		XboSet::displayData(["matusetrans.itemnum = item.itemnum"]);
	
	}
	
	public static function init(){
		MatuseshipLookup::displayData();
	}
	
}

MatuseshipLookup::init();




?>