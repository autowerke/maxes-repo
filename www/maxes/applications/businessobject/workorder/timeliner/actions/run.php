<?php 
require_once("actions.php");
header('Access-Control-Allow-Origin: *');
		
Class Action  {
	public static function Run(){
		if(isset($_POST["changetaskprogress"])) ActionSet::ChangeTaskProgress();
		if(isset($_POST["changetaskstartdate"])) ActionSet::ChangeTaskDate();
		if(isset($_POST["drawactivities"])) ActionSet::DrawActivities();
		if(isset($_POST["changewopriority"])) ActionSet::ChangeWOPriority();
		if(isset($_POST["timelinernum"])) ActionSet::CreateScheduler();
		if(isset($_POST["timeline_changedate"])) ActionSet::ChangeDateScheduler();
		if(isset($_POST["timeline_changeprogress"])) ActionSet::ChangeProgressScheduler();
		
		
		if(isset($_GET["getwopriority"])) ActionSet::GetWOPriority();
		
		
	}	
}
Action::Run();

?>