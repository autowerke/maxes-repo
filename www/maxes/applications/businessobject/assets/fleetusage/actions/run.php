<?php 
require_once("actions.php");
header('Access-Control-Allow-Origin: *');
		
Class Action  {
	public static function Run(){
		
		if(isset($_POST["showbuktitransfer"])) ActionSet::showbuktitransfer();
		if(isset($_POST["print_exp"])) ActionSet::PrintExpense();
		if(isset($_POST["print_exp_all"])) ActionSet::PrintExpenseAll();
		if(isset($_POST["printmanifest"])) ActionSet::PrintManifest();
		if(isset($_POST["startselling_pack"])) ActionSet::PrintSelling_Pack();
		if(isset($_POST["startselling"])) ActionSet::PrintSelling();
		if(isset($_POST["getfr"])) ActionSet::GetFR();
		if(isset($_POST["addpackageperkg"])) ActionSet::AddPackagePerKG();
		if(isset($_POST["checkexistingusage"]))ActionSet::CheckUsage();
		if(isset($_POST["searchdata"]))ActionSet::SearchData();
		if(isset($_POST["printseat"]))ActionSet::PrintSeat();
		if(isset($_POST["printpacket"]))ActionSet::PrintPacket();
		if(isset($_POST["packtransnum"]))ActionSet::packtransnum();
		if(isset($_POST["pooldestaddress"]))ActionSet::pooldestaddress();
		if(isset($_POST["poolpickaddress"]))ActionSet::poolpickaddress();
		if(isset($_POST["sumfleetcost"]))ActionSet::sumfleetcost();
		if(isset($_POST["seatconfig"]))ActionSet::SeatConfig();
		if(isset($_POST["getTransnum"]))ActionSet::getTransnum();
		if(isset($_POST["packtypeprice"]))ActionSet::PackTypePrice();
		if(isset($_POST["inputpessangers"]))ActionSet::InputPessangers();
		
		if(isset($_GET["delchildrow"])) ValidateTables::delchildrows();
	}	
}
Action::Run();

?>