<?php
require_once ("../../../eat.php");

Class ActionSet {
	public static function ChangeTaskProgress(){
		
		$progress = $_POST["changetaskprogress"];
		$wonum = $_POST["wonum"];
		if(isset($_POST["linenum"])) {
			$linenum = "and tasklinenum='".$_POST["linenum"]."'";
			$mboset = "wotask";
		}
		else {
			$linenum = null;
			$mboset = "workorder";
		}
		$changedate = Xbo::setEnterDate();
		$changeby = Xbo::getLoginUser();
		
		$set = new Set();		
		$set->MboSetRemote($mboset)->setWhere("wonum='$wonum' $linenum")->setValue("progress", $progress)
		->setValue("changedate", $changedate)->setValue("changeby", $changeby)
		->change();
		Set::throws(["msg"=>"progress_updated"]);
		
	}
	public static function ChangeTaskDate(){
		
		$set = new Set();
		$newstartdate = $_POST["changetaskstartdate"];
		$newenddate = $_POST["changetaskenddate"];
		$wonum = $_POST["wonum"];
		$changedate = Xbo::setEnterDate();
		$changeby = Xbo::getLoginUser();
		
		if(isset($_POST["linenum"]))  {
			$line_num = $_POST["linenum"];
			$linenum = "and tasklinenum='".$_POST["linenum"]."'";
			$mboset = "wotask";
		}
		else {
			$linenum = null;	
			$mboset = "workorder";
		}
		
		if($line_num == $wonum){
			$set->MboSetRemote("workorder")->setWhere("wonum='$wonum'")->setValue("targstartdate", $newstartdate)->setValue("targcompdate", $newenddate)
			->setValue("changedate", $changedate)->setValue("changeby", $changeby)
			->change();
		}else{
			$set->MboSetRemote($mboset)->setWhere("wonum='$wonum' $linenum")->setValue("targstartdate", $newstartdate)->setValue("targcompdate", $newenddate)
			->setValue("changedate", $changedate)->setValue("changeby", $changeby)
			->change();
		}
		
		Set::throws(["msg"=>"changedate_updated"]);
	}
	public static function DrawActivities(){
		$wonum = $_POST["drawactivities"];
		
		$set = new Set();
		$woSet = $set->MboSetRemote("workorder")->setWhere("wonum='$wonum'")->MboRemote("targstartdate")->MboRemote("targcompdate")->MboRemote("description")
		->MboRemote("wonum")->MboRemote("progress")->getMboSet();
		
		$wo = $woSet->getMbo();
		$prop = new stdClass();
		$prop->start = $wo->getString("targstartdate");
		$prop->end= $wo->getString("targcompdate"); 
		$prop->name = $wo->getString("description"); 
		$prop->id = $wo->getString("wonum"); 
		$progress = $wo->getString("progress"); 
		if($progress==null || $progress == 0) $progress=10;
		$prop->progress = $progress;
		$prop->dependencies = null;
		$woacts = new stdClass();
		$woacts->wotask = null;
		
		$set2 = new Set();
		$wotaskSet = $set2->MboSetRemote("wotask")->setWhere("wonum='$wonum'")->MboRemote("wonum")->MboRemote("targstartdate")->MboRemote("targcompdate")->MboRemote("description")->MboRemote("progress")
		->MboRemote("status")->MboRemote("tasklinenum")->getMboSet();
		$cnt = $wotaskSet->counts();
		if($cnt>0){
			$wotaskr = [];
			
			$set = new Set();
			$WOSet = $set->MboSetRemote("workorder")->setWhere("wonum='$wonum'")->MboRemote("targstartdate")->MboRemote("targcompdate")->MboRemote("description")->MboRemote("progress")
			->MboRemote("status")
			->getMboSet();
			$WO = $WOSet->getMbo();
			$targstartdate = $WO->getString("targstartdate");
			$targcompdate = $WO->getString("targcompdate");
			$description = $WO->getString("description");
			$progress = $WO->getString("progress");
			$status = $WO->getString("status");
			
			$act = new stdClass();
			$act->start = $targstartdate;
			$act->end= $targcompdate; 
			$act->name = $description; 
			$act->id = $wonum; 
			$act->progress = $progress;
			$act->dependencies = null;			
			array_push($wotaskr, $act);
			
			
			for($i=0; $i<$cnt; $i++){
				$wotask = $wotaskSet->getMbo();
				$targstartdate = $wotask->getString("targstartdate");
				$targcompdate = $wotask->getString("targcompdate");
				$description = $wotask->getString("description");
				$progress = $wotask->getString("progress");
				$status = $wotask->getString("status");
				$tasklinenum = $wotask->getString("tasklinenum");
				$wonum = $wotask->getString("wonum");
				
				$act = new stdClass();
				$act->start = $targstartdate;
				$act->end= $targcompdate; 
				$act->name = $description; 
				$act->id = $tasklinenum; 
				$progress = $progress; 
				if($progress==null || $progress == 0) $progress=10;
				$act->progress = $progress;
				$act->dependencies = $wonum;
				
				array_push($wotaskr, $act);
			}
			
			
			$woacts->wotask = $wotaskr;
		}
		Set::throws($woacts);
	}
	public static function ChangeWOPriority(){
		$priority = $_POST["changewopriority"];
		$wonum = $_POST["wonum"];
		$changedate = Xbo::setEnterDate();
		$changeby = Xbo::getLoginUser();
		$set = new Set();
		$set->MboSetRemote("workorder")->setWhere("wonum='$wonum'")->setValue("priority", $priority)
		->setValue("changedate", $changedate)->setValue("changeby", $changeby)
		->change();
		
		Set::throws(["msg"=>"updated"]);
		
	}
	public static function GetWOPriority(){
		$set = new Set();
		$woprioritySet = $set->MboSetRemote("mxsattrvalue")->setWhere("name ='wopriority'")->MboRemote("value")->MboRemote("remarks")->getMboSet();
		$wonum = $_GET["getwopriority"];
		$cnt = $woprioritySet->counts();
		$trr = [];
		for($i=0; $i<$cnt; $i++){
			$wopriority = $woprioritySet->getMbo();
			$value = $wopriority->getString("value");
			$remarks = $wopriority->getString("remarks");
			$tr = "<tr><td  ><input type = 'radio' name='wopriority' value='$value'>  &nbsp; &nbsp; <b>".strtoupper($value)."</b></td> <td > $remarks </td> </tr>";
			array_push($trr, $tr);
		}
		$tr= implode($trr, "");
		$set = new Set();
		$woset = $set->MboSetRemote("workorder")->setWhere("wonum='$wonum'")->MboRemote("wonum")->MboRemote("priority")->getMboSet();
		$wo = $woset->getMbo();
		$priority = $wo->getString("priority");
		
		$table = "<table><tr><td>Work Order </td><td>: <b>$wonum</b></td></tr><tr><td>Current Priority</td><td>: <b>".strtoupper($priority)."</b> </td></tr></table><br><table class='portlettable' width=98%>".$tr."</table>";
		echo $table;
	}
	
	public static function ChangeDateScheduler(){
		$data = $_POST["timeline_changedate"];
		$newtargstartdate = $data["_start"];
		$newtargcompdate = $data["_end"];
		$wonum = $data["id"];
		$changedate = Xbo::setEnterDate();
		$changeby = Xbo::getLoginUser();
		$set = new Set();
		$set->MboSetRemote("workorder")->setWhere("wonum='$wonum'")->setValue("targstartdate", $newtargstartdate)->setValue("targcompdate", $newtargcompdate)
		->setValue("changedate", $changedate)->setValue("changeby", $changeby)
		->change();		
		Set::throws(["msg"=>"timeliner_updated"]);
	}
	public static function ChangeProgressScheduler(){
		$data = $_POST["timeline_changeprogress"];
		$progress = $data["progress"];
		$wonum = $data["id"];
		$changedate = Xbo::setEnterDate();
		$changeby = Xbo::getLoginUser();
		
		$set = new Set();
		$set->MboSetRemote("workorder")->setWhere("wonum='$wonum'")->setValue("progress", $progress)
		->setValue("changedate", $changedate)->setValue("changeby", $changeby)
		->change();		
		Set::throws(["msg"=>"timeliner_updated"]);
	}
	
	public static function renderWoSubs($taskr=[], $parentwo, $retrieve_children=null, $timelinernum = null){
	
		$topparent = ActionSet::RenderWOs("workorder.wonum='$parentwo'", "render_task", $timelinernum);
		$taskr = $topparent;
		$timelinernum = $timelinernum;
		$set = new Set();
		$workorderSet = $set->MboSetRemote("wosubs")->setRelationship("workorder", "wosubs.subwonum = workorder.wonum")->setRelationship("assets", "workorder.assetnum = assets.assetnum")
		->setWhere("wosubs.wonum='$parentwo'")->MboRemote("workorder.targstartdate")
		->MboRemote("workorder.targcompdate")->MboRemote("workorder.description")->MboRemote("wosubs.wonum")->MboRemote("wosubs.subwonum")->MboRemote("workorder.progress")
		->MboRemote("workorder.plannedcost")->MboRemote("workorder.actualcost")->MboRemote("workorder.rollupcost")->MboRemote("workorder.worktype")->MboRemote("workorder.workclass")
		->MboRemote("workorder.status")->MboRemote("workorder.statusdate")->MboRemote("assets.assetnum")->MboRemote("assets.description as assetdesc")
		->MboRemote("workorder.location as wolocation")->MboRemote("workorder.priority")
		->getMboSet();
		
		$wocount = $workorderSet->counts();
		
		$setp = new Set();
		$wotop = $setp->MboSetRemote("workorder")->setWhere("workorder.wonum='$parentwo'")->MboRemote("plannedcost")->MboRemote("actualcost")->getMboSet()->getMbo();
		$totalplannedcost = $wotop->getString("plannedcost");
		$totalactualcost = $wotop->getString("actualcost");
		
		for($i=0; $i<$wocount; $i++){
			$wo = $workorderSet->getMbo();
			$subwonum = $wo->getString("subwonum");
			$prop = new stdClass();
			$prop->start = $wo->getString("targstartdate");
			$prop->end= $wo->getString("targcompdate"); 
			$prop->name = $wo->getString("description"); 
			$prop->id = $wo->getString("subwonum"); 
			$progress = $wo->getString("progress"); 
			if($progress==null || $progress == 0) $progress=10;
			$prop->progress = $progress;
			$prop->dependencies = $parentwo;
			$priority = $wo->getString("priority"); 
			$prop->priority = $priority;
			if($priority == "emergency") $prop->custom_class = "bar-progress-red";
			if($priority == "urgent") $prop->custom_class = "bar-progress-orange";
			$prop->plannedcost =  $wo->getString("plannedcost"); 
			$prop->rollupcost =  $wo->getString("rollupcost"); 
			$prop->actualcost =  $wo->getString("actualcost"); 
			$prop->worktype =  $wo->getString("worktype"); 
			$prop->workclass =  $wo->getString("workclass"); 
			$prop->status =  $wo->getString("status"); 
			$prop->statusdate = $wo->getString("statusdate"); 
			$prop->assetnum = $wo->getString("assetnum"); 
			$prop->assetdesc = $wo->getString("assetdesc"); 
			$prop->wolocation =$wo->getString("wolocation"); 
			$totalplannedcost = $totalplannedcost+ $prop->plannedcost;
			$totalactualcost = $totalactualcost+ $prop->actualcost;
			
			$set2= new Set();
			$hasChildrenSet = $set2->MboSetRemote("wosubs")->setWhere("wonum='$subwonum'")->MboRemote("subwonum")->getMboSet();
			$countcset = $hasChildrenSet->counts();
			if($countcset > 0){
				$topparent2 = ActionSet::RenderWOs("workorder.wonum='$subwonum'", "render_task", $timelinernum);
				$merge = array_merge($taskr, $topparent2);
				$taskr = $merge;
				$settrun = new Set();
				
				for($j=0; $j<$countcset; $j++){
					$hasChildren = $hasChildrenSet->getMbo();
					$subwonum2 = $hasChildren->getString("subwonum");
					$newr = ActionSet::renderWoSubs($taskr, $subwonum2, "retrieve_children", $timelinernum);
					ActionSet::CheckWOList($timelinernum, $subwonum);
					$joinmerge = array_merge($taskr, $newr);
					$taskr = $joinmerge;
				}
			}else{
				ActionSet::CheckWOList($timelinernum, $subwonum);
				array_push($taskr, $prop);
			}
		}
		if($retrieve_children == null){
			$o = new stdClass();
			$o->tasks = $taskr;
			$o->totalplannedcost = $totalplannedcost;
			$o->totalactualcost = $totalactualcost;
			return $o;
		}else{
			if($retrieve_children == "retrieve_children"){
				$o = $taskr;
				return $o;
			}
		}
	}
	public static function CheckWOList($timelinernum, $wonum){
		$checkSet =new Set();
		$timelinerlineSet = $checkSet->MboSetRemote("timelinerline")->setWhere("timelinernum='$timelinernum' and wonum = '$wonum'")->MboRemote("timelinernum")->getMboSet();
		$countTL = $timelinerlineSet->counts();
		if($countTL == 0){
			$setins = new Set();
			$setins->MboSetRemote("timelinerline")->setValue("timelinernum", $timelinernum)->setValue("wonum", $wonum)->add();
		}else{
			return null;
		}
	}
	public static function RenderWOs($whereclause, $isrendercost=null, $timelinernum = null){
	
		$set = new Set();
		$workorderSet = $set->MboSetRemote("workorder")->setRelationship("assets","workorder.assetnum = assets.assetnum")->setWhere($whereclause)->MboRemote("workorder.wonum")
		->MboRemote("workorder.assetnum")->MboRemote("assets.description as assetdesc")->MboRemote("workorder.location")->MboRemote("workorder.description")->MboRemote("workorder.parent")->MboRemote("workorder.plannedcost")->MboRemote("workorder.statusdate")
		->MboRemote("workorder.status")->MboRemote("workorder.rollupcost")->MboRemote("workorder.actualcost")->MboRemote("workorder.worktype")->MboRemote("workorder.workclass")
		->MboRemote("workorder.targstartdate")->MboRemote("workorder.targcompdate")->MboRemote("workorder.priority")->MboRemote("workorder.progress")->getMboSet();
		$wocount = $workorderSet->counts();
		$timelinernum = $timelinernum;
		$taskr = [];
		$woprop = [];
		for($i=0; $i<$wocount; $i++){
			$wo = $workorderSet->getMbo();
			$wonum = $wo->getString("wonum");
			$description = $wo->getString("description");
			$parent = $wo->getString("parent");
			$plannedcost = $wo->getString("plannedcost");
			$rollupcost = $wo->getString("rollupcost");
			$actualcost = $wo->getString("actualcost");
			$worktype = $wo->getString("worktype");
			$workclass = $wo->getString("workclass");
			$targstartdate = $wo->getString("targstartdate");
			$targcompdate = $wo->getString("targcompdate");
			$progress = $wo->getString("progress");
			$status = $wo->getString("status");
			$statusdate = $wo->getString("statusdate");
			$assetnum = $wo->getString("assetnum");
			$assetdesc = $wo->getString("assetdesc");
			$wolocation = $wo->getString("location");
			$priority = $wo->getString("priority");
			
			$prop = new stdClass();
			$prop->start = $targstartdate;
			$prop->end= $targcompdate;
			$prop->name = $description;
			$prop->id = $wonum;
			if($progress==null || $progress == 0) $progress=10;
			$prop->progress = $progress;
			$prop->dependencies = $parent;
			if($priority == "emergency") $prop->custom_class = "bar-progress-red";
			if($priority == "urgent") $prop->custom_class = "bar-progress-orange";
			$prop->plannedcost = $plannedcost;
			$prop->rollupcost = $rollupcost;
			$prop->actualcost = $actualcost;
			$prop->worktype = $worktype;
			$prop->workclass = $workclass;
			$prop->status = $status;
			$prop->statusdate = $statusdate;
			$prop->assetnum = $assetnum;
			$prop->assetdesc = $assetdesc;
			$prop->wolocation = $wolocation;
			$prop->priority = $priority;
			array_push($taskr, $prop);
			
			ActionSet::CheckWOList($timelinernum, $wonum);
			
		}
		
		$set2 = new Set();
		$woSet =  $set2->MboSetRemote("workorder")->setWhere($whereclause)->MboRemote("sum(plannedcost) as totalplannedcost")->getMboSet();
		
		$wos = $woSet->getMbo();
		$totalplannedcost = $wos->getString("totalplannedcost");
		
		$set3 = new Set();
		$woSet2 =  $set3->MboSetRemote("workorder")->setWhere($whereclause)->MboRemote("sum(actualcost) as totalactualcost")->getMboSet();
		$wos2 = $woSet2->getMbo();
		$totalactualcost = $wos2->getString("totalactualcost");
		
		if($isrendercost == null){
			$o = new stdClass();
			$o->tasks = $taskr;
			$o->totalplannedcost = $totalplannedcost;
			$o->totalactualcost = $totalactualcost;
			return $o;	
		}else{
			if($isrendercost == "render_task"){
				return $taskr;
			}
		}
	}
	public static function CreateScheduler(){
		$targstartdate = Xbo::getDate($_POST["targstartdate"]);
		$targcompdate = Xbo::getDate($_POST["targcompdate"]);
		$owner = $_POST["owner"];
		$leader = $_POST["leader"];
		$worktype = $_POST["worktype"];
		$workclass = $_POST["workclass"];
		$timelinernum = $_POST["timelinernum"];
		
		$wodesc = $_POST["wodesc"];
		$parentwo = $_POST["parentwo"];
		$set = new Set();
		$wherer = [];
		$setrrun = new Set();
		$setrrun->MboSetRemote("timelinerline")->setWhere("timelinernum = '$timelinernum'")->remove();
		if(!empty($parentwo)){
			$set = new Set();
			$taskr = [];
			$result = ActionSet::renderWoSubs($taskr, $parentwo, null, $timelinernum);
			Set::throws($result);
			
		}else{
			if(!empty($targstartdate)) array_push($wherer, "targstartdate >= '$targstartdate'");
			if(!empty($targcompdate)) array_push($wherer, "targcompdate <= '$targcompdate'");
			if(!empty($owner)) array_push($wherer, "owner = '$owner'");
			if(!empty($leader)) array_push($wherer, "leader = '$leader'");
			if(!empty($worktype)) array_push($wherer, "worktype = '$worktype'");
			if(!empty($wodesc)) array_push($wherer, " workorder.description like '%$wodesc%'");
			$whereclause = implode($wherer, " and ");
			$o = ActionSet::RenderWOs($whereclause, null, $timelinernum);
			Set::throws($o);
		}
		
	}
} 


	

?>