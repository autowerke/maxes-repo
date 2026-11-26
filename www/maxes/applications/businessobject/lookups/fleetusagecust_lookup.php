<?php 
include ("../eat.php");

class FleetUsageCust extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
	
		$fleetusagenum = Super::getParam("fleetusagenum");
		if(!empty($fleetusagenum)) $where =  "fleetusagenum ='".$fleetusagenum."'";	
		else $where = null;
		
		Xbo::select("No. Phone", "phone");
		Xbo::select("Nama Penumpang", "fullname");
		Xbo::select("Tipe Pembayaran", "paymenttype");
		Xbo::select("No. Transaksi", "transnum");
		Xbo::select("Alamat Jemput", "pickaddress");
		Xbo::select("Alamat Tujuan", "destaddress");
		XboSet::getXboSet("fleetusagecust", $where);
		XboSet::displayData();
	}
	public static function init(){
		FleetUsageCust::displayData();
	}
	
}
FleetUsageCust::init();
?>