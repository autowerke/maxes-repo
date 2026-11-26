<?php
/**
 * Main class for Workflow Engine
 */
 
 $composer_maxesworkflowExtend = 'maxeswf_extend.php';
 require_once $composer_maxesworkflowExtend;
 
class MaxesWorkflow {
    private const WAITING_MEMBER = "WAITING_MEMBER_TO_FOLLOW";

	public static function TotalAssignments()
	{
	}

	public static function IsMultiConditionMeet($nodeline, $wfname, $table, $tableid)
    {
        // Ambil semua branch, urutkan by sortorder ASC, default branch paling akhir
        $set = new Set();
        $branchSet = $set->useTransactionManager()
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
                $evalSet = new Set();
                $isMatch = $evalSet->useTransactionManager()
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
                $cset = new Set();
                $condtoNodeLineSet = $cset->useTransactionManager()
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
                $cset = new Set();
                $condtoNodeSet = $cset->useTransactionManager()
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
            $setCheckType = new Set();
            $targetType = $setCheckType->useTransactionManager()
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
        // ── ELSE: TASK / INPUT / STOP ────────────────────────
        // Node tujuan bukan CONDITION/MULTICONDITION.
        else {
            if($isCondition == "INPUT"){
                $nodeline = $tonl;
                $table    = !empty($_POST['wfxboset']) ? explode('^', $_POST['wfxboset'])[0] : '';
                $tableid  = !empty($_POST['wfxboset']) ? explode('^', $_POST['wfxboset'])[1] : '';

                $fields     = MaxesWorkflow::_getInputFields($wfname, $nodeline);
                $incomplete = MaxesWorkflow::IsInputComplete($fields, $table, $tableid);
                
                if (!empty($incomplete)) {
                    Set::throws([
                        'msg'               => 'inputrequired',
                        'nodeline'          => $nodeline,
                        'wfname'            => $wfname,
                        'fields'            => $incomplete,
                        'mxsrole'           => (new Set())->useTransactionManager()->MboSetRemote("mxswfnoderole")->where("wfname=? AND nodeline=?", $wfname, $nodeline)->getMboValue("mxsrole")
                    ]);
                   exit;
                } 
            }
            // Cek apakah node tujuan adalah STOP
            $targetType = (new Set())->useTransactionManager()
                ->MboSetRemote("mxswfnode")
                ->setWhere("wfname='$wfname' AND nodeline='$tonl'")
                ->MboRemote("nodetype")
                ->getMboSet()->getMbo()->getString("nodetype");

            if (strtoupper($targetType) === 'STOP') {
                // STOP node — tidak ada outgoing action, tidak ada role
                // Set flag khusus agar continueWorkFlow() tahu workflow selesai
                $this->mxswfactionSet     = null;
                $this->newcurrentnodeline = $tonl;   // tetap set nodeline ke STOP
                $this->roleaction         = 'STOP';  // signal ke caller
                $this->wfname             = $wfname;
            } else {
                // TASK / INPUT — advance normal
                $condwfObj = new MaxesWorkflow();
                $this->mxswfactionSet     = $condwfObj->sqlWf($tonl, $wfname);
                $this->newcurrentnodeline = $tonl;
                $this->roleaction         = null;
                $this->wfname             = $wfname;
            }
        }

        return $this;
    }
		
	public function ReplaceConditionOption($tonl, $wfname, $table, $tableid)
    {
        $wfname  = $wfname;
        $table   = $table;
        $tableid = $tableid;

        // Cek nodetype dari tonl
        $setType = new Set();
        $nodeType = $setType->useTransactionManager()
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
            $setCheckType2 = new Set();
            $targetNodetype = $setCheckType2->useTransactionManager()
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
                $set3 = new Set();
                $ssSet = $set3->useTransactionManager()
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
        $cset = new Set();
        $condtoNodeSet = $cset->useTransactionManager()
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

            $set3 = new Set();
            $ssSet = $set3->useTransactionManager()
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
      
        if($hasstop == "Y") {
            Set::throws(['msg' => 'wfnodestop']);
            exit;
        }
        
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
        try {
            // 1. Tangkap Parameter DataTables
            $draw   = isset($_POST['draw']) ? intval($_POST['draw']) : 1;
            $start  = isset($_POST['start']) ? intval($_POST['start']) : 0;
            $length = isset($_POST['length']) ? intval($_POST['length']) : 10;
            $search = isset($_POST['search']['value']) ? strtolower($_POST['search']['value']) : '';
            $userid = isset($_POST["xxwfinbox"]) ? $_POST["xxwfinbox"] : Super::getParam("xxwfinbox");

            if (empty($userid)) {
                echo json_encode(["draw" => $draw, "recordsTotal" => 0, "recordsFiltered" => 0, "data" => []]);
                return;
            }

            // 2. Panggil Helper: Ambil Data Mentah
            $allInboxItems = self::_getRawTrackingItems($userid);
            $recordsTotal  = count($allInboxItems);

            // 3. Proses Searching (Filter Lokal)
            $filteredItems =[];
            if (!empty($search)) {
                foreach ($allInboxItems as $item) {
                    if (strpos(strtolower($item['appname']), $search) !== false || strpos(strtolower($item['appkey']), $search) !== false) {
                        $filteredItems[] = $item;
                    }
                }
            } else {
                $filteredItems = $allInboxItems;
            }
            $recordsFiltered = count($filteredItems);

            // 4. Proses Pagination 
            $paginatedItems = array_slice($filteredItems, $start, $length);

            // 5. Panggil Helper: Ambil Detail Dokumen & URL (10 Baris)
            $masterAppData = self::_getDocumentDetails($paginatedItems);
            $appUrls       = self::_getAppUrls();

            // 6. Format Data Akhir untuk DataTables JSON
            $fsname = ContextPath::getFullHostName();
            $dataResponse =[];

            foreach ($paginatedItems as $row) {
                $appName     = strtoupper($row['appname']);
                $appKey      = $row['appkey'];
                $actualTable = ($appName == "RECEIVING") ? "PO" : $appName;
                $tableNumName= ($actualTable == "WORKORDER") ? "wonum" : strtolower($actualTable) . "num";

                // Lookup data dari helper
                $appRecord = $masterAppData[$actualTable][$appKey] ?? null;
                if (!$appRecord) continue; // Skip jika dokumen aslinya udah dihapus

                $tableNum  = $appRecord[$tableNumName] ?? '';
                $docNotice = ($appName == "RECEIVING") ? ($appRecord['docnotice2'] ?? '') : ($appRecord['docnotice'] ?? '');
                $docSentBy = ($appName == "RECEIVING") ? ($appRecord['docsentby2'] ?? '') : ($appRecord['docsentby'] ?? '');
                
                $appLinks  = $fsname . "/ui/" . ($appUrls[strtolower($appName)] ?? '');
                $recordId  = $actualTable . "^" . $appKey;

                // Masukkan ke array response
                $dataResponse[] =[
                    "appname"     => $appName,
                    "tablenum"    => $tableNum,
                    "description" => $appRecord['description'] ?? '',
                    "docnotice"   => "<span class='wfinstruction'>{$docNotice}</span>",
                    "docsentby"   => $docSentBy,
                    "enterdate"   => $row['enterdate'],
                    "detail_btn"  => "<a href='javascript:;' data-applinksurl='{$appLinks}' data-applinksownernum='{$tableNum}%{$actualTable}'><img src='{$fsname}/icons/mx_reports.png'></a>",
                    "route_btn"   => "<a href='javascript:;' data-wfxboset='{$recordId}'><img src='{$fsname}/icons/btn_wf.png'></a>",
                    "tableid"     => $appKey
                ];
            }

            // 7. Lempar JSON
            echo json_encode(["draw" => $draw, "recordsTotal" => $recordsTotal, "recordsFiltered" => $recordsFiltered, "data" => $dataResponse]);
            exit;

        } catch (Exception $e) {
            error_log("WFInbox Error: " . $e->getMessage());
            echo json_encode(["draw" => 1, "recordsTotal" => 0, "recordsFiltered" => 0, "data" => [], "error" => "Failed to load inbox."]);
            exit;
        }
    }

    private static function _getAppUrls()
    {
        $appUrlsSet = (new Set())->useTransactionManager()->MboSetRemote("mxsapp")
            ->MboRemote("appname, url")
            ->getMboSet()
            ->fetchData();

        $appUrls = [];
        foreach ($appUrlsSet as $row) {
            $appUrls[strtolower($row['appname'])] = $row['url'];
        }
        return $appUrls;
    }

    private static function _getRawTrackingItems($userid)
    {
        $allInboxItems =[];

        // A. Tracking Individu
        $trackingSet = (new Set())->useTransactionManager()->MboSetRemote("mxswftracking a")
            ->where("a.rolevalue = ? AND a.appkey NOT IN (SELECT mxswftracking.appkey FROM mxswftracking WHERE hasstop='Y' AND appname = a.appname)", $userid)
            ->MboRemote("a.appname, a.appkey, a.currentnodeline, a.mxswftrackingid, a.wfname, a.enterdate")
            ->getMboSet()
            ->fetchData();

        // Saring tanggal terbaru saja (Logic PHP)
        $latestTracking =[];
        foreach ($trackingSet as $row) {
            $key = $row['appname'] . '_' . $row['appkey'] . '_' . $row['wfname'];
            $time = strtotime($row['enterdate']);
            if (!isset($latestTracking[$key]) || $time > $latestTracking[$key]['_time']) {
                $row['_time'] = $time;
                $latestTracking[$key] = $row;
            }
        }
        foreach ($latestTracking as $row) {
            $allInboxItems[] = $row;
        }

        // B. Tracking Grup (Pesongroup)
        $trackingLineSet = (new Set())->useTransactionManager()->MboSetRemote("mxswftrackingline l")
            ->setRelationship("mxswftracking t", "t.wfname = l.wfname AND t.appname = l.appname AND t.appkey = l.appkey AND t.currentnodeline = l.currentnodeline")
            ->where("l.userid LIKE ? AND l.didaction = 'N'", "%" . $userid . "%")
            ->MboRemote("l.appname, l.appkey, l.currentnodeline, t.enterdate, l.wfname, l.mxswftrackinglineid")
            ->getMboSet()
            ->fetchData();

        foreach ($trackingLineSet as $row) {
            $allInboxItems[] = $row;
        }

        return $allInboxItems;
    }

    private static function _getDocumentDetails($paginatedItems)
    {
        if (empty($paginatedItems)) return [];

        $keysByApp =[];
        // Kelompokkan ID berdasarkan nama tabel
        foreach ($paginatedItems as $item) {
            $appName = strtoupper($item['appname']);
            $actualTable = ($appName == "RECEIVING") ? "PO" : $appName;
            $keysByApp[$actualTable][] = $item['appkey'];
        }

        $masterAppData =[];
        // Looping hanya berdasarkan jumlah jenis aplikasi (PO, WO, dll)
        foreach ($keysByApp as $tableName => $keys) {
            $uniqueKeys = array_unique($keys);
            $tableIdName = strtolower($tableName) . "id";
            $tableNumName = ($tableName == "WORKORDER") ? "wonum" : strtolower($tableName) . "num";

            $selectCols = "{$tableIdName}, description, docnotice, docsentby, {$tableNumName}";
            if ($tableName == "PO") { 
                $selectCols .= ", docnotice2, docsentby2"; 
            }

            // Query IN yang super cepat!
            $appData = (new Set())->useTransactionManager()->MboSetRemote($tableName)
                ->whereIn($tableIdName, $uniqueKeys)
                ->MboRemote($selectCols)
                ->getMboSet()
                ->fetchDataAssoc($tableIdName); 

            $masterAppData[$tableName] = $appData;
        }

        return $masterAppData;
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

	public static function ProcessWorkflow($xboset = null, $islinkwf = null)
    {
        // ── 1. Parse semua POST params ke satu array ──────────
        $p = self::_parseParams();
   
        // ── 2. Get active workflow name ───────────────────────
        $wfname = self::_getActiveWfName($p['table'], $p['mboset']);
        if (empty($wfname)) {
            if ($islinkwf == 'y') return;
            Set::throws(['msg' => 'wfdoesnotexist']);
            return;
        }
        $p['wfname'] = $wfname;

        // ── 3. Handle continue workflow (user sudah pilih action) ──
        if (isset($_POST['xxtonodeline']) && isset($_POST['xxwfname'])) {
            $passPOST = $_POST;
            self::_handleContinue($p);
        }
      
        // ── 4. Get current tracking state ─────────────────────
        $tracking = self::_getCurrentTracking($wfname, $p['table'], $p['tableid']);
        
        // ── 5. Already stopped? ───────────────────────────────
        if ($tracking['hasstop'] == 'Y') {
            if ($islinkwf == 'y') return;
            Set::throws(['msg' => 'wfnodestop']);
            return;
        }
        
        // ── 6. Resolve & render next node ─────────────────────
        self::_resolveAndRenderNextNode(
            $tracking['currentnodeline'],
            $wfname,
            $p['table'],
            $p['tableid'],
            $islinkwf
        );
        
    }

	private static function _parseParams()
    {
        $xbos      = $_POST['wfxboset'];
        $parts     = explode('^', $xbos);
        $table     = $parts[0];
        $tableid   = $parts[1];
        $mboset    = $_POST['mboset'] ?? null;

        // mboset override (untuk receiving → po)
        if (!empty($mboset)) $table = $mboset;

        return [
            'table'              => $table,
            'tableid'            => $tableid,
            'tableidname'        => $table . 'id',
            'mboset'             => $mboset,
            'wfuserid'           => $_POST['wfuserid'] ?? null,
            'loginuser'          => Xbo::getLoginUser(),
            'enterdate'          => Xbo::setEnterDate(),
            'mxsrole'            => $_POST['roleid']             ?? null,
            'roletype'           => $_POST['roletype']           ?? null,
            'followuprule'       => $_POST['followuprule']       ?? null,
            'rolevalue'          => $_POST['rolevalue']          ?? null,
            'followuprulegroup'  => $_POST['followuprulegroup']  ?? null,
            'instruction'        => $_POST['instruction']        ?? null,
            'roleaction'         => $_POST['roleaction']         ?? null,
            'wfname'             => $_POST['xxwfname']           ?? null,
            'currentnodeline'    => $_POST['xxtonodeline']       ?? null,
            'nodetype'           => $_POST['nodetype']           ?? null,
        ];
    }

    //Guard rails to prevent continue workflow if node has followuprule = ALLFOLLOW
    private static function _validateAllFollow($p){
        // Check if follow up rule = "ALLFOLLOW"
        extract($p);
        if($followuprule == "ALLFOLLOW"){
            if($roletype == "PERSONGROUP" || $roletype == "FIELDGROUP"){
               $flag = self::CheckAllMembersToFollow($wfname, $table, $tableid);
               if ($flag == 0){
                    return self::WAITING_MEMBER;
               }
            }
        }
        return null;
    }
	private static function _getActiveWfName($table, $mboset = null)
    {
        $appname = !empty($mboset) ? $mboset : $table;
        $set = (new Set())->useTransactionManager()
            ->MboSetRemote('mxsworkflow')
            ->setWhere("appname='$appname' and isactive='Y'")
            ->MboRemote('mxsworkflow.wfname')
            ->getMboSet();
        return $set->getMbo()->getString('wfname');
    }

	private static function _getCurrentTracking($wfname, $table, $tableid)
    {
        $maxDateSet = (new Set())->useTransactionManager()

            ->MboSetRemote('mxswftracking')
            ->setWhere("wfname='$wfname' and appname='$table' and appkey='$tableid'")
            ->MboRemote('max(mxswftrackingid) as mxswftrackingid')
            ->getMboSet();
        $mxswftrackingid = $maxDateSet->getMbo()->getString('mxswftrackingid');

        $trackingSet = (new Set())->useTransactionManager()
            ->MboSetRemote('mxswftracking')
            ->setWhere("appname='$table' and appkey='$tableid' and mxswftrackingid='$mxswftrackingid'")
            ->MboRemote('mxsrole')
            ->MboRemote('currentnodeline')
            ->MboRemote('hasstop')
            ->getMboSet();
        $row = $trackingSet->getMbo();

        return [
            'roleid'          => $row->getString('mxsrole'),
            'currentnodeline' => $row->getString('currentnodeline'),
            'hasstop'         => $row->getString('hasstop'),
        ];
    }

	private static function _resolveAndRenderNextNode($currentnodeline, $wfname, $table, $tableid, $islinkwf)
    {
        $continue = new MaxesWorkflow();
       
        // Fresh start — belum ada tracking
        if (empty($currentnodeline)) {
           
            $startSet = (new Set())->useTransactionManager()
                ->MboSetRemote('mxswfnode')
                ->setWhere("wfname='$wfname' and nodetype='START'")
                ->MboRemote('nodeline')
                ->getMboSet();

            if ($startSet->counts() == 0) {
                if ($islinkwf == 'y') return;
                Set::throws(['msg' => 'wfcannotfindstartnode']);
                exit;
            }

            $nodeline   = $startSet->getMbo()->getString('nodeline');
           
            $nextnode   = $continue->continueWorkFlow($nodeline, $wfname, $table, $tableid);
       
            $currdesc   = (new Set())->useTransactionManager()
                ->MboSetRemote('mxswfnode')
                ->where("wfname=? AND nodeline=?" , $wfname, $nodeline)
                ->getMboValue("description");

            if ($nextnode->msg == 'noassignment') {
                echo json_encode($nextnode);
                exit;
            }

            $reparr = MaxesWorkflow::WFReports($wfname, $nodeline);
            MaxesWorkflow::NextNode($nextnode, $reparr, $currdesc);
            return;
        }
        
        // Lanjut dari node yang sudah ada
        $nextnode = $continue->continueWorkFlow($currentnodeline, $wfname, $table, $tableid);
       
        
        if($nextnode == null){
            return;
        }
        if ($nextnode->msg == 'noassignment') {
            if ($islinkwf == 'y') return;
            echo json_encode($nextnode);
            exit;
        }

        $currdesc = (new Set())->useTransactionManager()
            ->MboSetRemote('mxswfnode')
            ->setWhere("wfname='$wfname' and nodeline='$currentnodeline'")
            ->getMboSet()->getMbo()->getString('description');
        $reparr = MaxesWorkflow::WFReports($wfname, $currentnodeline);
        MaxesWorkflow::NextNode($nextnode, $reparr, $currdesc);
    }

	// =========================================================
    // PRIVATE HANDLERS — continue workflow router
    // =========================================================

    
    private static function _handleContinue($p)
    {
        extract($p);

        // ── STOP ──────────────────────────────────────────────
        if ($nodetype == 'STOP'){
            $result = self::_handleStop($p);
           
            if($result == self::WAITING_MEMBER){
                self::_handlePerson($p);
                exit;
            }
        }

        // ── INPUT node submit — user sedang submit field values ──
        // HANYA masuk sini kalau frontend explicitly POST nodetype=INPUT
        // DAN ada inputfields di POST (user sudah isi form input).
        // Ini berbeda dari nodetype=INPUT di nodeObj (node TUJUAN adalah INPUT)
        // yang harus di-handle via alur normal PERSON/GROUP lalu
        // NextNode() intercept akan return inputrequired.
        if ($nodetype == 'INPUT' && !empty($_POST['inputfields'])) {
            
            $result = self::_handleInputSave($p);
            if($result == self::WAITING_MEMBER){
                self::_handlePerson($p);
                exit;
            }
            return;
        }

        // ── Route by roletype ─────────────────────────────────
        // Termasuk kasus nodetype=INPUT tanpa inputfields:
        // user act dari node sebelumnya, node TUJUAN adalah INPUT.
        // Handle sebagai TASK biasa — engine akan detect INPUT
        // node via NextNode() intercept dan return inputrequired.
         
        if ($roletype == 'PERSON')      { 
           
            $result =  self::_handlePerson($p);
            if($result == self::WAITING_MEMBER){
                self::_handlePerson($p);
                exit;
            }
            return;

        }
        if ($roletype == 'PERSONGROUP') { 
           
            $result = self::_handlePersonGroup($p); 
           
            if($result == self::WAITING_MEMBER){
                self::_handlePerson($p);
                exit;
            }
            return;
        }
        if ($roletype == 'FIELDNAME')   { 
            
            $result = self::_handleFieldName($p); 
            if($result == self::WAITING_MEMBER){
                self::_handlePerson($p);
                exit;
            }
            return;
        }
        if ($roletype == 'FIELDGROUP')  { 
           
            $result = self::_handleFieldGroup($p);  
            if($result == self::WAITING_MEMBER){
                self::_handlePerson($p);
                exit;
            }
            return;
        }
    }

	// =========================================================
    // PRIVATE HANDLER: STOP
    // =========================================================

	private static function _handleStop($p)
    {
        
        extract($p);

        // Check if follow up rule = "ALLFOLLOW"
        $isAllFollow = self::_validateAllFollow($p);
        if($isAllFollow == self::WAITING_MEMBER){
            return self::WAITING_MEMBER;
        }
       


        MaxesWorkflow::updateLeadTime($table, $tableid, $wfname);
        $hasstop = 'Y';
        if ($mxsrole   == 'null') $mxsrole   = 'STOP';
        if ($rolevalue == 'null') $rolevalue  = 'SYSTEM';
        
        
        (new Set())->useTransactionManager()
            ->MboSetRemote('mxswftracking')
            ->setValue('mxsrole',          $mxsrole)
            ->setValue('rolevalue',         $rolevalue)
            ->setValue('wfname',            $wfname)
            ->setValue('appname',           $table)
            ->setValue('appkey',            $tableid)
            ->setValue('currentnodeline',   $currentnodeline)
            ->setValue('hasstop',           $hasstop)
            ->setValue('enterdate',         $enterdate)
            ->setValue('changeby',          $loginuser)
            ->setValue('roleaction',        $roleaction)
            ->add();

        MaxesWorkflow::setDocStatus($table, $tableid, $currentnodeline, $roleaction, $instruction, $enterdate, $loginuser, $rolevalue);
        MaxesWorkflow::ConditionUpdates($wfname, $currentnodeline, $table, $tableid, $loginuser, $hasstop);
    }

	// =========================================================
    // PRIVATE HANDLER: PERSON
    // Logic SAMA PERSIS dengan versi lama
    // =========================================================

	private static function _handlePerson($p)
    {
        extract($p);
        $hasstop = 'N';
        if($nodetype == "STOP") $hasstop = "Y";

         
        if ($followuprulegroup == 'ALLFOLLOW') {
             // Mark user ini sudah follow
            (new Set())->useTransactionManager()
                ->MboSetRemote('mxswftrackingline')
                ->setWhere("wfname='$wfname' and appname='$table' and appkey='$tableid' and userid='$wfuserid'")
                ->setValue('didaction', 'Y')
                ->change();

            $flag = MaxesWorkflow::CheckAllMembersToFollow($wfname, $table, $tableid);
            
            // Semua sudah follow — commit tracking
            if ($flag == 1) {
                MaxesWorkflow::updateLeadTime($table, $tableid, $wfname);
                (new Set())->useTransactionManager()
                    ->MboSetRemote('mxswftracking')
                    ->setValue('mxsrole',        $mxsrole)
                    ->setValue('rolevalue',       $rolevalue)
                    ->setValue('wfname',          $wfname)
                    ->setValue('appname',         $table)
                    ->setValue('appkey',          $tableid)
                    ->setValue('currentnodeline', $currentnodeline)
                    ->setValue('hasstop',         $hasstop)
                    ->setValue('enterdate',       $enterdate)
                    ->setValue('changeby',        $loginuser)
                    ->setValue('roleaction',      $roleaction)
                    ->add();

                MaxesWorkflow::setDocStatus($table, $tableid, $currentnodeline, $roleaction, $instruction, $enterdate, $loginuser, $rolevalue);
                MaxesWorkflow::ConditionUpdates($wfname, $currentnodeline, $table, $tableid, $loginuser, $hasstop);
                return;
            }

            // Belum semua follow
            if ($flag == 0) {
                $hasfollow = (new Set())->useTransactionManager()
                    ->MboSetRemote('mxswftrackingline')
                    ->setWhere("wfname='$wfname' and appname='$table' and appkey='$tableid' and userid='$wfuserid'")
                    ->MboRemote('didaction')
                    ->getMboSet()->getMbo()->getString('didaction');
                
                if ($hasfollow == 'Y') {
                    Set::throws(['msg' => 'userhasfollowedup', 'nodeObj' => null]);
                    return;
                }

               
                // Cek lagi setelah mark
                $flag = MaxesWorkflow::CheckAllMembersToFollow($wfname, $table, $tableid);
                if ($flag == 1) {
                    MaxesWorkflow::updateLeadTime($table, $tableid, $wfname);
                    (new Set())->useTransactionManager()
                        ->MboSetRemote('mxswftracking')
                        ->setValue('mxsrole',        $mxsrole)
                        ->setValue('rolevalue',       $rolevalue)
                        ->setValue('wfname',          $wfname)
                        ->setValue('appname',         $table)
                        ->setValue('appkey',          $tableid)
                        ->setValue('currentnodeline', $currentnodeline)
                        ->setValue('hasstop',         $hasstop)
                        ->setValue('enterdate',       $enterdate)
                        ->setValue('changeby',        $loginuser)
                        ->setValue('roleaction',      $roleaction)
                        ->add();

                    MaxesWorkflow::setDocStatus($table, $tableid, $currentnodeline, $roleaction, $instruction, $enterdate, $loginuser, $rolevalue);
                    MaxesWorkflow::ConditionUpdates($wfname, $currentnodeline, $table, $tableid, $loginuser, $hasstop);
                }
            }
            return;
        }

        // ONEFOLLOW
        MaxesWorkflow::updateLeadTime($table, $tableid, $wfname);
        (new Set())->useTransactionManager()
            ->MboSetRemote('mxswftracking')
            ->setValue('mxsrole',        $mxsrole)
            ->setValue('rolevalue',       $rolevalue)
            ->setValue('wfname',          $wfname)
            ->setValue('appname',         $table)
            ->setValue('appkey',          $tableid)
            ->setValue('currentnodeline', $currentnodeline)
            ->setValue('hasstop',         $hasstop)
            ->setValue('enterdate',       $enterdate)
            ->setValue('changeby',        $loginuser)
            ->setValue('roleaction',      $roleaction)
            ->add();

        MaxesWorkflow::setDocStatus($table, $tableid, $currentnodeline, $roleaction, $instruction, $enterdate, $loginuser, $rolevalue);
        MaxesWorkflow::ConditionUpdates($wfname, $currentnodeline, $table, $tableid, $loginuser, $hasstop);
    }

	// =========================================================
    // PRIVATE HANDLER: PERSONGROUP
    // Logic SAMA PERSIS dengan versi lama
    // =========================================================

	private static function _handlePersonGroup($p)
    {
       
        extract($p);
        $hasstop = 'N';
        if($nodetype == "STOP") $hasstop = "Y";
        
        // Check if the follow-up rule is "ALLFOLLOW"; the previous node cannot proceed 
        // if there are members who have not yet followed up on the task.
        $isAllFollow = self::_validateAllFollow($p);
        if($isAllFollow == self::WAITING_MEMBER){
            return self::WAITING_MEMBER;
        }


        MaxesWorkflow::updateLeadTime($table, $tableid, $wfname, $rolevalue);

        //Tulis mxswftracking
        (new Set())->useTransactionManager()
            ->MboSetRemote('mxswftracking')
            ->setValue('mxsrole',        $mxsrole)
            ->setValue('rolevalue',       $rolevalue)
            ->setValue('wfname',          $wfname)
            ->setValue('appname',         $table)
            ->setValue('appkey',          $tableid)
            ->setValue('currentnodeline', $currentnodeline)
            ->setValue('hasstop',         $hasstop)
            ->setValue('enterdate',       $enterdate)
            ->setValue('changeby',        $loginuser)
            ->setValue('followuprule',    $followuprule)
            ->setValue('roleaction',      $roleaction)
            ->add();

        MaxesWorkflow::setDocStatus($table, $tableid, $currentnodeline, $roleaction, $instruction, $enterdate, $loginuser, $rolevalue);
        MaxesWorkflow::ConditionUpdates($wfname, $currentnodeline, $table, $tableid, $loginuser, $hasstop);

        // Insert tracking lines per team member
        $teamSet = (new Set())->useTransactionManager()
            ->MboSetRemote('empteam')
            ->setRelationship('mxsusers', 'empteam.personid = mxsusers.personid')
            ->setRelationship('teams',    'teams.teamnum = empteam.teamnum')
            ->setWhere("teams.teamnum='$rolevalue'")
            ->MboRemote('empteam.personid')
            ->MboRemote('mxsusers.userid')
            ->MboRemote('teams.teamnum')
            ->getMboSet();

        $cnt = $teamSet->counts();
        for ($i = 0; $i < $cnt; $i++) {
            $team    = $teamSet->getMbo();
            $userid  = $team->getString('userid');
            $teamnum = $team->getString('teamnum');
            (new Set())->useTransactionManager()
                ->MboSetRemote('mxswftrackingline')
                ->setValue('mxsrole',        $mxsrole)
                ->setValue('userid',          $userid)
                ->setValue('teamnum',         $teamnum)
                ->setValue('wfname',          $wfname)
                ->setValue('appname',         $table)
                ->setValue('appkey',          $tableid)
                ->setValue('currentnodeline', $currentnodeline)
                ->setValue('didaction',       'N')
                ->add();
        }
    }


	// =========================================================
    // PRIVATE HANDLER: FIELDNAME
    // Logic SAMA PERSIS dengan versi lama
    // =========================================================

    private static function _handleFieldName($p)
    {
        extract($p);
        $hasstop = 'N';
        if($nodetype == "STOP") $hasstop = "Y";
        
        // Check if the follow-up rule is "ALLFOLLOW"; the previous node cannot proceed 
        // if there are members who have not yet followed up on the task.
        $isAllFollow = self::_validateAllFollow($p);
        if($isAllFollow == self::WAITING_MEMBER){
            return self::WAITING_MEMBER;
        }

        $table2      = ($table == 'receiving') ? 'po' : $table;
        $tableidname2 = $table2 . 'id';

        $fieldvalue = (new Set())->useTransactionManager()
            ->MboSetRemote($table2)
            ->setWhere("$tableidname2='$tableid'")
            ->MboRemote($rolevalue)
            ->getMboSet()->getMbo()->getString($rolevalue);

        if (empty($fieldvalue)) {
            Set::throws(['msg' => 'noassignedperson', 'fieldname' => $rolevalue]);
            return;
        }

        $rolevalue = MaxesWorkflow::CheckUsernamePerson($fieldvalue);

        MaxesWorkflow::updateLeadTime($table, $tableid, $wfname);
        //tulis mxswftracking
        (new Set())->useTransactionManager()
            ->MboSetRemote('mxswftracking')
            ->setValue('mxsrole',        $mxsrole)
            ->setValue('rolevalue',       $rolevalue)
            ->setValue('wfname',          $wfname)
            ->setValue('appname',         $table)
            ->setValue('appkey',          $tableid)
            ->setValue('currentnodeline', $currentnodeline)
            ->setValue('hasstop',         $hasstop)
            ->setValue('enterdate',       $enterdate)
            ->setValue('changeby',        $loginuser)
            ->setValue('roleaction',      $roleaction)
            ->add();

        MaxesWorkflow::setDocStatus($table, $tableid, $currentnodeline, $roleaction, $instruction, $enterdate, $loginuser, $rolevalue);
        MaxesWorkflow::ConditionUpdates($wfname, $currentnodeline, $table, $tableid, $loginuser, $hasstop);
    }


	// =========================================================
    // PRIVATE HANDLER: FIELDGROUP
    // Logic SAMA PERSIS dengan versi lama
    // =========================================================

    private static function _handleFieldGroup($p)
    {
       
        extract($p);
        $hasstop = 'N';
        if($nodetype == "STOP") $hasstop = "Y";
        
        // Check if the follow-up rule is "ALLFOLLOW"; the previous node cannot proceed 
        // if there are members who have not yet followed up on the task.
        $isAllFollow = self::_validateAllFollow($p);
        if($isAllFollow == self::WAITING_MEMBER){
            return self::WAITING_MEMBER;
        }


        $table2   = ($table == 'receiving') ? 'po' : $table;
        $tableidname2 = $table2 . 'id';

        MaxesWorkflow::updateLeadTime($table, $tableid, $wfname, $rolevalue);

        // Multiple fields (comma-separated)
        if (strpos($rolevalue, ',') !== false) {
            $cols      = array_map('trim', explode(',', $rolevalue));
            $fgSet     = (new Set())->useTransactionManager()->MboSetRemote($table2)->setWhere("$tableidname2='$tableid'");
            foreach ($cols as $col) $fgSet = $fgSet->MboRemote($col);
            $fgRow     = $fgSet->getMboSet()->getMbo();

            $colvalr = [];
            foreach ($cols as $col) {
                $v = $fgRow->getString($col);
                if (!empty($v)) $colvalr[] = $v;
            }

            if (empty($colvalr)) {
                Set::throws(['msg' => 'noassignedperson', 'fieldname' => $rolevalue]);
                return;
            }

            $userim = implode(', ', $colvalr);

            // Tulis mxswftracking
            (new Set())->useTransactionManager()
                ->MboSetRemote('mxswftracking')
                ->setValue('mxsrole',        $mxsrole)
                ->setValue('rolevalue',       $userim)
                ->setValue('wfname',          $wfname)
                ->setValue('appname',         $table)
                ->setValue('appkey',          $tableid)
                ->setValue('currentnodeline', $currentnodeline)
                ->setValue('hasstop',         $hasstop)
                ->setValue('enterdate',       $enterdate)
                ->setValue('changeby',        $loginuser)
                ->setValue('followuprule',    $followuprule)
                ->setValue('roleaction',      $roleaction)
                ->add();

            foreach ($colvalr as $userid) {
                (new Set())->useTransactionManager()
                    ->MboSetRemote('mxswftrackingline')
                    ->setValue('mxsrole',        $mxsrole)
                    ->setValue('userid',          $userid)
                    ->setValue('teamnum',         $userim)
                    ->setValue('wfname',          $wfname)
                    ->setValue('appname',         $table)
                    ->setValue('appkey',          $tableid)
                    ->setValue('currentnodeline', $currentnodeline)
                    ->setValue('didaction',       'N')
                    ->add();
            }

            MaxesWorkflow::setDocStatus($table, $tableid, $currentnodeline, $roleaction, $instruction, $enterdate, $loginuser, $userim);
            MaxesWorkflow::ConditionUpdates($wfname, $currentnodeline, $table, $tableid, $loginuser, $hasstop);
            return;
        }

        // Single field
        $fieldvalue = (new Set())->useTransactionManager()
            ->MboSetRemote($table2)
            ->setWhere("$tableidname2='$tableid'")
            ->MboRemote($rolevalue)
            ->getMboSet()->getMbo()->getString($rolevalue);

        if (empty($fieldvalue)) {
            Set::throws(['msg' => 'noassignedperson', 'fieldname' => $rolevalue]);
            return;
        }

        $fieldvalue = MaxesWorkflow::CheckUsernamePerson($fieldvalue);

        // Tulis mxswftracking
        (new Set())->useTransactionManager()
            ->MboSetRemote('mxswftracking')
            ->setValue('mxsrole',        $mxsrole)
            ->setValue('rolevalue',       $rolevalue)
            ->setValue('wfname',          $wfname)
            ->setValue('appname',         $table)
            ->setValue('appkey',          $tableid)
            ->setValue('currentnodeline', $currentnodeline)
            ->setValue('hasstop',         $hasstop)
            ->setValue('enterdate',       $enterdate)
            ->setValue('changeby',        $loginuser)
            ->setValue('followuprule',    $followuprule)
            ->setValue('roleaction',      $roleaction)
            ->add();

        (new Set())->useTransactionManager()
            ->MboSetRemote('mxswftrackingline')
            ->setValue('mxsrole',        $mxsrole)
            ->setValue('userid',          $fieldvalue)
            ->setValue('teamnum',         $rolevalue)
            ->setValue('wfname',          $wfname)
            ->setValue('appname',         $table)
            ->setValue('appkey',          $tableid)
            ->setValue('currentnodeline', $currentnodeline)
            ->setValue('didaction',       'N')
            ->add();

        MaxesWorkflow::setDocStatus($table, $tableid, $currentnodeline, $roleaction, $instruction, $enterdate, $loginuser, $rolevalue);
        MaxesWorkflow::ConditionUpdates($wfname, $currentnodeline, $table, $tableid, $loginuser, $hasstop);
    }


	// =========================================================
    // INPUT NODE — Get field definitions
    // =========================================================

    public static function _getInputFields($wfname, $nodeline)
    {
        $set = (new Set())->useTransactionManager()
            ->MboSetRemote('mxswfinput')
            ->setWhere("wfname='$wfname' AND nodeline='$nodeline'")
            ->MboRemote('mxswfinputid')
            ->MboRemote('tablename')
            ->MboRemote('columnname')
            ->MboRemote('instruction')
            ->MboRemote('isrequired')
            ->MboRemote('sortorder')
            ->order('sortorder ASC')
            ->getMboSet();

        $fields = [];
        $cnt    = $set->counts();
        for ($i = 0; $i < $cnt; $i++) {
            $row      = $set->getMbo();
            $fields[] = [
                'mxswfinputid' => $row->getString('mxswfinputid'),
                'tablename'    => $row->getString('tablename'),
                'columnname'   => $row->getString('columnname'),
                'instruction'  => $row->getString('instruction'),
                'isrequired'   => $row->getString('isrequired') ?: 'Y',
                'sortorder'    => $row->getString('sortorder'),
            ];
        }
        return $fields;
    }


	// =========================================================
    // NEW: INPUT NODE — Check if all required fields are filled
    // Returns array of incomplete fields (empty = all complete)
    // =========================================================

    public static function IsInputComplete($fields, $table, $tableid)
    {
        $incomplete = [];

        foreach ($fields as $field) {
            if ($field['isrequired'] !== 'Y') continue;

            $tbl        = $field['tablename'];
            $col        = $field['columnname'];
            $tblid      = $tbl . 'id';

            // Ambil nilai current dari tabel
            $val = (new Set())->useTransactionManager()
                ->MboSetRemote($tbl)
                ->setWhere("$tblid='$tableid'")
                ->MboRemote($col)
                ->getMboSet()->getMbo()->getString($col);

            if (empty($val)) {
                $incomplete[] = array_merge($field, ['currentvalue' => '']);
            } else {
                // Field sudah terisi — sertakan current value untuk display
                $field['currentvalue'] = $val;
            }
        }

        return $incomplete; // empty = semua complete
    }

	// =========================================================
    // _handleInputSave
    //
    // FLOW:
    // 1. Decode + validate inputfields dari POST
    // 2. UPDATE setiap field ke tabel terkait
    // 3. Log ke mxswftracking
    // 4. Route ke _handlePerson / _handlePersonGroup /
    //    _handleFieldName / _handleFieldGroup
    //    — SAMA PERSIS dengan TASK, role diambil dari $p
    //    yang sudah berisi data dari mxswfnoderole
    // =========================================================

    private static function _handleInputSave($p)
    {
        extract($p);
      

        $hasstop = 'N';
        if($nodetype == "STOP") $hasstop = "Y";
        

        // Check if the follow-up rule is "ALLFOLLOW"; the previous node cannot proceed 
        // if there are members who have not yet followed up on the task.
        $isAllFollow = self::_validateAllFollow($p);
        if($isAllFollow == self::WAITING_MEMBER){
            return self::WAITING_MEMBER;
        }


        // ── 1. Decode fields dari POST ────────────────────────
        $rawFields = $_POST['inputfields'] ?? null;
        if (empty($rawFields)) {
            Set::throws(['msg' => 'inputvalidationfailed', 'message' => 'No input fields received.']);
            return;
        }
        $inputFields = json_decode($rawFields, true);
        if (!is_array($inputFields)) {
            Set::throws(['msg' => 'inputvalidationfailed', 'message' => 'Invalid input fields format.']);
            return;
        }

        // ── 2. Validasi tidak kosong ──────────────────────────
        foreach ($inputFields as $field) {
            if (($field['isrequired'] ?? 'Y') !== 'Y') continue;
            if (empty($field['value'])) {
                Set::throws([
                    'msg'       => 'inputvalidationfailed',
                    'fieldname' => $field['columnname'],
                    'message'   => $field['instruction'] ?? ($field['columnname'] . ' is required.'),
                ]);
                return;
            }
        }
       

        // ── 3. UPDATE setiap field ke tabel terkait ───────────
        foreach ($inputFields as $field) {
            $tbl    = $field['tablename'];
            $col    = $field['columnname'];
            $val    = $field['value'];
            $tblid  = $tbl . 'id';

            (new Set())->useTransactionManager()
                ->MboSetRemote($tbl)
                ->setWhere("$tblid='$tableid'")
                ->setValue($col, $val)
                ->change();
        }

        // ── 4. Log tracking INPUT node selesai ────────────────
     
        //Tulis MXSwftracking
        if(empty($mxsrole)) $mxsrole = Super::getParam("mxsrole");
        (new Set())->useTransactionManager()
            ->MboSetRemote('mxswftracking')
            ->setValue('mxsrole',        $mxsrole   ?? 'INPUT')
            ->setValue('rolevalue',       $rolevalue  ?? $loginuser)
            ->setValue('wfname',          $wfname)
            ->setValue('appname',         $table)
            ->setValue('appkey',          $tableid)
            ->setValue('currentnodeline', $currentnodeline)
            ->setValue('hasstop',         $hasstop)
            ->setValue('enterdate',       $enterdate)
            ->setValue('changeby',        $loginuser)
            ->setValue('roleaction',      'INPUT_COMPLETE')
            ->add();

        MaxesWorkflow::setDocStatus($table, $tableid, $currentnodeline, 'INPUT_COMPLETE', $instruction ?? '', $enterdate, $loginuser, $rolevalue ?? $loginuser);
        MaxesWorkflow::ConditionUpdates($wfname, $currentnodeline, $table, $tableid, $loginuser, $hasstop);
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
		
		// ── INPUT NODE intercept ──────────────────────────────
		if (is_array($nextnode)) {
			foreach ($nextnode as $node) {
				if (($node->nodetype ?? '') === 'INPUT') {

					$wfname   = $node->wfname ?? '';
					$nodeline = $node->currentnodeline ?? '';
					$table    = !empty($_POST['wfxboset']) ? explode('^', $_POST['wfxboset'])[0] : '';
					$tableid  = !empty($_POST['wfxboset']) ? explode('^', $_POST['wfxboset'])[1] : '';

					$fields     = MaxesWorkflow::_getInputFields($wfname, $nodeline);
					$incomplete = MaxesWorkflow::IsInputComplete($fields, $table, $tableid);

					if (!empty($incomplete)) {
						Set::throws([
							'msg'               => 'inputrequired',
							'nodeline'          => $nodeline,
							'wfname'            => $wfname,
							'fields'            => $incomplete,

							// Role info — ambil langsung dari nodeObj
							// yang sudah di-resolve oleh continueWorkFlow()
							'roleid'            => $node->roleid ?? '',
							'roletype'          => $node->roletype ?? 'PERSON',
							'rolevalue'         => $node->rolevalue ?? '',
							'followuprule'      => $node->followuprule ?? 'ONEFOLLOW',
							'followuprulegroup' => $node->followuprulegroup ?? 'ONEFOLLOW',
							'instruction'       => $node->instruction ?? '',
							'mboset'            => $node->mboset ?? '',
						]);
						return;
					}
					// Semua field sudah terisi → lanjut render nodeObj normal
				}
			}
		}
		// ── End INPUT NODE intercept ──────────────────────────
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
			if($docstatus == null || $docstatus == "null"){
				 (new Set())->useTransactionManager()->MboSetRemote($table2)->setWhere("$tableidname = '$tableid'")->setValue("docprocessnum", $docprocessnum)
				->setValue("docnotice", $docnotice)->setValue("docarrival", $docarrival)->setValue("docsentby", $docsentby)->setValue("docposition", $docposition)->change();
			}else{
				 (new Set())->useTransactionManager()->MboSetRemote($table2)->setWhere("$tableidname = '$tableid'")->setValue("docprocessnum", $docprocessnum)
				 ->setValue("docstatus", $docstatus)
				->setValue("docnotice", $docnotice)->setValue("docarrival", $docarrival)->setValue("docsentby", $docsentby)->setValue("docposition", $docposition)->change();
			}
			
		}else{
			
			if($docstatus == null || $docstatus == "null"){
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
                //=====================================================
                // buat jaring pengaman jika followuprule kosong, rolevalue kosong, mxsrole kosong
                //=====================================================
                if(empty($obj->roleid) || empty($obj->followuprule) || empty($obj->rolevalue) ){
                    $mxsrole = (new Set())->useTransactionManager()->MboSetRemote("mxswfnoderole")->where("wfname =? AND nodeline=?",$obj->wfname, $obj->fromnodeline)->getMboValue("mxsrole");
                    $mxswfroleSet = (new Set())->useTransactionManager()->MboSetRemote("mxswfrole")->where("appname=? AND mxsrole=?", $table, $mxsrole)->MboRemote("type,value,followuprule")->getMboSet()->fetch();
                    $type = $mxswfroleSet["type"];
                    $value = $mxswfroleSet["value"];
                    $followuprule = $mxswfroleSet["followuprule"];

                    //echo "$mxsrole, $type, $value, $followuprule"; //OUTPUT: WF-ADMIN-GROUP, PERSONGROUP, WF-ADMIN, ALLFOLLOW
                    $obj->roleid = $mxsrole;
                    $obj->roletype = $type;
                    $obj->rolevalue = $value;
                    $obj->followuprule = $followuprule;
                }
                
               

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

		if(empty($setWhere)){
			Set::throws(["msg"=>"error", "message"=> "Where Clause on Workflow [$wfname], on nodeline [$tonl] has not been set!"]);
			exit;
		}
			
		
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
		$mxswfactionSet = $mxswfObj->sqlWf($newcurrentnodeline, $wfname); //ActionSet
       
       
		$tonodelineSet = (new Set())->useTransactionManager()->MboSetRemote("mxswfnode")->setWhere("mxswfnode.nodeline='$newcurrentnodeline' and mxswfnode.wfname='$wfname' ")
		->setRelationship("mxswfaction", "mxswfnode.nodeline = mxswfaction.nodeline and mxswfnode.wfname = mxswfaction.wfname")
		->MboRemote("mxswfaction.tonodeline, description")->getMboSet();
        
		$tonodelinembo = $tonodelineSet->getMbo();
		$tonl = $tonodelinembo->getString("tonodeline");
		
       

		$isCondition = (new Set())->useTransactionManager()->MboSetRemote("mxswfnode")->setWhere("mxswfnode.nodeline='$tonl' and wfname='$wfname'")
		->MboRemote("nodetype")->getMboSet()->getMbo()->getString("nodetype");
		
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
	
        if($currentnodeline > $newcurrentnodeline) {
            //Backward Flows
            $newcurrentnodeline = $currentnodeline;
            $mxswfactionSet = $mxswfObj->sqlWf($newcurrentnodeline, $wfname); //ActionSet
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
                $noassignment->rolename = $fieldvalue;
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
                   
					// ── PATCH: Populate trackingline jika belum ada (first access) ──
                    // DELETE EXISTING RECORDS, guard rails jika user merubah value dari PERSONGROUP/FIELDGROUP.
                    // CARI RECORD user yang belum menjalankan task assignment (didaction='N')

                    //(new Set())->useTransactionManager()->MboSetRemote("mxswftrackingline")
                    //->where("wfname=? AND appname=? AND appkey=? AND currentnodeline=? AND mxsrole=? and didaction!='N'", $wfname, $table, $tableid, $newcurrentnodeline, $mxsrole)->delete();

                    $mxswftrackinglineSet = (new Set())->useTransactionManager()->MboSetRemote("mxswftrackingline")
                    ->where("wfname=? AND appname=? AND appkey=? AND currentnodeline=? AND mxsrole=? ", $wfname, $table, $tableid, $newcurrentnodeline, $mxsrole)->getMboSet();

                  
                    if ($mxswftrackinglineSet->isEmpty()) {
                        // First access — populate trackingline untuk semua member
                        if ($roletype == "PERSONGROUP") 
                        {
                           
                            $teamSet = (new Set())->useTransactionManager()
                            ->MboSetRemote("empteam")
                            ->setRelationship("mxsusers", "empteam.personid = mxsusers.personid")
                            ->setRelationship("teams",    "teams.teamnum = empteam.teamnum")
                            ->setWhere("teams.teamnum='$rolevalue'")
                            ->MboRemote("mxsusers.userid")
                            ->MboRemote("teams.teamnum")
                            ->getMboSet();
                            
                            $cnt = $teamSet->counts();
                            for ($i = 0; $i < $cnt; $i++) {
                                $member  = $teamSet->getMbo();
                                $userid  = $member->getString("userid");
                                $teamnum = $member->getString("teamnum");
                                (new Set())->useTransactionManager()
                                    ->MboSetRemote("mxswftrackingline")
                                    ->setValue("mxsrole",  $mxsrole)
                                    ->setValue("userid",   $userid)
                                    ->setValue("teamnum",  $teamnum)
                                    ->setValue("wfname",   $wfname)
                                    ->setValue("appname",  $table)
                                    ->setValue("appkey",   $tableid)
                                    ->setValue("currentnodeline", $newcurrentnodeline)
                                    ->setValue("didaction",  "N")
                                    ->add();
                            }
                        } elseif ($roletype == "FIELDGROUP") {
                           
                            // Query nilai dari field(s) di tabel record
                            $table2 = ($table == "receiving") ? "po" : $table;
                            $tableidname2 = $table2 . "id";
                            $cols = array_map("trim", explode(",", $rolevalue));
                            
                            $fgSet = (new Set())->useTransactionManager()->MboSetRemote($table2)->setWhere("$tableidname2='$tableid'");
                           
                            foreach ($cols as $col) {
                                $colKey = (strpos($col, ".") !== false)
                                    ? trim(explode(".", $col)[1]) : trim($col);
                                $fgSet = $fgSet->MboRemote($colKey);
                            }
                           
                            $fgRow   = $fgSet->getMboSet()->getMbo();
                            $userim  = implode(", ", array_map("trim", $cols));
                            
                            foreach ($cols as $col) {
                                $colKey = (strpos($col, ".") !== false) ? trim(explode(".", $col)[1]) : trim($col);
                                $columnValue = $fgRow->getString($colKey);
                                //Get Userid
                                $userid = (new Set())->useTransactionManager()->MboSetRemote("mxsusers")->where("userid=?", $columnValue)->getMboValue("userid");

                                if (empty($userid)) continue;
                                (new Set())->useTransactionManager()
                                    ->MboSetRemote("mxswftrackingline")
                                    ->setValue("mxsrole",        $mxsrole)
                                    ->setValue("userid",          $userid)
                                    ->setValue("teamnum",         $userim)
                                    ->setValue("wfname",          $wfname)
                                    ->setValue("appname",         $table)
                                    ->setValue("appkey",          $tableid)
                                    ->setValue("currentnodeline", $newcurrentnodeline)
                                    ->setValue("didaction",       "N")
                                    ->add();
                            }
                           
                        } 
                    }
                    
                   
                    // Lanjut: cek apakah loginuser eligible (ada di trackingline)
  	
					if($roletype == "FIELDGROUP"){
						$mxswftrackinglineSet =  (new Set())->useTransactionManager()->MboSetRemote("mxswftrackingline")
						->setWhere("mxsrole='$mxsrole' and userid='$loginuser' and wfname='$wfname' and currentnodeline='$newcurrentnodeline' and appname='$table' and appkey='$tableid'")
						->MboRemote("mxswftrackingline.userid")->getMboSet();

                        
                        //Check if mxswftrackinglineSet empty
                        if($mxswftrackinglineSet->isEmpty()){
                            //berarti kemungkinan NO USER FOUND. Check userid di mxsusers
                           $mxsusersSet = (new Set())->useTransactionManager()->MboSetRemote("mxsusers")->where("userid=?", $userid)->getMboSet();
                            if($mxsusersSet->isEmpty()){
                                $noassignment = new stdClass();
                                $noassignment->msg = "nouserfound"; //Workflow cannot find the user
                                $noassignment->rolevalue = $rolevalue;
                                $noassignment->instruction = $instruction;
                                return $noassignment;
                            }else{
                                //Jika usernya ada, berarti bukan untuknya( current user login)
                                $noassignment = new stdClass();
                                $noassignment->msg = "noassignment";
                                $noassignment->rolevalue = $rolevalue;
                                $noassignment->instruction = $instruction;
                                return $noassignment;
                            }
                        }
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
                        //sinibang
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
				$flag = $flag*1; // SUDAH FOLLOW UP
			}else{
				$flag = $flag*0; // BELUM FOLLOW UP
			}
		}
		return $flag;
	}
	
}
?>
