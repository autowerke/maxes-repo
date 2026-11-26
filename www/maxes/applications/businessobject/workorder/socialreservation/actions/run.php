<?php 
require_once("actions.php");
header('Access-Control-Allow-Origin: *');
		
Class Action  {
	public static function Run(){
		
		
		if(isset($_POST["substituteAndReallocate"])) ActionSet::substituteAndReallocate();
		if(isset($_POST["releaseReservation"])) ActionSet::releaseReservation();
		if(isset($_POST["forceDescalation"])) ActionSet::forceDescalation();
		if(isset($_POST["sendInquiryDescalation"])) ActionSet::sendInquiryDescalation();
		if(isset($_POST["findBadActors"])) ActionSet::findBadActors();
		if(isset($_POST["applyReorderSafety"])) ActionSet::applyReorderSafety();
		if(isset($_POST["analyzeItem"])) ActionSet::analyzeItem();
		if(isset($_POST["initiateSwapRequest"]))  ActionSet::initiateSwapRequest();
		if(isset($_POST["getSentBy"]))  ActionSet::getSentBy();
		if(isset($_POST["acceptSubstitution"]))  ActionSet::acceptSubstitution();
		if(isset($_POST["alternates_offered"]))  ActionSet::notifyWaitingListUsers();
		if(isset($_POST["addtoCart"]))  ActionSet::addtoCart();
		if(isset($_POST["getItemDesc"]))  ActionSet::getItemDesc();
		if(isset($_POST["resourceconflict"]))  ActionSet::resourceConflict();
		if(isset($_POST["showHideButtons"])) ActionSet::showHideButtons();
		if(isset($_POST["revalidateall"])) ActionSet::revalidateall();
		if(isset($_POST["rejectNegotiation"])) ActionSet::rejectNegotiation();
		if(isset($_POST["approveNegotiation"])) ActionSet::approveNegotiation();
		if(isset($_POST["replyToNegotiation"])) ActionSet::replyToNegotiation();
		if(isset($_POST["negotiateReservation"])) ActionSet::negotiateReservation();
		
	}	
}
Action::Run();

?>