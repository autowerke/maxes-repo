<?php
require_once ("../../../eat.php");

Class ActionSet {
	
	
    // Ambil semua master role dari mxswfrole

 	public static function getRoles()
    {
        try {
            $set = new Set();
            $roles = $set->MboSetRemote("mxswfrole")
                ->MboRemote("mxsrole, type, value, followuprule, isemail, isbroadcast")
                ->order("mxsrole")
                ->getMboSet()
                ->fetchData();

            Super::sendJsonResponse("success", "Roles retrieved successfully.", $roles);

        } catch (Exception $e) {
            Super::sendJsonResponse("error", $e->getMessage());
        }
    }


    /**
     * saveWF() — Updated
     *
     * PERUBAHAN vs versi lama:
     * 1. Fix mapping action vs label — sebelumnya action diisi dari label
     * 2. Tambah DELETE + INSERT mxswfinput
     * 3. Soft warning INPUT node (dilakukan di frontend — tidak block save di backend)
     */

    public static function saveWF()
    {
        $raw     = Super::getParam("saveWF");
        $payload = json_decode($raw, true);

        if (json_last_error() !== JSON_ERROR_NONE || empty($payload)) {
            Super::sendJsonResponse("error", "Invalid payload. JSON parse failed.");
            return;
        }

        $wfname      = trim($payload['wfname']      ?? '');
        $nodes       = $payload['nodes']       ?? [];
        $actions     = $payload['actions']     ?? [];
        $noderoles   = $payload['noderoles']   ?? [];
        $inputfields = $payload['inputfields'] ?? [];

        // ── 1. VALIDASI ───────────────────────────────────────────
        if (empty($wfname)) {
            Super::sendJsonResponse("error", "wfname is required.");
            return;
        }

        $startNodes = array_filter($nodes, fn($n) => strtoupper($n['type'] ?? '') === 'START');
        if (count($startNodes) !== 1) {
            Super::sendJsonResponse("error", "Workflow must have exactly 1 START node.");
            return;
        }

        $stopNodes = array_filter($nodes, fn($n) => strtoupper($n['type'] ?? '') === 'STOP');
        if (count($stopNodes) < 1) {
            Super::sendJsonResponse("error", "Workflow must have at least 1 STOP node.");
            return;
        }

        $multiCondNodes = array_filter($nodes, fn($n) => strtoupper($n['type'] ?? '') === 'MULTICONDITION');
        foreach ($multiCondNodes as $mcNode) {
            $nodeline    = $mcNode['nodeline'];
            $nodeActions = array_filter($actions, fn($a) => ($a['source'] ?? '') === $nodeline);
            $hasDefault  = array_filter($nodeActions, fn($a) => ($a['isdefault'] ?? 'N') === 'Y');
            if (count($nodeActions) === 0) {
                Super::sendJsonResponse("error", "MULTICONDITION node '$nodeline' has no branches defined.");
                return;
            }
            if (count($hasDefault) !== 1) {
                Super::sendJsonResponse("error", "MULTICONDITION node '$nodeline' must have exactly 1 default branch.");
                return;
            }
        }

        // ── 2. TRANSACTION ────────────────────────────────────────
        $tm = TransactionManager::getInstance();
        $tm->beginTransaction();

        try {
            $now     = date("Y-m-d H:i:s");
            $session = Xbo::getLoginUser();

            // ── A. DELETE — child tables dulu ────────────────────
            (new Set())->useTransactionManager()->MboSetRemote("mxswfinput")->where("wfname = ?", $wfname)->delete();
            (new Set())->useTransactionManager()->MboSetRemote("mxswfnoderole")->where("wfname = ?", $wfname)->delete();
            (new Set())->useTransactionManager()->MboSetRemote("mxswfaction")->where("wfname = ?", $wfname)->delete();
            (new Set())->useTransactionManager()->MboSetRemote("mxswfnode")->where("wfname = ?", $wfname)->delete();

            // ── B. PREPARE DATA ───────────────────────────────────

            $nodesData = [];
            foreach ($nodes as $node) {
                $nodesData[] = [
                    "wfname"      => $wfname,
                    "nodeline"    => $node['nodeline'],
                    "title"       => $node['title']       ?? '',
                    "description" => $node['description'] ?? '',
                    "nodetype"    => $node['type']         ?? 'TASK',
                    "whereclause" => $node['whereclause']  ?? null,
                    "top"         => $node['top']          ?? 100,
                    "left"        => $node['left']         ?? 100,
                    "enterby"     => $session,
                    "enterdate"   => $now,
                ];
            }

            $actionsData = [];
            foreach ($actions as $action) {
                $actionsData[] = [
                    "wfname"      => $wfname,
                    "nodeline"    => $action['source']      ?? '',
                    "tonodeline"  => $action['target']      ?? '',
                    "label"       => $action['label']       ?? '',
                    "action"      => $action['action']      ?? '',
                    "title"       => $action['title']       ?? '',
                    "instruction" => $action['instruction'] ?? '',
                    "isnegative"  => $action['isnegative']  ?? 'N',
                    "whereclause" => $action['whereclause']  ?? null,
                    "sortorder"   => $action['sortorder']    ?? 0,
                    "isdefault"   => $action['isdefault']    ?? 'N',
                    "enterby"     => $session,
                    "enterdate"   => $now,
                ];
            }

            $rolesData = [];
            foreach ($noderoles as $role) {
                if (empty($role['mxsrole'])) continue;
                $rolesData[] = [
                    "wfname"    => $wfname,
                    "nodeline"  => $role['nodeline'],
                    "nodetitle" => $role['nodetitle'] ?? '',
                    "mxsrole"   => $role['mxsrole'],
                ];
            }

            $inputfieldsData = [];
            foreach ($inputfields as $nodeline => $fieldArr) {
                if (!is_array($fieldArr)) continue;
                foreach ($fieldArr as $field) {
                    if (empty($field['tablename']) || empty($field['columnname'])) continue;
                    $inputfieldsData[] = [
                        "wfname"      => $wfname,
                        "nodeline"    => $nodeline,
                        "tablename"   => $field['tablename']   ?? '',
                        "columnname"  => $field['columnname']  ?? '',
                        "instruction" => $field['instruction'] ?? '',
                        "isrequired"  => $field['isrequired']  ?? 'Y',
                        "sortorder"   => $field['sortorder']   ?? 10,
                        "ownership"   => null,
                    ];
                }
            }

            // ── C. INSERT ────────────────────────────────────────
            // mxswfnode pakai loop setValue→add karena kolom `left` dan `top`
            // adalah reserved keywords di MariaDB — addStream() build SQL tanpa
            // backtick sehingga syntax error. Tabel lain aman pakai addStream().
            foreach ($nodesData as $node) {
                (new Set())->useTransactionManager()->MboSetRemote("mxswfnode")
                    ->setValue("wfname",      $node["wfname"])
                    ->setValue("nodeline",    $node["nodeline"])
                    ->setValue("title",       $node["title"])
                    ->setValue("description", $node["description"])
                    ->setValue("nodetype",    $node["nodetype"])
                    ->setValue("whereclause", $node["whereclause"])
                    ->setValue("node_top",    $node["top"])
                    ->setValue("node_left",   $node["left"])
                    ->setValue("enterby",     $node["enterby"])
                    ->setValue("enterdate",   $node["enterdate"])
                    ->add();
            }

            if (!empty($actionsData))     (new Set())->useTransactionManager()->MboSetRemote("mxswfaction")->addStream($actionsData);
            if (!empty($rolesData))       (new Set())->useTransactionManager()->MboSetRemote("mxswfnoderole")->addStream($rolesData);
            if (!empty($inputfieldsData)) (new Set())->useTransactionManager()->MboSetRemote("mxswfinput")->addStream($inputfieldsData);

            // ── D. COMMIT ─────────────────────────────────────────
            $tm->commit();

            Super::sendJsonResponse("success", "Workflow '$wfname' saved successfully.", [
                "wfname"            => $wfname,
                "nodes_saved"       => count($nodesData),
                "actions_saved"     => count($actionsData),
                "roles_saved"       => count($rolesData),
                "inputfields_saved" => count($inputfieldsData),
            ]);

        } catch (Exception $e) {
            $tm->rollback();
            error_log("Failed to save Workflow '$wfname': " . $e->getMessage());
            Super::sendJsonResponse("error", "Save failed. Please contact administrator.");
        }
    }


	private static function autoLayoutNodes($nodes, $actions) {
		$positions = [];
		$levels = [];
		$nodeMap = [];

		// Inisialisasi nodeMap dan temukan node awal (START)
		$startNodeId = null;
		foreach ($nodes as $node) {
			$nodeMap[$node['id']] = ['node' => $node, 'level' => -1, 'children' => [], 'parents' => []];
			if (strtoupper($node['type']) === 'START') {
				$startNodeId = $node['id'];
			}
		}

		// Bangun graph (siapa parent, siapa child)
		foreach ($actions as $action) {
			if (isset($nodeMap[$action['source']]) && isset($nodeMap[$action['target']])) {
				$nodeMap[$action['source']]['children'][] = $action['target'];
				$nodeMap[$action['target']]['parents'][] = $action['source'];
			}
		}

		// Algoritma Breadth-First Search (BFS) untuk menentukan level setiap node
		if ($startNodeId) {
			$queue = new SplQueue();
			$queue->enqueue($startNodeId);
			$nodeMap[$startNodeId]['level'] = 0;
			$levels[0][] = $startNodeId;
			
			while (!$queue->isEmpty()) {
				$currentId = $queue->dequeue();
				foreach ($nodeMap[$currentId]['children'] as $childId) {
					if ($nodeMap[$childId]['level'] === -1) { // Jika belum dikunjungi
						$level = $nodeMap[$currentId]['level'] + 1;
						$nodeMap[$childId]['level'] = $level;
						$levels[$level][] = $childId;
						$queue->enqueue($childId);
					}
				}
			}
		}
		
		// Atur posisi X dan Y berdasarkan level
		$x_gap = 250; // Jarak horizontal antar level
		$y_gap = 150; // Jarak vertikal antar node di level yang sama
		$y_offset = 50; // Margin atas

		foreach ($levels as $level => $nodeIds) {
			$x_pos = 50 + ($level * $x_gap);
			$totalNodesInLevel = count($nodeIds);
			$level_y_offset = $y_offset + (($totalNodesInLevel > 1) ? 0 : 75); // Pusatkan jika hanya 1 node

			foreach ($nodeIds as $index => $nodeId) {
				$y_pos = $level_y_offset + ($index * $y_gap);
				$positions[$nodeId] = ['top' => $y_pos, 'left' => $x_pos];
			}
		}

		return $positions;
	}
		
	 public static function renderWF()
    {
        try {
            $wfname = Super::getParam("renderWF");

            if (empty($wfname)) {
                Super::sendJsonResponse("error", "wfname parameter is missing.");
                return;
            }

            // ── 1. NODES ─────────────────────────────────────────
            $nodesData   = [];
            $setNodes    = new Set();
            $nodesResult = $setNodes->MboSetRemote("mxswfnode")
                ->where("wfname = ?", $wfname)
                ->MboRemote("nodeline as id, title, nodetype as type, description, whereclause")
                ->order("nodeline ASC")
                ->getMboSet();

            while ($nodeRow = $nodesResult->fetch()) {
                $nodesData[] = $nodeRow;
            }

            // ── 2. ACTIONS ────────────────────────────────────────
            $actionsData   = [];
            $setActions    = new Set();
            $actionsResult = $setActions->MboSetRemote("mxswfaction a")
                ->setRelationship("mxswfnode n", "a.wfname = n.wfname AND a.nodeline = n.nodeline")
                ->where("a.wfname = ?", $wfname)
                ->MboRemote("a.nodeline as source, a.tonodeline as target, a.action, a.title as actiontitle, a.instruction, a.isnegative, a.whereclause as actionwhereclause, a.sortorder, a.isdefault, n.nodetype as sourcetype")
                ->order("a.nodeline ASC, a.sortorder ASC")
                ->getMboSet();

            while ($actionRow = $actionsResult->fetch()) {
                $sourceType = strtoupper($actionRow['sourcetype'] ?? '');

                // Label untuk render visual — preserve logika lama
                $label = $actionRow['action'] ?? '';
                if ($sourceType === 'CONDITION') {
                    $label = ($actionRow['isnegative'] === 'N') ? 'Yes' : 'No';
                } elseif ($sourceType === 'MULTICONDITION') {
                    $wc = $actionRow['actionwhereclause'] ?? '';
                    $label = !empty($actionRow['action'])
                        ? $actionRow['action']
                        : (!empty($wc)
                            ? substr($wc, 0, 20) . (strlen($wc) > 20 ? '...' : '')
                            : 'Branch');
                }

                $actionsData[] = [
                    "source"      => $actionRow['source'],
                    "target"      => $actionRow['target'],
                    "label"       => $label,
                    "action"      => $actionRow['action']            ?? '',   // ← mxswfaction.action untuk Action Name field di builder
                    "title"       => $actionRow['actiontitle']       ?? '',
                    "instruction" => $actionRow['instruction']       ?? '',
                    "isnegative"  => $actionRow['isnegative']        ?? 'N',
                    "whereclause" => $actionRow['actionwhereclause'] ?? '',
                    "sortorder"   => (int)($actionRow['sortorder']   ?? 0),
                    "isdefault"   => $actionRow['isdefault']         ?? 'N',
                ];
            }

            // ── 3. NODEROLES ──────────────────────────────────────
            $nodeRolesData = [];
            $setRoles      = new Set();
            $rolesResult   = $setRoles->MboSetRemote("mxswfnoderole")
                ->where("wfname = ?", $wfname)
                ->MboRemote("nodeline, nodetitle, mxsrole")
                ->order("nodeline ASC")
                ->getMboSet();

            while ($roleRow = $rolesResult->fetch()) {
                $nodeRolesData[] = $roleRow;
            }

            // ── 4. INPUT FIELDS ──────────────────────────────────
            // { nodeline: [{tablename, columnname, instruction, isrequired, sortorder}] }
            $inputfieldsData = [];
            $setInput        = new Set();
            $inputResult     = $setInput->MboSetRemote("mxswfinput")
                ->where("wfname = ?", $wfname)
                ->MboRemote("nodeline, tablename, columnname, instruction, isrequired, sortorder")
                ->order("nodeline ASC, sortorder ASC")
                ->getMboSet();

            while ($inputRow = $inputResult->fetch()) {
                $nl = $inputRow['nodeline'];
                if (!isset($inputfieldsData[$nl])) $inputfieldsData[$nl] = [];
                $inputfieldsData[$nl][] = [
                    'tablename'   => $inputRow['tablename']   ?? '',
                    'columnname'  => $inputRow['columnname']  ?? '',
                    'instruction' => $inputRow['instruction'] ?? '',
                    'isrequired'  => $inputRow['isrequired']  ?? 'Y',
                    'sortorder'   => (int)($inputRow['sortorder'] ?? 10),
                ];
            }

            // ── 5. AUTO LAYOUT — preserve logika lama ────────────
            $nodePositions = self::autoLayoutNodes($nodesData, $actionsData);
            foreach ($nodesData as &$node) {
                if (isset($nodePositions[$node['id']])) {
                    $node['top']  = $nodePositions[$node['id']]['top'];
                    $node['left'] = $nodePositions[$node['id']]['left'];
                } else {
                    $node['top']  = 50;
                    $node['left'] = 50;
                }
            }

            Super::sendJsonResponse("success", "Workflow data retrieved successfully.", [
                "nodes"       => $nodesData,
                "actions"     => $actionsData,
                "noderoles"   => $nodeRolesData,
                "inputfields" => $inputfieldsData,  // { nodeline: [fields] }
            ]);

        } catch (Exception $e) {
            Super::sendJsonResponse("error", "An error occurred: " . $e->getMessage());
        }
    }



	public static function ReassignInbox(){
		$toassignment = $_POST["toassignment"];
		$rastype = $_POST["rastype"];
		if(isset ($_POST["newrule"]) ) $newrule = $_POST["newrule"];
		else $newrule = null;
		
		$reassigninbox = $_POST["reassigninbox"];
		$enterdate = Xbo::setEnterDate();
		$changeby = Xbo::getLoginUser();
		$count = count($reassigninbox);
		if($rastype == "newperson"){
			for($i=0; $i<$count; $i++){
				$rai = $reassigninbox[$i]["mxswftrackingid"];
				$set = new Set();
				$mxswftrackingSet = $set->MboSetRemote("mxswftracking")->setWhere("mxswftrackingid = '$rai'")->MboRemote("mxsrole")->MboRemote("wfname")->MboRemote("appname")
				->MboRemote("rolevalue")->MboRemote("appkey")->MboRemote("currentnodeline")->MboRemote("roleaction")->MboRemote("hasstop")->MboRemote("followuprule")->getMboSet();
				$mxswftracking = $mxswftrackingSet->getMbo();
				$mxsrole = $mxswftracking->getString("mxsrole");
				$wfname = $mxswftracking->getString("wfname");
				$appname = $mxswftracking->getString("appname");
				$appkey = $mxswftracking->getString("appkey");
				$currentnodeline = $mxswftracking->getString("currentnodeline");
				$roleaction = $mxswftracking->getString("roleaction");
				$hasstop = $mxswftracking->getString("hasstop");
				$followuprule = $mxswftracking->getString("followuprule");
				$rolevalue = $mxswftracking->getString("rolevalue");
				
				MaxesWorkflow::updateLeadTime($appname, $appkey, $wfname);
				
				$set2 = new Set();
				$set2->MboSetRemote("mxswftracking")->setValue("mxsrole", "REASSIGN")->setValue("rolevalue", $toassignment)->setValue("wfname", $wfname)->setValue("appname", $appname)
				->setValue("appkey", $appkey)->setValue("currentnodeline", $currentnodeline)->setValue("roleaction", $roleaction)->setValue("hasstop", $hasstop)
				->setValue("followuprule", $followuprule)->setValue("enterdate", $enterdate)->setValue("changeby", $changeby)->setValue("reassignfrom", $rai)->add();
				
			}
		}else{
			
		}
		Set::throws(["rastype" => $rastype, "newrule" => $newrule, "toassignment" => $toassignment, "reassigninbox" => $reassigninbox]);
	}
	public static function MoveWorkflowInbox()
	{
		$id = $_POST["moveinbox"];
		$newnodeline = $_POST["newnodeline"];
		$wfname = $_POST["wfname"];
		$countids = count($id);
		
		$s = new Set();
		$newnodelineSet = $s->MboSetRemote("mxswfnode")->setRelationship("mxswfnoderole", "mxswfnoderole.nodeline = mxswfnode.nodeline and mxswfnoderole.wfname = mxswfnode.wfname ")
		->setRelationship("mxswfrole", "mxswfnoderole.mxsrole = mxswfrole.mxsrole")
		->setWhere("mxswfnode.wfname='$wfname' and mxswfnode.nodeline='$newnodeline' ")->MboRemote("mxswfnode.nodeline")->MboRemote("mxswfnode.nodetype")->MboRemote("mxswfnode.description")
		->MboRemote("mxswfnoderole.mxsrole")->MboRemote("mxswfrole.type")->MboRemote("mxswfrole.value")->getMboSet();
		
		$t = new Set();
		$mxswfactionSet = $t->MboSetRemote("mxswfaction")->setWhere("wfname = '$wfname' and tonodeline='$newnodeline'")->MboRemote("action")->getMboSet();
		$mxswfaction = $mxswfactionSet->getMbo();
		$roleaction = $mxswfaction->getString("action");
		$docsentby = Xbo::getLoginUser();
		$cnt= $newnodelineSet->counts();
		for($i=0; $i<$cnt; $i++){
			$set = new Set();
			$newnode = $newnodelineSet->getMbo();
			$nodeline = $newnode->getString("nodeline");
			$nodetype = $newnode->getString("nodetype");
			$mxsrole = $newnode->getString("mxsrole");
			$roletype = $newnode->getString("type");
			$value = $newnode->getString("value");
			$nodedesc = $newnode->getString("description");
			
			if($nodetype == "STOP") $hasstop = "Y";
			else $hasstop = "N";
			for($j=0; $j<$countids; $j++){
				$set2 = new Set();
				$mxswfid = $id[$j]["mxswftrackingid"];
				$mxswftrackingSet = $set2->MboSetRemote("mxswftracking")->setWhere("mxswftrackingid='".$mxswfid."'")->MboRemote("appname")->MboRemote("appkey")->getMboSet();
				$mxswftracking = $mxswftrackingSet->getMbo();
				
				$appname = $mxswftracking->getString("appname");
				$appkey = $mxswftracking->getString("appkey");
				$appnameid = $appname."id";
				
				if($roletype == "PERSON" ){
					$upd = new Set();
					$upd->MboSetRemote("mxswftracking")->setWhere("mxswftrackingid='$mxswfid'")->setValue("mxsrole", $mxsrole)->setValue("rolevalue", $value)->setValue("currentnodeline", $newnodeline)
					->setValue("roleaction", $roleaction)->setValue("hasstop", $hasstop)->change();
					$upd2 = new Set();
					
					$upd2->MboSetRemote($appname)->setWhere("$appnameid = '$appkey'")->setValue("docprocessnum", $newnodeline)->setValue("docstatus", $roleaction)->setValue("docnotice", $nodedesc)
					->setValue("docposition", $value)->setValue("docsentby", $docsentby)->change();
				}else{
					if($roletype == "FIELDNAME"){
						$st = new Set();
						$mxswfroleSet = $st->MboSetRemote("mxswfrole")->setWhere("mxsrole='$mxsrole' and type = 'FIELDNAME'")->MboRemote("value")->getMboSet();
						$mxswfrole = $mxswfroleSet->getMbo();
						$field = $mxswfrole->getString("value");
						
						$at = new Set();
						$atSet=$at->MboSetRemote($appname)->setWhere("$appnameid='$appkey'")->MboRemote($field)->getMboSet();
						$at = $atSet->getMbo();
						$fieldvalue = $at->getString($field);

						if(!empty($fieldvalue)){
							$value = $fieldvalue;
							$upd = new Set();
							$upd->MboSetRemote("mxswftracking")->setWhere("mxswftrackingid='$mxswfid'")->setValue("mxsrole", $mxsrole)->setValue("rolevalue", $value)->setValue("currentnodeline", $newnodeline)
							->setValue("roleaction", $roleaction)->setValue("hasstop", $hasstop)->change();
							
							$upd2 = new Set();
							$upd2->MboSetRemote($appname)->setWhere("$appnameid = '$appkey'")->setValue("docprocessnum", $newnodeline)->setValue("docstatus", $roleaction)->setValue("docnotice", $nodedesc)
							->setValue("docposition", $value)->setValue("docsentby", $docsentby)->change();
						}
					}else{
						// not yet defined
					}
				}
			}
		}
	}
} 

Class ValidateTables {
	
	public static function tbcx(){
		$dl = explode("|", $_GET["tbcx"]);
		$set = new Set();
		$appname = $dl[0];
		$vtable = $dl[2];
		
		if ($vtable == "mxswfnode") {
			$colarr = explode("^", $dl[3]);
			$flag = 0;
			for ($i = 0; $i < count($colarr); $i++) {
				$tableid = explode("`", $colarr[$i]) [1];
				
				$st = new Set();
				$mxswfnset = $set->MboSetRemote("mxswfnode")->setWhere("mxswfnodeid='$tableid'")->MboRemote("wfname")->MboRemote("nodeline")->MboRemote("nodetype")->getMboSet();
				$mxswfn = $mxswfnset->getMbo();
				$nodeline = $mxswfn->getString("nodeline");
				$nodetype = $mxswfn->getString("nodetype");
				$wfname = $mxswfn->getString("wfname");
				
				
				$set = new Set();
				$where = " a.appkey not in (select mxswftracking.appkey from mxswftracking where hasstop='Y' and appname = a.appname) and a.wfname = '$wfname' and a.currentnodeline=$nodeline ";
				$rendertableSet = $set->MboSetRemote("mxswftracking a")
				->setWhere($where)
				->MboRemote("a.appname")->MboRemote("a.appkey")->MboRemote("a.currentnodeline")
				->MboRemote("a.enterdate")->MboRemote("a.wfname")->MboRemote("a.mxswftrackingid")
				->MboRemote("a.rolevalue")->getMboSet();	
				$count = $rendertableSet->counts();
				
				if ($count > 0) {
					$flag = $flag + 1;
				}
				else {
					$flag = 0;
				}
			}
			if ($flag > 0) {
				Set::throws(["msg" => "relateddata", "rltbl" => "WF Inbox"]);
			}
			else {
				Set::throws(["msg" => "omdelete"]);
			}
		}
		
		
	} 
}
	

?>