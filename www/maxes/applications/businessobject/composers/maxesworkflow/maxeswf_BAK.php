<?php
/**
 * Main class for Workflow Engine
 */
 
 $composer_maxesworkflowExtend = 'maxeswf_extend.php';
 require_once $composer_maxesworkflowExtend;
 
class MaxesWorkflow {
	public static function TotalAssignments()
	{
	}

	public static function IsMultiConditionMeet($nodeline, $wfname, $table, $tableid)
	{
		// Ambil semua branch, urutkan by sortorder ASC, default branch paling akhir
	
		$branchSet = (new Set())->useTransactionManager()
			->MboSetRemote("mxswfaction")
			->setWhere("wfname='$wfname' AND nodeline='$nodeline'")
			->MboRemote("tonodeline")
			->MboRemote("whereclause")
			->MboRemote("isdefault")
			->MboRemote("sortorder")
			->MboRemote("action")
			->MboRemote("title")
			->MboRemote("instruction")
			->order("isdefault ASC, sortorder ASC")  // default branch selalu paling akhir
			->getMboSet();

		$countBranch = $branchSet->counts();

		if ($countBranch === 0) {
			return null;
		}

		$defaultBranch = null;
		$matchedBranch = null;

		$realTable = ($table === 'receiving') ? 'po' : $table;
		$tableidname = $realTable . 'id';

		for ($i = 0; $i < $countBranch; $i++) {
			$branch = $branchSet->getMbo();

			$tonodeline   = $branch->getString("tonodeline");
			$whereclause  = $branch->getString("whereclause");
			$isdefault    = $branch->getString("isdefault");
			$action       = $branch->getString("action");
			$title        = $branch->getString("title");
			$instruction  = $branch->getString("instruction");

			// Simpan default branch sebagai fallback
			if ($isdefault === 'Y') {
				$defaultBranch = new stdClass();
				$defaultBranch->tonodeline  = $tonodeline;
				$defaultBranch->action      = $action;
				$defaultBranch->title       = $title;
				$defaultBranch->instruction = $instruction;
				continue; // jangan evaluasi whereclause-nya
			}

			// Skip branch tanpa whereclause
			if (empty($whereclause)) continue;

			// Evaluasi kondisi — sama dengan IsConditionMeet()
			try {
			
				$isMatch = (new Set())->useTransactionManager()
					->MboSetRemote($realTable)
					->setWhere("$tableidname = $tableid AND $whereclause")
					->MboRemote($tableidname)
					->getMboSet()
					->counts();

				if ($isMatch > 0) {
					// First match — langsung return
					$matchedBranch = new stdClass();
					$matchedBranch->tonodeline  = $tonodeline;
					$matchedBranch->action      = $action;
					$matchedBranch->title       = $title;
					$matchedBranch->instruction = $instruction;
					break;
				}
			} catch (Exception $e) {
				// Log error evaluasi kondisi, lanjut ke branch berikutnya
				error_log("[IsMultiConditionMeet] Error evaluating branch for node $nodeline: " . $e->getMessage());
				continue;
			}
		}

		// Return matched branch, atau fallback ke default
		if ($matchedBranch !== null) return $matchedBranch;
		if ($defaultBranch  !== null) return $defaultBranch;

		return null;
	}

	public function buildActionSet($isCondition, $tonl, $wfname, $table, $tableid)
    {
        $condmxswfactionSet = null;

        // ── EXISTING: CONDITION (binary) ──────────────────────
        if ($isCondition == "CONDITION") {
            $countisCondMeet = MaxesWorkflow::IsConditionMeet($tonl, $wfname, $table, $tableid);
            if ($countisCondMeet > 0) {
                $condwfObj = new MaxesWorkflow();
           
                $condtoNodeLineSet = (new Set())->useTransactionManager()
                    ->MboSetRemote("mxswfaction")
                    ->setWhere("wfname='$wfname' and isnegative='N' and nodeline='$tonl'")
                    ->MboRemote("tonodeline")
                    ->MboRemote("action")
                    ->getMboSet();
                $c2nl = $condtoNodeLineSet->getMbo();
                $condtoNodeLine = $c2nl->getString("tonodeline");
                $action = $c2nl->getString("action");
                $condmxswfactionSet = $condwfObj->sqlWf($tonl, $wfname, "and isnegative='N'");
            } else {
                $condwfObj = new MaxesWorkflow();
          
                $condtoNodeSet = (new Set())->useTransactionManager()
                    ->MboSetRemote("mxswfaction")
                    ->setRelationship("mxswfnode", "mxswfaction.wfname = mxswfnode.wfname and mxswfaction.tonodeline = mxswfnode.nodeline")
                    ->setWhere("mxswfaction.wfname='$wfname' and mxswfaction.isnegative='Y' and mxswfaction.nodeline='$tonl'")
                    ->MboRemote("mxswfaction.tonodeline")
                    ->MboRemote("mxswfnode.nodetype")
                    ->MboRemote("mxswfaction.action")
                    ->getMboSet();

                $condToNode     = $condtoNodeSet->getMbo();
                $condtoNodeLine = $condToNode->getString("tonodeline");
                $nodetype       = $condToNode->getString("nodetype");
                $action         = $condToNode->getString("action");

                if ($nodetype == "TASK") {
                    $condmxswfactionSet = $condwfObj->sqlWf($tonl, $wfname, "and isnegative='Y'");
                } else {
                    if ($nodetype == "CONDITION") {
                        $countisCondMeet = MaxesWorkflow::IsConditionMeet($condtoNodeLine, $wfname, $table, $tableid);
                        $a = new MaxesWorkflow();
                        $b = $a->buildActionSet($nodetype, $condtoNodeLine, $wfname, $table, $tableid);
                        $condmxswfactionSet = $b->mxswfactionSet;
                        $condtoNodeLine     = $b->newcurrentnodeline;
                    }
                    // ── NEW: nested MULTICONDITION di dalam CONDITION ──
                    if ($nodetype == "MULTICONDITION") {
                        $a = new MaxesWorkflow();
                        $b = $a->buildActionSet($nodetype, $condtoNodeLine, $wfname, $table, $tableid);
                        $condmxswfactionSet = $b->mxswfactionSet;
                        $condtoNodeLine     = $b->newcurrentnodeline;
                    }
                }
            }

            $this->mxswfactionSet    = $condmxswfactionSet;
            $this->newcurrentnodeline = $condtoNodeLine;
            $this->roleaction        = $action;
        }

        // ── NEW: MULTICONDITION (multi-branch) ────────────────
        elseif ($isCondition == "MULTICONDITION") {

            $matchedBranch = MaxesWorkflow::IsMultiConditionMeet($tonl, $wfname, $table, $tableid);

            if ($matchedBranch === null) {
                // Tidak ada branch yang match dan tidak ada default — dead end
                error_log("[buildActionSet] MULTICONDITION node $tonl: no branch matched and no default branch defined.");
                $this->mxswfactionSet    = null;
                $this->newcurrentnodeline = null;
                $this->roleaction        = null;
                return $this;
            }

            $matchedNodeline = $matchedBranch->tonodeline;
            $action          = $matchedBranch->action;

            // Cek apakah target dari branch ini juga sebuah condition (nested)
         
            $targetType = (new Set())->useTransactionManager()
                ->MboSetRemote("mxswfnode")
                ->setWhere("wfname='$wfname' AND nodeline='$matchedNodeline'")
                ->MboRemote("nodetype")
                ->getMboSet()
                ->getMbo()
                ->getString("nodetype");

            if ($targetType === "CONDITION" || $targetType === "MULTICONDITION") {
                // Target adalah condition lain — rekursif
                $a = new MaxesWorkflow();
                $b = $a->buildActionSet($targetType, $matchedNodeline, $wfname, $table, $tableid);
                $this->mxswfactionSet    = $b->mxswfactionSet;
                $this->newcurrentnodeline = $b->newcurrentnodeline;
                $this->roleaction        = $b->roleaction;
            } else {
                // Target adalah TASK/INPUT/STOP — build action set dari matched nodeline
                $condwfObj = new MaxesWorkflow();
                $this->mxswfactionSet    = $condwfObj->sqlWf($tonl, $wfname, "AND sortorder = (SELECT MIN(sortorder) FROM mxswfaction WHERE wfname='$wfname' AND nodeline='$tonl' AND tonodeline='$matchedNodeline')");
                $this->newcurrentnodeline = $matchedNodeline;
                $this->roleaction        = $action;
            }
        }

        // ── ELSE: bukan condition ──────────────────────────────
        else {
            $this->mxswfactionSet    = null;
            $this->newcurrentnodeline = null;
            $this->roleaction        = null;
        }

        return $this;
    }
		
	public function ReplaceConditionOption($tonl, $wfname, $table, $tableid)
    {
        $wfname  = $wfname;
        $table   = $table;
        $tableid = $tableid;

        // Cek nodetype dari tonl
   
        $nodeType = (new Set())->useTransactionManager()
            ->MboSetRemote("mxswfnode")
            ->setWhere("nodeline='$tonl' AND wfname='$wfname'")
            ->MboRemote("nodetype")
            ->getMboSet()
            ->getMbo()
            ->getString("nodetype");

        // ── NEW: Handle MULTICONDITION ─────────────────────────
        if ($nodeType === "MULTICONDITION") {
            $matchedBranch = MaxesWorkflow::IsMultiConditionMeet($tonl, $wfname, $table, $tableid);

            if ($matchedBranch === null) {
                // Tidak ada match — return kosong
                $this->tonodeline  = null;
                $this->nodetype    = null;
                $this->action      = null;
                $this->instruction = null;
                return $this;
            }

            $this->ismeet    = 1;
            $this->tonodeline = $matchedBranch->tonodeline;
            $this->action     = $matchedBranch->action;
            $this->instruction = $matchedBranch->instruction;

            // Cek apakah target matched branch juga condition
          
            $targetNodetype = (new Set())->useTransactionManager()
                ->MboSetRemote("mxswfnode")
                ->setWhere("nodeline='$this->tonodeline' AND wfname='$wfname'")
                ->MboRemote("nodetype")
                ->getMboSet()
                ->getMbo()
                ->getString("nodetype");

            $this->nodetype = $targetNodetype;

            if ($targetNodetype === "CONDITION" || $targetNodetype === "MULTICONDITION") {
                // Rekursif
                $e = new MaxesWorkflow();
                $r = $e->ReplaceConditionOption($this->tonodeline, $wfname, $table, $tableid);
                $this->tonodeline  = $r->tonodeline;
                $this->nodetype    = $r->nodetype;
                $this->action      = $r->action;
                $this->instruction = $r->instruction;
            } else {
                // Target adalah TASK — ambil role
         
                $ssSet = (new Set())->useTransactionManager()
                    ->MboSetRemote("mxswfrole")
                    ->setRelationship("mxswfnoderole", "mxswfrole.mxsrole = mxswfnoderole.mxsrole")
                    ->setWhere("mxswfnoderole.wfname = '$wfname' AND mxswfnoderole.nodeline='$this->tonodeline'")
                    ->MboRemote("mxswfnoderole.mxsrole")
                    ->MboRemote("value")
                    ->MboRemote("type")
                    ->getMboSet();

                $ss = $ssSet->getMbo();
                $value = $ss->getString("value");
                $this->rolevalue = MaxesWorkflow::CheckUsernamePerson($value);
                $this->roleid    = $ss->getString("mxsrole");
                $this->roletype  = $ss->getString("type");
            }

            return $this;
        }

        // ── EXISTING: Handle CONDITION (binary) — tidak diubah ─
        $countisCondMeet = MaxesWorkflow::IsConditionMeet($tonl, $wfname, $table, $tableid);
        $condwfObj = new MaxesWorkflow();
  
        $condtoNodeSet = (new Set())->useTransactionManager()
            ->MboSetRemote("mxswfaction")
            ->setRelationship("mxswfnode", "mxswfaction.wfname = mxswfnode.wfname and mxswfaction.tonodeline = mxswfnode.nodeline");

        if ($countisCondMeet == 0) {
            $condtoNodeSet = $condtoNodeSet->setWhere("mxswfaction.wfname='$wfname' and mxswfaction.isnegative='Y' and mxswfaction.nodeline='$tonl'");
        } else {
            $condtoNodeSet = $condtoNodeSet->setWhere("mxswfaction.wfname='$wfname' and mxswfaction.isnegative='N' and mxswfaction.nodeline='$tonl'");
        }

        $condtoNodeSet = $condtoNodeSet
            ->MboRemote("mxswfaction.tonodeline")
            ->MboRemote("mxswfnode.nodetype")
            ->MboRemote("mxswfaction.action")
            ->MboRemote("mxswfaction.instruction")
            ->getMboSet();

        $this->ismeet    = $countisCondMeet;
        $condToNode      = $condtoNodeSet->getMbo();
        $this->nodetype  = $condToNode->getString("nodetype");
        $this->tonodeline = $condToNode->getString("tonodeline");

        if ($this->nodetype == "CONDITION" || $this->nodetype == "MULTICONDITION") {
            $e = new MaxesWorkflow();
            $r = $e->ReplaceConditionOption($this->tonodeline, $wfname, $table, $tableid);
            $this->tonodeline  = $r->tonodeline;
            $this->nodetype    = $r->nodetype;
            $this->wfname      = $wfname;
            $this->table       = $table;
            $this->tableid     = $tableid;
        } else {
            $this->action      = $condToNode->getString("action");
            $this->isnegative  = "N";
            $this->instruction = $condToNode->getString("instruction");

            $ssSet = (new Set())->useTransactionManager()
                ->MboSetRemote("mxswfrole")
                ->setRelationship("mxswfnoderole", "mxswfrole.mxsrole = mxswfnoderole.mxsrole")
                ->setWhere("mxswfnoderole.wfname = '$wfname' AND mxswfnoderole.nodeline='$this->tonodeline'")
                ->MboRemote("mxswfnoderole.mxsrole")
                ->MboRemote("value")
                ->MboRemote("type")
                ->getMboSet();

            $ss = $ssSet->getMbo();
            $value = $ss->getString("value");
            $this->rolevalue = MaxesWorkflow::CheckUsernamePerson($value);
            $this->roleid    = $ss->getString("mxsrole");
            $this->roletype  = $ss->getString("type");
        }

        return $this;
    }

	public static function ConditionUpdates($wfname, $currentnodeline, $table, $tableid, $loginuser, $hasstop)
	{

		$tonodelineSet = (new Set())->useTransactionManager()->MboSetRemote("mxswfnode")->setWhere("mxswfnode.nodeline='$currentnodeline' and mxswfnode.wfname='$wfname'")
		->setRelationship("mxswfaction", "mxswfnode.nodeline = mxswfaction.nodeline and mxswfnode.wfname = mxswfaction.wfname")->MboRemote("mxswfaction.tonodeline")->MboRemote("description")->getMboSet();
		$tonodelinembo = $tonodelineSet->getMbo();
		$tonl = $tonodelinembo->getString("tonodeline");
		$isCondition = (new Set())->useTransactionManager()->MboSetRemote("mxswfnode")->setWhere("mxswfnode.nodeline='$tonl' and wfname='$wfname'")->MboRemote("nodetype")->getMboSet()->getMbo()->getString("nodetype");
		
		if($isCondition == "CONDITION" || $isCondition == "MULTICONDITION"){
			$enterdate = Xbo::setEnterDate();
			$a = new MaxesWorkflow();
			$b = $a->buildActionSet("CONDITION", $currentnodeline, $wfname, $table, $tableid);
			$newcurrentnodeline = $b->newcurrentnodeline;
			$roleaction = $b->roleaction;
			
			
			$instrSet = (new Set())->useTransactionManager()->MboSetRemote("mxswfaction")->setWhere("wfname='$wfname' and tonodeline='$newcurrentnodeline'")->MboRemote("instruction")->getMboSet();
			$instr = $instrSet->getMbo();
			$instruction = $instr->getString("instruction");
			$readdate = Xbo::setEnterDate();
			
			$ssSet =(new Set())->useTransactionManager()->MboSetRemote("mxswfrole")->setRelationship("mxswfnoderole", "mxswfrole.mxsrole = mxswfnoderole.mxsrole")
			->setWhere("mxswfnoderole.wfname = '$wfname' and mxswfnoderole.nodeline='$newcurrentnodeline'")
			->MboRemote("mxswfnoderole.mxsrole")->MboRemote("value")->MboRemote("type")->getMboSet();
			
			$ss= $ssSet->getMbo();
			$value = $ss->getString("value");
			$rolevalue = MaxesWorkflow::CheckUsernamePerson($value);
			$mxsrole = $ss->getString("mxsrole");
			
			(new Set())->useTransactionManager()->MboSetRemote("mxswftracking")->setWhere("wfname = '$wfname' and appname='$table' and appkey='$tableid' and currentnodeline='$currentnodeline' ")->remove();
			
			
			(new Set())->useTransactionManager()->MboSetRemote("mxswftracking")->setValue("mxsrole", $mxsrole)->setValue("rolevalue", $rolevalue)->setValue("wfname", $wfname)->setValue("appname", $table)
							->setValue("appkey", $tableid)->setValue("currentnodeline", $newcurrentnodeline)->setValue("hasstop", $hasstop)
							->setValue("enterdate", $enterdate)->setValue("changeby",$loginuser )->setValue("roleaction", $roleaction)->add();
							
			MaxesWorkflow::setDocStatus($table, $tableid, $newcurrentnodeline, $roleaction, $instruction, $enterdate, $loginuser, $rolevalue);	
		}
	}

	public static function setRoles()
	{
		$xxchildtable = $_POST['xxchildtable'];
		$xxtableid = $_POST['xxtableid'];
		$xxtableidvalue = $_POST['xxtableidvalue'];
		
		$mxwfnode = (new Set())->useTransactionManager()->MboSetRemote("mxswfnode")->setWhere("$xxtableid = '$xxtableidvalue'")->MboRemote("wfname")->MboRemote("nodeline")->MboRemote("title")->MboRemote("description")->MboRemote("nodetype")->getMboSet()->getMbo();
		$nodeline = $mxwfnode->getString("nodeline");
		$nodetype = $mxwfnode->getString("nodetype");
		$wfname = $mxwfnode->getString("wfname");
		$title = $mxwfnode->getString("title");
		$description = $mxwfnode->getString("description");
		
		
		$objnode = new stdClass();
		$objnode->nodeline = $nodeline;
		$objnode->nodetype = $nodetype;
		$objnode->wfname = $wfname;
		$objnode->nodetitle = $title;
		$objnode->description = $description;
		$objnode->xxtableidvalue = $xxtableidvalue;
		
		if($nodetype == "START" || $nodetype == "STOP" || $nodetype == "CONDITION"){
			echo json_encode(["msg"=>"norole", "roleobj"=>$objnode]);
		}else{
			echo json_encode(["msg"=>"setrole", "roleobj"=>$objnode]);
		}
	}

	public static function ShowWFNotif()
	{
		$userid = $_POST['xxxnotifuserid'];
		$fsname = ContextPath::getFullHostName();

		$mxswfnotifSet = (new Set())->useTransactionManager()->MboSetRemote("mxswfnotif")->order("enterdate desc limit 0,30")->setWhere("mxswfnotif.rolevalue='$userid'")
		->MboRemote("mxswfnotif.rolevalue")->MboRemote("mxswfnotif.appname")->MboRemote("mxswfnotif.appkey")->MboRemote("mxswfnotif.tablelogid")->getMboSet();
		
		$count = $mxswfnotifSet->counts();
		$arr = array();
		for($i=0; $i<$count; $i++){
			$obj = new StdClass();
			$mxswfnotif = $mxswfnotifSet->getMbo();
			$rolevalue = $mxswfnotif->getString("rolevalue");
			$appname = $mxswfnotif->getString("appname");
			$appkey = $mxswfnotif->getString("appkey");
			$tablelogid = $mxswfnotif->getString("tablelogid");
			
			$tablenumname = $appname."num";
			if($appname == "WORKORDER") $tablenumname = "wonum";
			$ownerSet = (new Set())->useTransactionManager()->MboSetRemote($appname)->setWhere($appname."id=$appkey")->MboRemote($tablenumname)->MboRemote("description")->getMboSet();
			$owner = $ownerSet->getMbo();
			
			$tablenum = $owner->getString($tablenumname);
			$tabledesc = $owner->getString("description");
			
			$tablelogname = $appname."doctrackinglog";
			$tablelognameid = $tablelogname."id";
	
			
			$tablelog = (new Set())->useTransactionManager()->MboSetRemote($tablelogname)->setWhere("$tablelognameid = $tablelogid")
			->MboRemote("lastdocnotice")->MboRemote("lastdocposition")->MboRemote("comment")->MboRemote("enterby")
			->MboRemote("lastdocsentby")->MboRemote("enterdate")->getMboSet()->getMbo();
			
			$applinks = $fsname . "/ui/" . (new Set())->useTransactionManager()->MboSetRemote("mxsapp")->setWhere("appname='$appname'")->MboRemote("url")->getMboSet()->getMbo()->getString("url");
			
			$obj->lastdocnotice = strtoupper("<b>".$tablelog->getString("lastdocnotice")."</b>");
			$obj->lastdocposition = $tablelog->getString("lastdocposition");
			$obj->lastdocsentby = $tablelog->getString("lastdocsentby");
			$obj->lastdocsentby = $tablelog->getString("lastdocsentby");
			$obj->comment = $tablelog->getString("comment");
			$obj->enterby = $tablelog->getString("enterby");
			$obj->enterdate = $tablelog->getString("enterdate");
			$obj->tablenum = "<a href='javascript:;' data-applinksurl = '" . $applinks . "' data-applinksownernum = '$tablenum%$appname' >".$tablenum."</a>";
			$obj->tabledesc = $tabledesc;
			$obj->appname = strtoupper($appname);
			array_push($arr, $obj);
			
			(new Set())->useTransactionManager()->MboSetRemote("mxswfnotif")->setWhere("tablelogid = '$tablelogid' and appname='$appname' and appkey='$appkey' and rolevalue='$userid'")->setValue("isread", 'Y')->change();
		}
		Set::throws(["showcomments"=>$arr]);
	}

	public static function CheckWFNotif()
	{
		$userid = $_POST['xxnotifuserid'];
		
		$countnotifSet = (new Set())->useTransactionManager()->MboSetRemote("mxswfnotif")->setWhere("rolevalue='$userid' and isread='N'")->MboRemote("rolevalue")->getMboSet()->counts();
		Set::throws(["countnotif"=>$countnotifSet]);
	}

	public static function showcomments()
	{
		$recordid = explode("^", $_POST['recordid']);
		$ownerTable = $recordid[0];
		$ownerkey = $recordid[1];
		$ownertableid = $ownerTable."id";
		
		$tablelogSet =  (new Set())->useTransactionManager()->MboSetRemote($ownerTable."doctrackinglog")
		->setWhere("ownerkey = '$ownerkey' and ownertable = '$ownerTable'")->MboRemote("enterby")->MboRemote("docprocessnum")->MboRemote("enterdate")
		->MboRemote("readdate")->MboRemote("isread")->MboRemote("comment")->MboRemote("lastdocnotice")->MboRemote("lastdocposition")
		->MboRemote("lastdocsentby")->MboRemote("lastdocstatus")->getMboSet();
		//echo $tablelogSet->sql;
		$readdate = Xbo::setEnterDate();
		$countTableLog = $tablelogSet->counts();
		if($countTableLog==0){
			echo "No comments yet.";
		}else{
			$titleArr = array();
			$commentsArr = array();
			
			for($i=0; $i<$countTableLog; $i++){				
				$tablelog = $tablelogSet->getMbo();
				$enterby = $tablelog->getString("enterby");
				$enterdate = $tablelog->getString("enterdate");
				$comment = $tablelogSet->getString("comment");
				$isread = $tablelogSet->getString("isread");
				$docprocessnum = $tablelogSet->getString("docprocessnum");
				$lastreaddate = $tablelogSet->getString("readdate");
				$lastdocnotice = $tablelogSet->getString("lastdocnotice");
				$lastdocposition = $tablelogSet->getString("lastdocposition");
				$lastdocsentby = $tablelogSet->getString("lastdocsentby");
				$lastdocstatus = $tablelogSet->getString("lastdocstatus");
				$spanclass = null;
				if($isread == "Y"){
					$spanclass = " class = 'comment_read' ";
				}else{
					$spanclass = " class = 'comment_unread' ";
				}
				$area = "$lastdocnotice^$comment^$enterby^$enterdate";
				$title_arr = "$lastdocnotice^$lastdocposition";
				array_push($commentsArr, $area);
				array_push($titleArr, $title_arr);
			}
			
			$titleArr2 = array_unique($titleArr);
			$titleArr = array_values($titleArr2);
			for($i=0; $i<count($titleArr); $i++){
				$titleparts =explode("^", $titleArr[$i]);
				$title = $titleparts[0];
				$docposition = $titleparts[1];
				echo "<div class='divcomment_thread'><b>".strtoupper($title). " </b>";	
				for($j=0; $j<count($commentsArr); $j++){
					$parts = explode("^", $commentsArr[$j]);
					$noticeflag = $parts[0];
					$comments = $parts[1];
					$enterby = $parts[2];
					$enterdate = $parts[3];
					if($noticeflag == $title){
						echo "<div class='divcomment'><span class='divcomment_title'>".$enterby."</span> ".$comments." <div class='divcomment_footer'> Posted Date ".$enterdate."</div></div>";	
					}
				}
				echo "</div>";
			}
			$doc =  (new Set())->useTransactionManager()->MboSetRemote($ownerTable)->setWhere("$ownertableid='$ownerkey'")->MboRemote("docsentby")->MboRemote("docposition")->getMboSet()->getMbo();
			$docsentby = $doc->getString("docsentby");
			$docposition = $doc->getString("docposition");
			$userid = Xbo::getLoginUser();

			if($docposition == $userid){
				 (new Set())->useTransactionManager()->MboSetRemote($ownerTable."doctrackinglog")
				->setWhere("ownerkey = '$ownerkey' and ownertable = '$ownerTable' and isread='N' and enterby != '$docposition'")->setValue("isread", "Y")->setValue("readdate", $readdate)->change();
			}
			if($docposition != $userid){
				if($docsentby == $userid){
					 (new Set())->useTransactionManager()->MboSetRemote($ownerTable."doctrackinglog")
					->setWhere("ownerkey = '$ownerkey' and ownertable = '$ownerTable' and isread='N' and enterby = '$docposition'")->setValue("isread", "Y")->setValue("readdate", $readdate)->change();
				}
			}
		}
	}
		
	public static function CheckUsernamePerson($fieldvalue, $ifuser=null)
	{
		//check if fieldvalue = PERSON
		
		$employeesSet =  (new Set())->useTransactionManager()->MboSetRemote("employees")->setWhere("personid='".$fieldvalue."'")->MboRemote("personid")->getMboSet();
		
		$countemp = $employeesSet->counts();
	
		if($countemp > 0){
			$employees = $employeesSet->getMbo();
			$personid = $employees->getString("personid");
			
			$mxusersSet =  (new Set())->useTransactionManager()->MboSetRemote("mxsusers")->setWhere("personid = '$personid'")->MboRemote("username")->getMboSet();
			$countusr = $mxusersSet->counts();
			
			if($countusr >0){
				$mxusers = $mxusersSet->getMbo();
				$username = $mxusers->getString("username");
				$rolevalue = $username;
				return $rolevalue;
			}else{
				$rolevalue = $fieldvalue;
				return $rolevalue;
			}
		}else{
			$rolevalue = $fieldvalue;
			return $rolevalue;
		}
	}

	public static function commentinput()
	{
		
		$recordid = explode("^", $_POST['recordid']);
		$ownerTable = $recordid[0];
		$ownerkey = $recordid[1];
		$ownertableid = $ownerTable."id";
		$dtprocessnum = $_POST['dtprocessnum'];
		$commentinput = $_POST['commentinput'];
		$ownerTableLog = $ownerTable."doctrackinglog";
		$ownerTableLogID = $ownerTableLog."id";
		
		$enterby = Xbo::getLoginUser();
		$enterdate = Xbo::setEnterDate();
		
		$tableSet =  (new Set())->useTransactionManager()->MboSetRemote($ownerTable)->setWhere("$ownertableid='$ownerkey'")
		->MboRemote("docstatus")->MboRemote("docnotice")->MboRemote("docposition")->MboRemote("docsentby")->getMboSet();
		$table = $tableSet->getMbo();
		$lastdocstatus = $table->getString("docstatus");
		$lastdocnotice = $table->getString("docnotice");
		$lastdocposition = $table->getString("docposition");
		$lastdocsentby = $table->getString("docsentby");
		
		
		 (new Set())->useTransactionManager()->MboSetRemote($ownerTableLog)->setValue("docprocessnum", $dtprocessnum)->setValue("ownertable", $ownerTable)
		->setValue("ownerkey", $ownerkey)->setValue("enterby", $enterby)->setValue("enterdate", $enterdate)
		->setValue("comment", $commentinput)->setValue("isread", "N")->setValue("lastdocstatus", "$lastdocstatus")->setValue("lastdocnotice", "$lastdocnotice")
		->setValue("lastdocposition", "$lastdocposition")->setValue("lastdocsentby", "$lastdocsentby")->add();
	
		$dateval =Super::whereDate("enterdate", $enterdate);
		$tablelogid =  (new Set())->useTransactionManager()->MboSetRemote($ownerTableLog)->setWhere("ownertable = '$ownerTable' and ownerkey='$ownerkey' $dateval")
		->MboRemote($ownerTableLogID)->getMboSet()->getMbo()->getString($ownerTableLogID);
		
		$mxswftrackingSet =  (new Set())->useTransactionManager()->MboSetRemote("mxswftracking")->setWhere("appname='$ownerTable' and appkey='$ownerkey'")->MboRemote("rolevalue")->getMboSet("remove_duplicate");
		
		$countroleval = $mxswftrackingSet->counts();
		for($i=0; $i<$countroleval; $i++){
			$mxswftracking = $mxswftrackingSet->getMbo();
			$rolevalue= $mxswftracking->getString("rolevalue");
			(new Set())->useTransactionManager()->MboSetRemote("mxswfnotif")->setValue("rolevalue", $rolevalue)->setValue("appname", $ownerTable)->setValue("appkey", $ownerkey)
			->setValue("isread", "N")->setValue("tablelogid", $tablelogid)->setValue("enterdate", $enterdate)->add();
		}
		
	}
		
	public static function WFInbox()
	{

		$userid = $_POST['xxwfinbox'];
		$where = "a.rolevalue='$userid' and a.appkey not in (select mxswftracking.appkey from mxswftracking where hasstop='Y' and appname = a.appname)";
		$rendertableSet =  (new Set())->useTransactionManager()->MboSetRemote("mxswftracking a")
		->setWhere($where)
		->MboRemote("a.appname")->MboRemote("a.appkey")->MboRemote("a.currentnodeline")
		->MboRemote("a.enterdate")->MboRemote("a.wfname")->MboRemote("a.mxswftrackingid")->getMboSet();	
		
		$count = $rendertableSet->counts();
		$fsname = ContextPath::getFullHostName();
		$iconPath = $fsname . "/icons/";
		$viewdetail = "<img src=\"" . $iconPath . "/mx_reports.png\"> ";
		$routewf = "<img src=\"" . $iconPath . "/btn_wf.png\"> ";
		
		$tdr = array();
		
		for($i=0; $i<$count; $i++){
			$rendertable = $rendertableSet->getMbo();
			$appname = strtoupper($rendertable->getString("appname"));
			$appkey = $rendertable->getString("appkey");
					
			$enterdate = $rendertable->getString("enterdate");
			$wfname = $rendertable->getString("wfname");
			$mxswftrackingid = $rendertable->getString("mxswftrackingid");
		
			$maxDateSet =  (new Set())->useTransactionManager()->MboSetRemote("mxswftracking")->setWhere("wfname='$wfname' and appname='$appname' and appkey = '$appkey'")->MboRemote("max(enterdate) as maxenterdate")->getMboSet();
			
			$maxdate = $maxDateSet->getMbo()->getString("maxenterdate");
			$enterdate_otime = strtotime($enterdate);
			$maxdate_otime = strtotime($maxdate);
		
			if($enterdate_otime == $maxdate_otime){
				
				//need to be update for tablenumname for next release
				//this function must pass parameter data-key to get pk of the table
				$appname2 = null;
				
				if($appname == "RECEIVING") $appname2 = "PO";
				else { $appname2 = $appname; }
				
				if($appname2 == "WORKORDER") {
					$tablenumname = "wonum";
				}else{
					$tablenumname = $appname2."num";
				}
				$tableidname = $appname2."id";
				
				if($appname == "RECEIVING"){
				
					$tablenameSet =  (new Set())->useTransactionManager()->MboSetRemote($appname2)->setWhere($tableidname."=".$appkey)->MboRemote("description")->MboRemote("docnotice")->MboRemote("docnotice2")
					->MboRemote($tablenumname)->MboRemote("docsentby")->MboRemote("docsentby2")->getMboSet();
					
					$tablename = $tablenameSet->getMbo();
					$tablenum=$tablename->getString($tablenumname);
					$tabledesc = $tablename->getString("description");
					$docnotice = $tablename->getString("docnotice");
					$docnotice2 = $tablename->getString("docnotice2");
					$docsentby = $tablename->getString("docsentby");
					$docsentby2 = $tablename->getString("docsentby2");
				}else{

					$tablenameSet =  (new Set())->useTransactionManager()->MboSetRemote($appname2)->setWhere($tableidname."=".$appkey)->MboRemote("description")->MboRemote("docnotice")
					->MboRemote($tablenumname)->MboRemote("docsentby")->getMboSet();
					
					//echo $tablenameSet->sql."<br>";
					$tablename = $tablenameSet->getMbo();
				
					$tablenum=$tablename->getString($tablenumname);
					$tabledesc = $tablename->getString("description");
					$docnotice = $tablename->getString("docnotice");
					$docsentby = $tablename->getString("docsentby");
				}
				
				$applinks = $fsname . "/ui/" .  (new Set())->useTransactionManager()->MboSetRemote("mxsapp")->setWhere("appname='$appname'")->MboRemote("url")->getMboSet()->getMbo()->getString("url");				
				$recordid = $appname2."^".$appkey;
				
				if($appname == "RECEIVING"){
					$td = "<tr>
						<td data-attribute='appname'>$appname</td> <td data-attribute='tablenum'>$tablenum </td> 
						<td data-attribute='description'>$tabledesc</td> <td data-attribute='docnotice'><span class='wfinstruction'>$docnotice2</span></td> 
						<td data-attribute='docsentby'>$docsentby2</td><td data-attribute='enterdate'>$enterdate</td> 
						<td><a href='javascript:;' data-applinksurl = '" . $applinks . "' data-applinksownernum = '$tablenum%$appname2' >$viewdetail</a></td>
						<td><a href='javascript:;' data-wfxboset='$recordid'>$routewf</a></td> <td data-attribute='tableid' class='hide'>$appkey</td></tr>";
				}else{
					$td = "<tr>
						<td data-attribute='appname'>$appname</td> <td data-attribute='tablenum'>$tablenum </td> 
						<td data-attribute='description'>$tabledesc</td> <td data-attribute='docnotice'><span class='wfinstruction'>$docnotice</span></td> 
						<td data-attribute='docsentby'>$docsentby</td><td data-attribute='enterdate'>$enterdate</td> 
						<td><a href='javascript:;' data-applinksurl = '" . $applinks . "' data-applinksownernum = '$tablenum%$appname2' >$viewdetail</a></td>
						<td><a href='javascript:;' data-wfxboset='$recordid'>$routewf</a></td> <td data-attribute='tableid' class='hide'>$appkey</td></tr>";
				}
				
				array_push($tdr, $td);
			}else{
				continue;
			}
		}
		
		//list mxswftrackingline for PERSONGROUP - ALLFOLLOW
		
		$rendertableSet =  (new Set())->useTransactionManager()->MboSetRemote("mxswftrackingline")->setRelationship("mxswftracking", "mxswftracking.wfname =mxswftrackingline.wfname and mxswftracking.appname =mxswftrackingline.appname and mxswftracking.appkey =mxswftrackingline.appkey and mxswftracking.currentnodeline = mxswftrackingline.currentnodeline")
		->setWhere("mxswftrackingline.userid like '%$userid%' and  mxswftrackingline.didaction='N'")->MboRemote("mxswftrackingline.appname")
		->MboRemote("mxswftrackingline.appkey")->MboRemote("mxswftrackingline.currentnodeline")
		->MboRemote("mxswftracking.enterdate")->MboRemote("mxswftrackingline.wfname")->MboRemote("mxswftrackingline.mxswftrackinglineid")->getMboSet();	
		//echo $rendertableSet->sql;
		
		$count = $rendertableSet->counts();
		for($i=0; $i<$count; $i++){
			$rendertable = $rendertableSet->getMbo();
			$appname = strtoupper($rendertable->getString("appname"));
			$appkey = $rendertable->getString("appkey");
			$enterdate = $rendertable->getString("enterdate");
			$wfname = $rendertable->getString("wfname");
			$mxswftrackinglineid = $rendertable->getString("mxswftrackinglineid");
			
			$appname2 = null;
			if($appname == "RECEIVING") $appname2 = "PO";
			else { $appname2 = $appname; }
			
			$tablenumname = $appname2."num";
			if($appname2 == "WORKORDER") $tablenumname = "wonum";
			$tableidname = $appname2."id";
			
			$tablenameSet =  (new Set())->useTransactionManager()->MboSetRemote($appname2)->setWhere($tableidname."=".$appkey)->MboRemote("description")->MboRemote("docnotice")->MboRemote("docnotice2")
			->MboRemote($tablenumname)->MboRemote("docsentby")->MboRemote("docsentby2")->getMboSet();
			$tablename = $tablenameSet->getMbo();
		
			$tablenum=$tablename->getString($tablenumname);
			$tabledesc = $tablename->getString("description");
			$docnotice = $tablename->getString("docnotice");
			$docnotice2 = $tablename->getString("docnotice2");
			$docsentby = $tablename->getString("docsentby");
			$docsentby2 = $tablename->getString("docsentby2");
				
			$applinks = $fsname . "/ui/" .  (new Set())->useTransactionManager()->MboSetRemote("mxsapp")->setWhere("appname='$appname'")->MboRemote("url")->getMboSet()->getMbo()->getString("url");
			$recordid = $appname."^".$appkey;
			
			if($appname == "RECEIVING") {
				$docnotice = $docnotice2;
				$docsentby = $docsentby2;
			}
			$td = "<tr><td data-attribute='appname'>$appname</td> <td data-attribute='tablenum'>$tablenum</td> <td data-attribute='description'>$tabledesc</td> <td data-attribute='docnotice'><div class='wfinstruction'>$docnotice</div></td> <td data-attribute='docsentby'>$docsentby</td><td data-attribute='enterdate'>$enterdate</td> 
			<td><a href='javascript:;' data-applinksurl = '" . $applinks . "' data-applinksownernum = '$tablenum%$appname' >$viewdetail</a></td> <td><a href='javascript:;' data-wfxboset='$recordid'>$routewf</a></td> <td data-attribute='tableid' class='hide'>$appkey</td></tr>";
			array_push($tdr, $td);
		}				
			
		if(count($tdr)>0){
			$tdjoin = implode("", $tdr);
		}else{
			$tdjoin = "";
		}
		$tbody = "<tbody>$tdjoin</tbody>";
		$thead = "<thead><th>Application</th><th>Num</th><th>Description</th><th>Notice</th><th>Sent By</th><th>Enter Date</th><th>Detail</th><th>Route</th><th class='hide'>Tracking ID</th></thead>";
		$drawtable = $thead.$tbody;
		Set::throws(["userid"=>$userid, "drawtable"=>$drawtable]);
	}

	public static function TotalLeadTime(){
		$rec = $_POST["xxtotalleadtime"];
		$wfxboset = explode("^", $rec);
		$table = $wfxboset[0];
		$tableid = $wfxboset[1];
		
		$mxsworkflowSet =  (new Set())->useTransactionManager()->MboSetRemote("mxsworkflow")->setWhere("appname='$table' and isactive='Y'")->MboRemote("wfname")->getMboSet();
		$mxsworkflow = $mxsworkflowSet->getMbo();
		$wfname = $mxsworkflow->getString("wfname");
		
		$sumMinutes =  (new Set())->useTransactionManager()->MboSetRemote("mxswftracking")->setWhere("appname ='$table' and appkey=$tableid and wfname= '$wfname'")->MboRemote("sum(minutes) as totalminutes")
		->getMboSet()->getMbo()->getString("totalminutes");
		
		
		Set::throws(["totalleadtime"=>$sumMinutes]);
	}

	public function setLeadTime($wfname, $table, $tableid)
	{
		$enterdate = Xbo::setEnterDate();
		$adSt = strtotime($enterdate);

		$lastAcceptedSet =  (new Set())->useTransactionManager()->MboSetRemote("mxswftracking")->setWhere("appname = '$table' and appkey = '$tableid' and wfname='$wfname'")
		->MboRemote("max(enterdate) as lastaccepteddate")->getMboSet();
		
		$lastAccepted = $lastAcceptedSet->getMbo();
		$lastaccepteddate = $lastAccepted->getString("lastaccepteddate");
		
		$dateval =Super::whereDate("enterdate", $lastaccepteddate);
		
		$lastrolevalueSet =  (new Set())->useTransactionManager()->MboSetRemote("mxswftracking")->setWhere("appname = '$table' and appkey = '$tableid' and wfname='$wfname' $dateval")->MboRemote('rolevalue')
		->getMboSet();			
		$lastrolevalue = $lastrolevalueSet->getMbo();
		$rolevalue = $lastrolevalue->getString("rolevalue");
		$this->rolevalue = $rolevalue;
		if (!empty($lastaccepteddate)) {
			$lacdSt = strtotime($lastaccepteddate);
			$diff = $adSt - $lacdSt;
			$days = floor($diff / (60 * 60 * 24));
			$hours = round(($diff - $days * 60 * 60 * 24) / (60 * 60));
			$interval = abs($diff);
			$minute = $minutes = round($interval / 60);
			$manydays = Super::convertToHoursMins($minute, '%02d:%02d');
		}
		else {
			$manydays = "0 : 0";
			$minute = "0";
		}
		$this->hourmin = $manydays;
		$this->minutes = $minute;
		//echo json_encode($this);
		return $this;
	}

	public static function ProcessWorkflow($xboset, $islinkwf=null)
	{
		
		$xbos = $_POST['wfxboset'];
		$wfxboset = explode("^", $xbos);
		$wfuserid = $_POST['wfuserid'];
		$loginuser = Xbo::getLoginUser();
		$table = $wfxboset[0];
		$tableid = $wfxboset[1];
		$mboset = $_POST["mboset"];
		if($mboset != null) $table = $mboset;
		$tableidname = $table."id";
		//echo $xbos;
		/*
		if($table = "receiving"){
			$table = "po";
			$tableidname = $table."id";
		}
		*/
	
		$mxsworkflowSet = (new Set())->useTransactionManager()->MboSetRemote("mxsworkflow")->setWhere("appname='$table' and isactive='Y'")->MboRemote("wfname")->MboRemote("fordoctrack")->MboRemote("doctracktable")->getMboSet();
		$mxsworkflow = $mxsworkflowSet->getMbo();
		$wfname = $mxsworkflow->getString("wfname");
	
		if(!empty($wfname)){
		
			//check if this is a continue wf
			if(isset($_POST['xxtonodeline']) && isset($_POST['xxwfname'])){
				
				$passSPOST = $_POST;
				$mxsrole = $_POST['roleid'];
				$roletype = $_POST['roletype'];
				$followuprule = $_POST['followuprule'];
				$rolevalue = $_POST['rolevalue'];
				$followuprulegroup = $_POST['followuprulegroup'];
				$instruction = $_POST['instruction'];
				$roleaction = $_POST['roleaction'];
				$set = new Set();
				$wfname = $_POST['xxwfname'];
				$currentnodeline = $_POST['xxtonodeline'];
				$nodetype=$_POST['nodetype'];
				$enterdate = Xbo::setEnterDate();
				
				
				if($nodetype == "STOP"){
					MaxesWorkflow::updateLeadTime($table, $tableid, $wfname);
					$hasstop="Y";
					$set = new Set();
					if($mxsrole == "null") $mxsrole = "STOP";
					if($rolevalue == "null") $rolevalue = "SYSTEM";
					(new Set())->useTransactionManager()->MboSetRemote("mxswftracking")->setValue("mxsrole", $mxsrole)->setValue("rolevalue", $rolevalue)->setValue("wfname", $wfname)->setValue("appname", $table)
						->setValue("appkey", $tableid)->setValue("currentnodeline", $currentnodeline)->setValue("hasstop", $hasstop)
						->setValue("enterdate", $enterdate)->setValue("changeby",$loginuser )->setValue("roleaction", $roleaction)->add();
					MaxesWorkflow::setDocStatus($table, $tableid, $currentnodeline, $roleaction, $instruction, $enterdate, $loginuser, $rolevalue);
					MaxesWorkflow::ConditionUpdates($wfname, $currentnodeline, $table, $tableid, $loginuser, $hasstop);		
					
				}else{
					if($roletype == "PERSON"){
						$hasstop="N";
						if($followuprulegroup == "ALLFOLLOW"){
							$set = new Set();
							$flag = MaxesWorkflow::CheckAllMembersToFollow($wfname, $table, $tableid);
							if($flag == 1){
								MaxesWorkflow::updateLeadTime($table, $tableid, $wfname);
								(new Set())->useTransactionManager()->MboSetRemote("mxswftracking")->setValue("mxsrole", $mxsrole)->setValue("rolevalue", $rolevalue)->setValue("wfname", $wfname)->setValue("appname", $table)
								->setValue("appkey", $tableid)->setValue("currentnodeline", $currentnodeline)->setValue("hasstop", $hasstop)
								->setValue("enterdate", $enterdate)->setValue("changeby",$loginuser)->setValue("roleaction", $roleaction)->add();
								
								MaxesWorkflow::setDocStatus($table, $tableid, $currentnodeline, $roleaction, $instruction, $enterdate, $loginuser, $rolevalue);
								MaxesWorkflow::ConditionUpdates($wfname, $currentnodeline, $table, $tableid, $loginuser, $hasstop);
							}else{
								if($flag == 0){
								
									$hasfollow = (new Set())->useTransactionManager()->MboSetRemote("mxswftrackingline")->setWhere("wfname='$wfname' and appname='$table' and appkey='$tableid' and userid='$wfuserid'")
									->MboRemote("didaction")->getMboSet()->getMbo()->getString("didaction");
									if($hasfollow=="Y"){
										$message =  json_encode(["msg"=>"userhasfollowedup", "nodeObj"=>null]);
										echo $message; 
										
										return null;
									}else{
										(new Set())->useTransactionManager()->MboSetRemote("mxswftrackingline")->setWhere("wfname='$wfname' and appname='$table' and appkey='$tableid' and userid='$wfuserid'")->setValue("didaction", "Y")->change();
										$flag = MaxesWorkflow::CheckAllMembersToFollow($wfname, $table, $tableid);
										if($flag == 1){
											MaxesWorkflow::updateLeadTime($table, $tableid, $wfname);	
											(new Set())->useTransactionManager()->MboSetRemote("mxswftracking")->setValue("mxsrole", $mxsrole)->setValue("rolevalue", $rolevalue)->setValue("wfname", $wfname)->setValue("appname", $table)
											->setValue("appkey", $tableid)->setValue("currentnodeline", $currentnodeline)->setValue("hasstop", $hasstop)
											->setValue("enterdate", $enterdate)->setValue("changeby",$loginuser )->setValue("roleaction", $roleaction)->add();
																					
											MaxesWorkflow::setDocStatus($table, $tableid, $currentnodeline, $roleaction, $instruction, $enterdate, $loginuser, $rolevalue);
											MaxesWorkflow::ConditionUpdates($wfname, $currentnodeline, $table, $tableid, $loginuser, $hasstop);						
										}
									}
								}
							}
						}else{
							MaxesWorkflow::updateLeadTime($table, $tableid, $wfname);
						
							(new Set())->useTransactionManager()->MboSetRemote("mxswftracking")->setValue("mxsrole", $mxsrole)->setValue("rolevalue", $rolevalue)->setValue("wfname", $wfname)->setValue("appname", $table)
							->setValue("appkey", $tableid)->setValue("currentnodeline", $currentnodeline)->setValue("hasstop", $hasstop)
							->setValue("enterdate", $enterdate)->setValue("changeby",$loginuser )->setValue("roleaction", $roleaction)->add();
							
							MaxesWorkflow::setDocStatus($table, $tableid, $currentnodeline, $roleaction, $instruction, $enterdate, $loginuser, $rolevalue);
							MaxesWorkflow::ConditionUpdates($wfname, $currentnodeline, $table, $tableid, $loginuser, $hasstop);
						}
					}else{
						$hasstop="N";
						if($roletype == "PERSONGROUP"){
							
							MaxesWorkflow::updateLeadTime($table, $tableid, $wfname, $rolevalue);	
							(new Set())->useTransactionManager()->MboSetRemote("mxswftracking")->setValue("mxsrole", $mxsrole)->setValue("rolevalue", $rolevalue)->setValue("wfname", $wfname)->setValue("appname", $table)
								->setValue("appkey", $tableid)->setValue("currentnodeline", $currentnodeline)->setValue("hasstop", $hasstop)
								->setValue("enterdate", $enterdate)->setValue("changeby",$loginuser )->setValue("followuprule", $followuprule)->setValue("roleaction", $roleaction)->add();
									
								MaxesWorkflow::setDocStatus($table, $tableid, $currentnodeline, $roleaction, $instruction, $enterdate, $loginuser, $rolevalue);	
								MaxesWorkflow::ConditionUpdates($wfname, $currentnodeline, $table, $tableid, $loginuser, $hasstop);									
							
								$teamSet = (new Set())->useTransactionManager()->MboSetRemote("empteam")->setRelationship("mxsusers", "empteam.personid = mxsusers.personid")
								->setRelationship("teams", "teams.teamnum = empteam.teamnum")->setWhere("teams.teamnum='$rolevalue'")
								->MboRemote("empteam.personid")->MboRemote("mxsusers.userid")->MboRemote("teams.teamnum")->getMboSet();
								
								$cnt = $teamSet->counts();
								
								for($i=0; $i<$cnt; $i++){
								
									$team = $teamSet->getMbo();
									$personid = $team->getString("personid");
									$userid = $team->getString("userid");
									$teamnum = $team->getString("teamnum");
									(new Set())->useTransactionManager()->MboSetRemote("mxswftrackingline")->setValue("mxsrole", $mxsrole)->setValue("userid", $userid)->setValue("teamnum", $teamnum)
									->setValue("wfname", $wfname)->setValue("appname", $table)->setValue("appkey", $tableid)->setValue("currentnodeline", $currentnodeline)
									->setValue("didaction", "N")->add();
								}
								
						}else{
							
							if($roletype == "FIELDNAME"){
								
								$asSet = (new Set())->useTransactionManager()->MboSetRemote($table)->setWhere("$tableidname = '$tableid'")->MboRemote($rolevalue)->getMboSet();
								$fieldvalue = $asSet->getMbo()->getString($rolevalue);
								if($fieldvalue == null){
									Set::throws(["msg"=>"noassignedperson", "fieldname"=>$rolevalue]);
									return null;
								}else{
									//$rolevalue = $fieldvalue;
									$rolevalue = MaxesWorkflow::CheckUsernamePerson($fieldvalue);
									
									MaxesWorkflow::updateLeadTime($table, $tableid, $wfname);	
									(new Set())->useTransactionManager()->MboSetRemote("mxswftracking")->setValue("mxsrole", $mxsrole)->setValue("rolevalue", $rolevalue)->setValue("wfname", $wfname)->setValue("appname", $table)
									->setValue("appkey", $tableid)->setValue("currentnodeline", $currentnodeline)->setValue("hasstop", $hasstop)
									->setValue("enterdate", $enterdate)->setValue("changeby",$loginuser )->setValue("roleaction", $roleaction)->add();
									
									MaxesWorkflow::setDocStatus($table, $tableid, $currentnodeline, $roleaction, $instruction, $enterdate, $loginuser, $rolevalue);
									MaxesWorkflow::ConditionUpdates($wfname, $currentnodeline, $table, $tableid, $loginuser, $hasstop);
								}
							}else{
								if($roletype == "FIELDGROUP"){
									MaxesWorkflow::updateLeadTime($table, $tableid, $wfname, $rolevalue);											
									$as = new Set();
									$cols = null;
									if (strpos($rolevalue, ',') !== false) {
										$cols = explode(",", $rolevalue);
										$setfg = new Set();
										$table2 = $table;
										$tableidname2 = $tableidname;
										
										if($table == "receiving") {
											$table2 = "po";
											$tableidname2 = "poid";
										}
										$fgtableSet = (new Set())->useTransactionManager()->MboSetRemote($table2)->setWhere("$tableidname2 = '$tableid'");
										$colnamer = array();
										for($i=0; $i<count($cols); $i++){
											$colname = trim($cols[$i], " ");
											array_push($colnamer, $colname);
											$fgtableSet = $fgtableSet->MboRemote($colname);
										}
										$fgtableSet = $fgtableSet->getMboSet();
										$fgtable = $fgtableSet->getMbo();
										
										$colvalr = array();
										for($i=0; $i<count($colnamer); $i++){
											$colval = $fgtable->getString($colnamer[$i]);
											if($colval!=null){
												array_push($colvalr, $colval);
											}
										}
										if(count($colvalr) ==0 ){
											Set::throws(["msg"=>"noassignedperson", "fieldname"=>$rolevalue]);
											return null;
										}else{
											$userr = array();
											for($i=0; $i<count($colvalr); $i++){
												$userid = $colvalr[$i];
												array_push($userr, $userid);
											}
											$userim = implode(", ", $userr);
											(new Set())->useTransactionManager()->MboSetRemote("mxswftracking")->setValue("mxsrole", $mxsrole)->setValue("rolevalue", $userim)->setValue("wfname", $wfname)->setValue("appname", $table)
												->setValue("appkey", $tableid)->setValue("currentnodeline", $currentnodeline)->setValue("hasstop", $hasstop)
												->setValue("enterdate", $enterdate)->setValue("changeby",$loginuser )->setValue("followuprule", $followuprule)->setValue("roleaction", $roleaction)->add();
											
											for($i=0; $i<count($colvalr); $i++){
												
												$userid = $colvalr[$i];
												(new Set())->useTransactionManager()->MboSetRemote("mxswftrackingline")->setValue("mxsrole", $mxsrole)->setValue("userid", $userid)->setValue("teamnum", $userim)
												->setValue("wfname", $wfname)->setValue("appname", $table)->setValue("appkey", $tableid)->setValue("currentnodeline", $currentnodeline)
												->setValue("didaction", "N")->add();
											}
											MaxesWorkflow::setDocStatus($table, $tableid, $currentnodeline, $roleaction, $instruction, $enterdate, $loginuser, $userim);
											MaxesWorkflow::ConditionUpdates($wfname, $currentnodeline, $table, $tableid, $loginuser, $hasstop);												
										}
									}else{
										$table2 = $table;
										$tableidname2 = $tableidname;
										if($table == "receiving") {
											$table2 = "po";
											$tableidname2 = "poid";
										}
										$fieldvalue = (new Set())->useTransactionManager()->MboSetRemote($table2)->setWhere("$tableidname2 = '$tableid'")->MboRemote($rolevalue)->getMboSet()->getMbo()->getString($rolevalue);
										if($fieldvalue == null){
											Set::throws(["msg"=>"noassignedperson", "fieldname"=>$rolevalue]);
											return null;
										}else{
											$fieldvalue = MaxesWorkflow::CheckUsernamePerson($fieldvalue);
											(new Set())->useTransactionManager()->MboSetRemote("mxswftracking")->setValue("mxsrole", $mxsrole)->setValue("rolevalue", $rolevalue)->setValue("wfname", $wfname)->setValue("appname", $table)
												->setValue("appkey", $tableid)->setValue("currentnodeline", $currentnodeline)->setValue("hasstop", $hasstop)
												->setValue("enterdate", $enterdate)->setValue("changeby",$loginuser )->setValue("followuprule", $followuprule)->setValue("roleaction", $roleaction)->add();
											(new Set())->useTransactionManager()->MboSetRemote("mxswftrackingline")->setValue("mxsrole", $mxsrole)->setValue("userid", $fieldvalue)->setValue("teamnum", $rolevalue)
											->setValue("wfname", $wfname)->setValue("appname", $table)->setValue("appkey", $tableid)->setValue("currentnodeline", $currentnodeline)
											->setValue("didaction", "N")->add();
											
											MaxesWorkflow::setDocStatus($table, $tableid, $currentnodeline, $roleaction, $instruction, $enterdate, $loginuser, $rolevalue);	
											MaxesWorkflow::ConditionUpdates($wfname, $currentnodeline, $table, $tableid, $loginuser, $hasstop);
										}
									}
								}
							}
							
						}
					}
				}
				
				
				MaxesWorkflowExtend::AfterAction($passSPOST);
			}
			
			
			$maxDateSet = (new Set())->useTransactionManager()->MboSetRemote("mxswftracking")->setWhere("wfname='$wfname' and appname='$table' and appkey = '$tableid'")
			->MboRemote("max(enterdate) as maxenterdate")->getMboSet();
			$maxdate = $maxDateSet->getMbo()->getString("maxenterdate");
			
			$mxswftrackingSet =(new Set())->useTransactionManager()->MboSetRemote("mxswftracking")->setWhere("appname='$table' and appkey='$tableid' and enterdate = '$maxdate'")->MboRemote("mxsrole")
			->MboRemote("currentnodeline")->MboRemote("currentnodeline")->MboRemote("hasstop")->getMboSet();
			$mxswftracking = $mxswftrackingSet->getMbo();
			$roleid = $mxswftracking->getString("mxsrole");
			$currentnodeline = $mxswftracking->getString("currentnodeline");
			$hasstop = $mxswftracking->getString("hasstop");
			
			
			if($hasstop=="Y"){
				if($islinkwf== "y"){}else{
					Set::throws(["msg"=>"wfnodestop"]);
				}
				
			}else{
			
				if(empty($currentnodeline)){
					
					$mxswfnodeSet = (new Set())->useTransactionManager()->MboSetRemote("mxswfnode")->setWhere("wfname='$wfname' and nodetype='START'")
						->MboRemote("nodeline")->getMboSet();
					if($mxswfnodeSet->counts() >0){
						
						$mxswfnode = $mxswfnodeSet->getMbo();
						$nodeline = $mxswfnode->getString("nodeline");
						$continue = new MaxesWorkflow();
						
						$nextnode = $continue->continueWorkFlow($nodeline, $wfname, $table, $tableid);
						
						$currdesc = (new Set())->useTransactionManager()->MboSetRemote("mxswfnode")->setWhere("wfname='$wfname' and nodeline='$nodeline'")->getMboSet()->getMbo()->getString("description");
						if($nextnode == "noassignment"){
							Set::throws(["msg"=>"noassignment"]);
						}else{
							$reparr = MaxesWorkflow::WFReports($wfname, $nodeline);
							$set = new Set();
							MaxesWorkflow::NextNode($nextnode, $reparr, $currdesc);
						}
					}else{
						if($islinkwf== "y"){}else{
							Set::throws(["msg"=>"wfcannotfindstartnode"]);
						}
				
						
					}	
				}else{
					
					$continue = new MaxesWorkflow();
					$nextnode = $continue->continueWorkFlow($currentnodeline, $wfname, $table, $tableid);
					if($nextnode == "noassignment"){
						if($islinkwf== "y"){}
						else{	
							Set::throws(["msg"=>"noassignment"]);
						}
					}else{
						$currdesc = (new Set())->useTransactionManager()->MboSetRemote("mxswfnode")->setWhere("wfname='$wfname' and nodeline='$currentnodeline'")->getMboSet()->getMbo()->getString("description");
						$reparr = MaxesWorkflow::WFReports($wfname, $currentnodeline);
						MaxesWorkflow::NextNode($nextnode, $reparr, $currdesc);
					}
				}
			}
		}else{
			if($islinkwf== "y"){}else{
				Set::throws(["msg"=>"wfdoesnotexist"]);
			}
		}
		
		
	}
	
	public static function WFReports($wfname, $nodeline)
	{
		$mxswfreportsSet =  (new Set())->useTransactionManager()->MboSetRemote("mxswfreports")->setWhere("wfname='$wfname' and nodeline='$nodeline'")->MboRemote("reportname")->getMboSet();
		$countrep = $mxswfreportsSet->counts();
		$reparr = array();
		for($i=0; $i<$countrep; $i++){
			$mxswfreports = $mxswfreportsSet->getMbo();
			$reportname = $mxswfreports->getString("reportname");
			array_push($reparr, $reportname);
		}
		return $reparr;
	}

	public static function NextNode($nextnode, $reportlist=null, $currdesc=null){
		
		return Set::throws(["msg"=>"wfnodeexist", "nodeObj"=>$nextnode, "reportlist"=>$reportlist, "currdesc"=>$currdesc]);
	}

	public static function updateLeadTime($table, $tableid, $wfname)
	{
		
		$a = new MaxesWorkflow();
		$delta = $a->setLeadTime($wfname, $table, $tableid);
		$leadtime = $delta->hourmin;
		$minutes = $delta->minutes;
		$rolevalue = $delta->rolevalue;
		
		$mxswfTrackingSet = (new Set())->useTransactionManager()->MboSetRemote("mxswftracking")->where("rolevalue=? and appname=? and wfname=? and appkey=?",$rolevalue, $table, $wfname, $tableid )
		->MboRemote("max(enterdate) as max_enterdate")->getMboSet();
		
		$mxswftracking = $mxswfTrackingSet->getMbo();
		
		$maxenterdate = $mxswftracking->getString("max_enterdate");
		
		(new Set())->useTransactionManager()->MboSetRemote("mxswftracking")
		->where("enterdate = ?", $maxenterdate)
		->setValue("leadtime", $leadtime)
		->setValue("minutes", $minutes)->change();
		
	}
		
	public static function setDocStatus($table, $tableid, $docprocessnum, $docstatus, $docnotice, $docarrival, $docsentby, $docposition){
	
		$table2 = null;
		if($table == "receiving") $table2 = "po";
		else { $table2 = $table; }
		$tableidname = $table2."id";
		
		if($table != "receiving"){
			if($docstatus == null){
				 (new Set())->useTransactionManager()->MboSetRemote($table2)->setWhere("$tableidname = '$tableid'")->setValue("docprocessnum", $docprocessnum)
				->setValue("docnotice", $docnotice)->setValue("docarrival", $docarrival)->setValue("docsentby", $docsentby)->setValue("docposition", $docposition)->change();
			}else{
				 (new Set())->useTransactionManager()->MboSetRemote($table2)->setWhere("$tableidname = '$tableid'")->setValue("docprocessnum", $docprocessnum)->setValue("docstatus", $docstatus)
				->setValue("docnotice", $docnotice)->setValue("docarrival", $docarrival)->setValue("docsentby", $docsentby)->setValue("docposition", $docposition)->change();
			}
			
		}else{
			
			if($docstatus == null){
				 (new Set())->useTransactionManager()->MboSetRemote($table2)->setWhere("$tableidname = '$tableid'")->setValue("docprocessnum2", $docprocessnum)
				->setValue("docnotice2", $docnotice)->setValue("docarrival2", $docarrival)->setValue("docsentby2", $docsentby)->setValue("docposition2", $docposition)->change();
			}else{
				 (new Set())->useTransactionManager()->MboSetRemote($table2)->setWhere("$tableidname = '$tableid'")->setValue("docprocessnum2", $docprocessnum)->setValue("docstatus2", $docstatus)
				->setValue("docnotice2", $docnotice)->setValue("docarrival2", $docarrival)->setValue("docsentby2", $docsentby)->setValue("docposition2", $docposition)->change();
			}
			
		}
	
	}

	public function getWFAttributes($wfname){
		$appname = null;
		$WFSet =  (new Set())->useTransactionManager()->MboSetRemote("mxsworkflow")->setWhere("wfname='$wfname'")->MboRemote("appname")->getMboSet();
		$WF = $WFSet->getMbo();
		$this->WFSQL = $WFSet->sql;
		$this->appname = $WF->getString("appname");
		return $this;
	}

	public function getWFProperties($mxswfactionSet, $currentnodeline, $followuprule, $table=null, $tableid=null){
		//echo $mxswfactionSet->sql;
		
		$countmxswfaction = $mxswfactionSet->counts();
		$optArr = array();
		for($i=0; $i<$countmxswfaction; $i++){
			
				$mxswfaction = $mxswfactionSet->getMbo();
				$obj = new stdClass();
				$obj->roleid = $mxswfaction->getString("mxsrole");
				$setr = new Set();
				$obj->currentnodeline = $currentnodeline;
				$obj->wfname = $mxswfaction->getString("wfname");
				$obj->fromnodeline = $mxswfaction->getString("nodeline");
				$obj->tonodeline = $mxswfaction->getString("tonodeline");
				$obj->instruction = $mxswfaction->getString("instruction");
				$obj->action = $mxswfaction->getString("action");
				$obj->isnegative = $mxswfaction->getString("isnegative");
				$obj->nodetype = $mxswfaction->getString("nodetype");
				$obj->roletype = $mxswfaction->getString("type");
				$obj->followuprule = $mxswfaction->getString("followuprule");
				$obj->rolevalue = $mxswfaction->getString("value");			
				$obj->followuprulegroup = $followuprule;
				$obj->mboset = $table;
				
				if($obj->nodetype == "CONDITION"){
					$e = new MaxesWorkflow();
					$r = $e->ReplaceConditionOption($obj->tonodeline, $obj->wfname, $table, $tableid);
					$obj->tonodeline = $r->tonodeline;
					$obj->action = $r->action;
					$obj->nodetype =$r->nodetype;
					$obj->instruction =$r->instruction;
					$obj->rolevalue =$r->rolevalue;
					$obj->roleid =$r->roleid;
					$obj->roletype =$r->roletype;
				}
				if($obj->roletype == "FIELDNAME"){
					$fv = new MaxesWorkflow();
					$fv2 = $fv->getFieldValue($table, $tableid, $obj->rolevalue);
					$obj->fieldvalue = $fv2->fieldvalue;
					//check if fieldvalue = PERSON
				
					$employeesSet =  (new Set())->useTransactionManager()->MboSetRemote("employees")->setWhere("personid='".$obj->fieldvalue."'")->MboRemote("personid")->getMboSet();
					$countemp = $employeesSet->counts();
					if($countemp > 0){
						$employees = $employeesSet->getMbo();
						$personid = $employees->getString("personid");
						
						$mxusersSet =  (new Set())->useTransactionManager()->MboSetRemote("mxsusers")->setWhere("personid = '$personid'")->MboRemote("username")->getMboSet();
						$countusr = $mxusersSet->counts();
						if($countusr >0){
							$mxusers = $mxusersSet->getMbo();
							$username = $mxusers->getString("username");
							$obj->fieldvalue = $username;
						}else{
							$obj->fieldvalue = $obj->fieldvalue;
						}
					}else{
						$obj->fieldvalue = $obj->fieldvalue;
					}
				}else{
					if($obj->roletype == "FIELDGROUP"){
						if (strpos($obj->rolevalue, ',') !== false) {
							$cols = explode(",", $obj->rolevalue);
							$fieldr = array();
							for($i=0; $i<count($cols); $i++){
								
								if($table == "receiving") {
									$table = "po";
								}
								$colname = $cols[$i];
								$colname = trim($cols[$i], " ");
								$fv = new MaxesWorkflow();
								$fv2 = $fv->getFieldValue($table, $tableid, $colname);
								$fieldvalues = $fv2->fieldvalue;
								array_push($fieldr, $fieldvalues);
								
							}
							$im = implode(", ", $fieldr);
							$obj->fieldvalue = $im;
						}else{
							$fv = new MaxesWorkflow();
							if($table == "receiving") {
								$table = "po";
							} 
							$fv2 = $fv->getFieldValue($table, $tableid, $obj->rolevalue);
							$obj->fieldvalue = $fv2->fieldvalue;
						}
						
					}else{
						$obj->fieldvalue = null;
					}
				}
				array_push($optArr, $obj);
			}
		return $optArr;
	}
		
	public static function IsConditionMeet($tonl, $wfname, $table, $tableid){
		
		$checkCondSet =  (new Set())->useTransactionManager()->MboSetRemote("mxswfnode")->setWhere("mxswfnode.nodeline='$tonl' and mxswfnode.wfname='$wfname'")->MboRemote("whereclause")->getMboSet();		
		$checkCond = $checkCondSet->getMbo();
		$setWhere  = $checkCond->getString("whereclause");
			
		
		if($table == "receiving"){
			$table = "po";
		}
		$isCondMeetSet =  (new Set())->useTransactionManager()->MboSetRemote($table)->setWhere($table."id = ".$tableid. " and ".$setWhere)->MboRemote($table."id")->getMboSet();
		$countisCondMeet = $isCondMeetSet->counts();
		return $countisCondMeet;
	}
		
	public function getFieldValue($table, $tableid, $rolevalue){
		

		$tableidname = $table."id";
		
		$fieldname = null;
		$fieldvalue = null;
	
		$fieldvalueSet =  (new Set())->useTransactionManager()->MboSetRemote($table)->setWhere("$tableidname = '$tableid'")->MboRemote($rolevalue)->getMboSet();
		$fieldvalue = $fieldvalueSet->getMbo()->getString($rolevalue);
		
		if($fieldvalue == null){
			$fieldname = $rolevalue;
			$fieldvalue = null;
		}else{
			$fieldname = $rolevalue;
			$fieldvalue = $fieldvalue;
		}
		$this->sql = $fieldvalueSet->sql;
		$this->fieldname = $fieldname;
		$this->fieldvalue = $fieldvalue;
		
		return $this;
	}
	public function continueWorkFlow($currentnodeline, $wfname, $table, $tableid){
		
		
		$mxswfObj = new MaxesWorkflow();
		$newcurrentnodeline = $currentnodeline;
		$mxswfactionSet = $mxswfObj->sqlWf($newcurrentnodeline, $wfname);

		
		$tonodelineSet = (new Set())->useTransactionManager()->MboSetRemote("mxswfnode")->setWhere("mxswfnode.nodeline='$newcurrentnodeline' and mxswfnode.wfname='$wfname' ")
		->setRelationship("mxswfaction", "mxswfnode.nodeline = mxswfaction.nodeline and mxswfnode.wfname = mxswfaction.wfname")
		->MboRemote("mxswfaction.tonodeline")->MboRemote("description")->getMboSet();
		$tonodelinembo = $tonodelineSet->getMbo();
		$tonl = $tonodelinembo->getString("tonodeline");
		$isCondition = (new Set())->useTransactionManager()->MboSetRemote("mxswfnode")->setWhere("mxswfnode.nodeline='$tonl' and wfname='$wfname'")->MboRemote("nodetype")->getMboSet()->getMbo()->getString("nodetype");
		
		$BA = new MaxesWorkflow();
		$BuildAction = $BA->buildActionSet($isCondition, $tonl, $wfname, $table, $tableid);
		$ActionSet = $BuildAction->mxswfactionSet;
		$newnodeline = $BuildAction->newcurrentnodeline;
		
		
		
		if($ActionSet == null|| $newnodeline == null){
			$mxswfactionSet = $mxswfactionSet;
			$newcurrentnodeline = $currentnodeline;
		}else{
			$mxswfactionSet = $ActionSet;
			$newcurrentnodeline = $newnodeline;
		}
		
		
	
		$loginuser = Xbo::getLoginUser();
		
		$mxswfnodeSet = (new Set())->useTransactionManager()->MboSetRemote("mxswfnoderole")->setRelationship("mxswfrole", "mxswfnoderole.mxsrole = mxswfrole.mxsrole ")
		->setWhere("mxswfnoderole.wfname='$wfname' and mxswfnoderole.nodeline='$newcurrentnodeline'")->MboRemote("mxswfrole.mxsrole")
		->MboRemote("mxswfrole.value")->MboRemote("mxswfrole.type")->MboRemote("mxswfrole.followuprule")->getMboSet();
		//checkhere
		
		
		$mxswfnode = $mxswfnodeSet->getMbo();
		$rolevalue = $mxswfnode->getString("value");
		$roletype = $mxswfnode->getString("type");
		$followuprule = $mxswfnode->getString("followuprule");
		$mxsrole = $mxswfnode->getString("mxsrole");
		
		
		$prevnode =  (new Set())->useTransactionManager()->MboSetRemote("mxswfaction")->setWhere("wfname ='$wfname' and nodeline='$newcurrentnodeline'")->MboRemote("nodeline")->getMboSet()->getMbo()->getString("nodeline");
	
		$insarray = array();
		$insSet =  (new Set())->useTransactionManager()->MboSetRemote("mxswfaction")->setWhere("wfname ='$wfname' and tonodeline='$prevnode'")->MboRemote("instruction")->getMboSet();
		$countInsSet = $insSet->counts();
		
		
		if($countInsSet > 0){
			for($i=0; $i<$countInsSet; $i++){
				$ins = $insSet->getMbo();
				$inst = $ins->getString("instruction");
				$instruction = "<br>$inst<br>";
				array_push($insarray, $instruction);
			}
			$instruction = implode(" ", $insarray);	
		}else{
			
			$description =  (new Set())->useTransactionManager()->MboSetRemote("mxswfnode")->setWhere("wfname ='$wfname' and nodeline='$newcurrentnodeline'")->MboRemote("description")->getMboSet()->getMbo()->getString("description");
			$instruction = "$description";
			$instruction = "<br>$instruction<br>";
		}

		if($roletype == "FIELDNAME"){
			$fv = new MaxesWorkflow();
			$fv2 = $fv->getFieldValue($table, $tableid, $rolevalue);
			$fieldname = $fv2->fieldname;
			$fieldvalue = $fv2->fieldvalue;
			// check username for the person 
			$fieldvalue = MaxesWorkflow::CheckUsernamePerson($fieldvalue);
			//check username 
			if($fieldvalue == $loginuser){
				$WF = new MaxesWorkflow();
				$WFProp = $WF->getWFProperties($mxswfactionSet, $newcurrentnodeline, $followuprule, $table, $tableid);
				return $WFProp;
			}else{
				
				
				$noassignment = new stdClass();
				$noassignment->msg = "noassignment";
				$noassignment->rolevalue = $rolevalue;
				$noassignment->instruction = $instruction;
				return $noassignment;
			}
		}else{
			
			if($roletype == "PERSON"){
				
				if($rolevalue == $loginuser){
					$WF = new MaxesWorkflow();
					$WFProp = $WF->getWFProperties($mxswfactionSet, $newcurrentnodeline, $followuprule, $table, $tableid);
					
					return $WFProp;
				}else{
					$noassignment = new stdClass();
					$noassignment->msg = "noassignment";
					$noassignment->rolevalue = $rolevalue;
					$noassignment->instruction = $instruction;
					return $noassignment;
				}
			}else{
				
				if($roletype == "PERSONGROUP" || $roletype == "FIELDGROUP"){
					
					if($roletype == "FIELDGROUP"){
						$mxswftrackinglineSet =  (new Set())->useTransactionManager()->MboSetRemote("mxswftrackingline")
						->setWhere("mxsrole='$mxsrole' and userid='$loginuser' and wfname='$wfname' and currentnodeline='$newcurrentnodeline' and appname='$table' and appkey='$tableid'")
						->MboRemote("mxswftrackingline.userid")->getMboSet();
					}else{
						$mxswftrackinglineSet =  (new Set())->useTransactionManager()->MboSetRemote("mxswftrackingline")
						->setWhere("mxsrole='$mxsrole' and userid='$loginuser' and teamnum='$rolevalue' and wfname='$wfname' and currentnodeline='$newcurrentnodeline' and appname='$table' and appkey='$tableid'")
						->MboRemote("mxswftrackingline.userid")->getMboSet();
					}
					
					$mxswftrackingline = $mxswftrackinglineSet->getMbo();
					$roleuserid = $mxswftrackingline->getString("userid");
								
					if($roleuserid == $loginuser){
						$WF = new MaxesWorkflow();
						$WFProp = $WF->getWFProperties($mxswfactionSet, $newcurrentnodeline, $followuprule, $table, $tableid);
						return $WFProp;
					}else{
						$noassignment = new stdClass();
						$noassignment->msg = "noassignment";
						$noassignment->rolevalue = $rolevalue;
						$noassignment->instruction = $instruction;
						return $noassignment;
					}
				}else{
					//not defined yet
				}
			}
		}
	}
	public function sqlWf($currentnodeline, $wfname, $isnegative=null){
		
		$mxswfactionSet =  (new Set())->useTransactionManager()->MboSetRemote("mxswfaction")->order("mxswfaction.instruction ")
		->setRelationship("mxswfnode", "mxswfnode.nodeline = mxswfaction.tonodeline  and mxswfnode.wfname = mxswfaction.wfname")
		->setRelationship("mxswfnoderole","mxswfnoderole.nodeline = mxswfaction.tonodeline and mxswfnoderole.wfname = mxswfaction.wfname ")
		->setWhere("mxswfaction.nodeline='$currentnodeline' and mxswfaction.wfname='$wfname' $isnegative")
		->setRelationship("mxswfrole", "mxswfrole.mxsrole = mxswfnoderole.mxsrole")
		->MboRemote("mxswfaction.wfname")->MboRemote("mxswfaction.tonodeline")->MboRemote("mxswfaction.nodeline")->MboRemote("mxswfaction.instruction")
		->MboRemote("mxswfaction.action")->MboRemote("mxswfaction.isnegative")->MboRemote("mxswfnode.whereclause")
		->MboRemote("mxswfnoderole.mxsrole")->MboRemote("mxswfnode.nodetype")->MboRemote("mxswfrole.value")
		->MboRemote("mxswfrole.type")->MboRemote("mxswfrole.followuprule")
		->getMboSet("remove_duplicate");
		//echo $mxswfactionSet->sql;
		return $mxswfactionSet;
	}		
	public static function CheckAllMembersToFollow($wfname, $table, $tableid){
		
		$flag = 1;
		$checkhasfollowSet =  (new Set())->useTransactionManager()->MboSetRemote("mxswftrackingline")->setWhere("wfname='$wfname' and appname='$table' and appkey='$tableid'")->MboRemote("didaction")->getMboSet();
		$countrec = $checkhasfollowSet->counts();
		for($i=0; $i<$countrec; $i++){
			$checkhasfollow = $checkhasfollowSet->getMbo();
			$hasdidaction = $checkhasfollow->getString("didaction");
			if($hasdidaction == "Y") {
				$flag = $flag*1;
			}else{
				$flag = $flag*0;
			}
		}
		return $flag;
	}
	
}
?>
