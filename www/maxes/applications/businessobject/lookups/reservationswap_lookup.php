<?php 
include ("../eat.php");

class ReservationSwapLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		$whereclause = null;
		
		if(isset($_GET['invreservenum'])) {
			$invreservenum = $_GET["invreservenum"];
			$whereclause = "(invreservenum='$invreservenum' and swapqty>0 ) or (requester_invreservenum='MANAGER_INQUIRY')";
		}
		
		
		Xbo::select("Requester invreservenum", "requester_invreservenum");
		Xbo::select("Swap Type", "swaptype");
		Xbo::select("Swap Qty", "swapqty", "decimal");
		Xbo::select("Status", "status");
		Xbo::select("Requester Message", "requestermsg");
		Xbo::select("Enter Date", "enterdate", "date");	
		Xbo::select("Enter By", "enterby");		
		
		XboSet::getXboSet("reservationswap", $whereclause);
		XboSet::displayData();	
	}
	public static function init(){
		ReservationSwapLookup::displayData();
	}
	
}
ReservationSwapLookup::init();
?>