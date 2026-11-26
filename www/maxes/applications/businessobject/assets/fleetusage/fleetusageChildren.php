<?php 
require_once ("../../eat.php");



class FleetUsageChildren {

	public static function init(){
		XboSet::validateChildren();	
		FleetUsageChildren::displayData();
		
	}
	public static function displayData(){
		$getOwner = XboSet::getOwner();
	
		$tableid = Xbo::getTableID();
		if($tableid=="mxtable_fleetusagecust_child"){
			Xbo::select("Seat Num.", "seatnum");
			Xbo::select("Type", "seattype");
			Xbo::select("Seat Position (row_column)", "seatposition");
			Xbo::select("Passenger Name", "fullname");
			Xbo::select("Phonne", "phone");
			Xbo::select("Pick Address", "pickaddress");
			Xbo::select("Destination Address", "destaddress");
			Xbo::select("Unit Price", "unitprice", "decimal");
			Xbo::select("Additional Price", "additionalprice", "decimal");
			Xbo::select("Line  Price", "lineprice", "decimal");
			Xbo::select("Trans. Num", "transnum");
			Xbo::select("Payment Type", "paymenttype");
			Xbo::select("File Transfer", "filetransfer");
			XboSet::getXboSet("fleetusagecust", $getOwner);
			XboSet::displayData();
		}
		
		if($tableid=="mxtable_fleetusagepack_child"){
			Xbo::select("Package Number", "packnum");
			Xbo::select("Package Name", "packname");			;
			Xbo::select("Pick Address", "pickaddress");
			Xbo::select("Destination Address", "destaddress");
			Xbo::select("Unit Price", "unitprice", "decimal");
			Xbo::select("Additional Price", "additionalprice", "decimal");
			Xbo::select("Line Price", "lineprice", "decimal");
			Xbo::select("Dropped Date", "dropdate", "date");
			Xbo::select("Payment Type", "paymenttype");
			Xbo::select("Transaction Number", "transnum");
			XboSet::getXboSet("fleetusagepack", $getOwner);
			XboSet::displayData();
		}
		if($tableid=="mxtable_fleetusagecost_child"){
			Xbo::select("Num.", "fleetcostlinenum");
			Xbo::select("Description", "description");			;
			Xbo::select("Cost", "cost", "decimal");
			XboSet::getXboSet("fleetusagecost", $getOwner);
			XboSet::displayData();
		}
	}
}

FleetUsageChildren::init();

?>