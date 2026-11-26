<?php 
require_once("actions.php");
header('Access-Control-Allow-Origin: *');
		
Class Action  {
	public static function Run(){
		if(isset($_POST["searchnotalist"])){
			ActionSet::SearchNotaList();
		}
		if(isset($_POST["searchexplist"])){
			ActionSet::SearchExpList();
		}
		if(isset($_POST["delNota"])){
			ActionSet::DeleteNota();
		}
	
		if(isset($_POST["modNota"])){
			ActionSet::ModifyNota();
		}
		
		if(isset($_POST["getNota"])){
			ActionSet::getNota($_POST["minid"], $_POST["length"]);
		}
		
		if(isset($_POST["entrynota"])){
			ActionSet::EntryNota();
		}
		
		if(isset($_POST["entryexpenses"])){
			ActionSet::EntryExpense();
		}
		
		
		if(isset($_POST["delpel"])){
			ActionSet::DeleteRecordPelanggan();
		}
		if(isset($_POST["deljenpeng"])){
			ActionSet::DeleteRecordJenisPengeluaran();
		}
		
		if(isset($_POST["deljenlau"])){
			ActionSet::DeleteRecordJenisLaundry();
		}
		if(isset($_POST["modjenlau"])){
			ActionSet::ModifyRecordJenisLaundry();
		}
		
		if(isset($_POST["deljenlay"])){
			ActionSet::DeleteRecordJenisLayanan();
		}
		if(isset($_POST["modjenlay"])){
			ActionSet::ModifyRecordJenisLayanan();
		}
		if(isset($_POST["modpel"])){
			ActionSet::ModifyRecordPelanggan();
		}
		
		if(isset($_POST["modpeng"])){
			ActionSet::ModifyRecordPengeluaran();
		}
		if(isset($_POST["modexp"])){
			ActionSet::ModifyExpense();
		}
		
		if(isset($_POST["dellaundrycost"])){
			ActionSet::DeleteRecordLaundryCost();
		}
		if(isset($_POST["getDataPelanggan"])) {
			ActionSet::getDataPelanggan($_POST["minid"], $_POST["length"]);
		}
		
		if(isset($_POST["getJenisLaundry"])) {
			ActionSet::getJenisLaundry($_POST["minid"], $_POST["length"]);
		}
		if(isset($_POST["getJenisLayanan"])){
			ActionSet::getJenisLayanan($_POST["minid"], $_POST["length"]);
		}
		
		if(isset($_POST["getLaundryCostType"])){
			ActionSet::getLaundryCostType($_POST["minid"], $_POST["length"]);
		}
		if(isset($_POST["getLaundryCost"])){
			ActionSet::getLaundryCost($_POST["minid"], $_POST["length"]);
		}
	
	}	
}


Action::Run();

?>