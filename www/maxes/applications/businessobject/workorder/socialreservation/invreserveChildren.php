<?php
require_once ("../../eat.php");

class ReservationSwapChildren {

	public static function init(){
		XboSet::validateChildren();
		ReservationSwapChildren::displayData();
	}


	public static function displayData(){
        $tableid = Xbo::getTableID();
        $getOwner = XboSet::getOwner();
        if($tableid=="mxtable_reservationswap_child"){ 
		
            Xbo::select("Enter Date", "enterdate", "date");
            Xbo::select("From", "enterby"); 
			Xbo::select("Swap Type", "swaptype");
			Xbo::select("Swap To", "requester_invreservenum");
            Xbo::select("Message", "requestermsg");
			Xbo::select("Status", "status");
            XboSet::getXboSet("reservationswap", $getOwner); 
			
            XboSet::displayData(); 
        }
	}
}

ReservationSwapChildren::init();
?>