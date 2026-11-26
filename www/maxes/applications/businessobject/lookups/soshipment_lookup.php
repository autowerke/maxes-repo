<?php 
include ("../eat.php");

class SOShipmentLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$whereArr=array();
		$sonum = Super::getParam("sonum");
		if(!empty($sonum)) array_push($whereArr, "sonum ='".$sonum."'");
		else $where = null;
		
		
		$where = implode(" and ", $whereArr);
		Xbo::select("Tracking Num.", "trackingnum");
		Xbo::select("Address", "address");
		Xbo::select("Courier Name", "couriername");
		Xbo::select("Shipping Cost", "shippingcost", "decimal");
		Xbo::select("Received By", "receivedby");
		Xbo::select("Phone", "phone");
		Xbo::select("soshipmentid", "soshipmentid");
		
		XboSet::getXboSet("soshipment", $where);
		XboSet::displayData("distinct");
		
	}
	public static function init(){
		SOShipmentLookup::displayData();
	}
	
}
SOShipmentLookup::init();
?>